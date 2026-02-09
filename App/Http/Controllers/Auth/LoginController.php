<?php

namespace App\Http\Controllers\Auth;

// ── Framework Dependencies ──────────────────────────────────────────────────
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Laravel\Socialite\Facades\Socialite;

// ── Application Dependencies ────────────────────────────────────────────────
use App\Helper\EmailHelper;
use App\Http\Controllers\Controller;
use App\Mail\UserForgetPassword;
use App\Models\User;
use App\Rules\Captcha;

// ── Module Models ───────────────────────────────────────────────────────────
use Modules\EmailSetting\App\Models\EmailTemplate;
use Modules\GlobalSetting\App\Models\GlobalSetting;

/**
 * LoginController
 *
 * Handles user authentication including custom login/logout, password
 * reset flow (forget → reset → store), and social login via Google
 * and Facebook using Laravel Socialite. Social OAuth credentials are
 * loaded dynamically from the database GlobalSetting table.
 *
 * @package App\Http\Controllers\Auth
 */
class LoginController extends Controller
{
    use AuthenticatesUsers;

    /** @var string  Default redirect path after login */
    protected $redirectTo = '/user/dashboard';

    /**
     * Create a new controller instance.
     * Guest middleware applied to all methods except logout.
     */
    public function __construct()
    {
        $this->middleware('guest:web')->except('student_logout');
    }

    /**
     * Resolve the post-login redirect path.
     *
     * Checks for an intended URL first (e.g., from a booking flow
     * that required authentication), falling back to the default.
     *
     * @return string
     */
    public function redirectTo()
    {
        if (session()->has('url.intended')) {
            return session()->pull('url.intended');
        }
        return $this->redirectTo;
    }

    // ════════════════════════════════════════════════════════════════════════
    // ── Login / Logout ──────────────────────────────────────────────────────
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Display the custom login page.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function custom_login_page()
    {
        $breadcrumb_title = trans('translate.Sign In');
        return view('auth.login', ['breadcrumb_title' => $breadcrumb_title]);
    }

    /**
     * Authenticate a user with email and password.
     *
     * Validates credentials and reCAPTCHA, checks account status,
     * email verification, and provider type before attempting login.
     * Redirects agencies to the agency dashboard, regular users to
     * the user dashboard, or to an intended URL if one exists.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store_login(Request $request)
    {
        $this->validate($request, [
            'email' => 'required',
            'password' => 'required',
            'g-recaptcha-response' => new Captcha(),
        ], [
            'email.required' => trans('translate.Email is required'),
            'password.required' => trans('translate.Password is required'),
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return $this->loginError(trans('translate.Email not found'));
        }

        // Check account is active and not banned
        if ($user->status != $user::STATUS_ACTIVE || $user->is_banned != $user::BANNED_INACTIVE) {
            return $this->loginError(trans('translate.Inactive your account'));
        }

        // Check email verification
        if ($user->email_verified_at == null) {
            return $this->loginError(trans('translate.Please verify your email'));
        }

        // Prevent password login for social-only accounts
        if ($user->provider) {
            return $this->loginError(trans('translate.Please try to login with social media'));
        }

        // Verify password and attempt login
        if (!Hash::check($request->password, $user->password)) {
            return $this->loginError(trans('translate.Credential does not match'));
        }

        $credentials = ['email' => $request->email, 'password' => $request->password];
        if (Auth::guard('web')->attempt($credentials, $request->remember)) {
            $notify_message = ['message' => trans('translate.Login successfully'), 'alert-type' => 'success'];

            // Redirect to intended URL (booking flow) or dashboard
            if (session()->has('url.intended')) {
                $intended = session()->pull('url.intended');
                Log::info('Redirecting to intended URL after login', [
                    'intended_url' => $intended,
                    'user_id' => $user->id,
                ]);
                return redirect($intended)->with($notify_message);
            }

            return ($user->is_seller == 1)
                ? redirect()->route('agency.dashboard')->with($notify_message)
                : redirect()->route('user.dashboard')->with($notify_message);
        }

        return $this->loginError(trans('translate.Credential does not match'));
    }

    /**
     * Log the user out and clear session data.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function student_logout()
    {
        Auth::guard('web')->logout();
        session()->forget('url.intended');
        session()->forget('url.intended.timestamp');

        $notify_message = ['message' => trans('translate.Logout successfully'), 'alert-type' => 'success'];
        return redirect()->route('user.login')->with($notify_message);
    }

    // ════════════════════════════════════════════════════════════════════════
    // ── Password Reset Flow ─────────────────────────────────────────────────
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Display the forget password form.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function custom_forget_page()
    {
        $breadcrumb_title = trans('translate.Forget Password');
        return view('auth.forget_password', ['breadcrumb_title' => $breadcrumb_title]);
    }

    /**
     * Send a password reset link to the user's email.
     *
     * Generates a random token, stores it on the user record, and
     * sends a reset email using the configured email template (ID 1).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function send_custom_forget_pass(Request $request)
    {
        $this->validate($request, [
            'email' => 'required',
            'g-recaptcha-response' => new Captcha(),
        ], [
            'email.required' => trans('translate.Email is required'),
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return $this->loginError(trans('translate.Email not found'));
        }

        // Generate reset token and send email
        EmailHelper::mail_setup();
        $user->forget_password_token = Str::random(100);
        $user->save();

        $reset_link = route('user.reset-password') . '?token=' . $user->forget_password_token . '&email=' . $user->email;
        $reset_link = '<a href="' . $reset_link . '">' . $reset_link . '</a>';

        try {
            $template = EmailTemplate::where('id', 1)->first();
            $subject = $template->subject;
            $message = $template->description;
            $message = str_replace('{{user_name}}', $user->name, $message);
            $message = str_replace('{{reset_link}}', $reset_link, $message);
            Mail::to($user->email)->send(new UserForgetPassword($message, $subject, $user));
        }
        catch (Exception $ex) {
            Log::info('Forget pass : ' . $ex->getMessage());
        }

        $notify_message = trans('translate.A password reset link has been send to your mail');
        $notify_message = ['message' => $notify_message, 'alert-type' => 'success'];
        return redirect()->back()->with($notify_message);
    }

    /**
     * Display the password reset form after token validation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function custom_reset_password(Request $request)
    {
        $user = User::select('id', 'name', 'email', 'forget_password_token')
            ->where('forget_password_token', $request->token)
            ->where('email', $request->email)
            ->first();

        if (!$user) {
            $notify_message = ['message' => trans('translate.Invalid token, please try again'), 'alert-type' => 'error'];
            return redirect()->route('user.forget-password')->with($notify_message);
        }

        $breadcrumb_title = trans('translate.Reset Password');
        return view('auth.reset_password', [
            'breadcrumb_title' => $breadcrumb_title,
            'user' => $user,
        ]);
    }

    /**
     * Store the new password after a valid reset token verification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $token  Password reset token
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store_reset_password(Request $request, $token)
    {
        $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'confirmed', 'min:4', 'max:100'],
            'g-recaptcha-response' => new Captcha(),
        ], [
            'email.required' => trans('translate.Email is required'),
            'email.unique' => trans('translate.Email already exist'),
            'password.required' => trans('translate.Password is required'),
            'password.confirmed' => trans('translate.Confirm password does not match'),
            'password.min' => trans('translate.You have to provide minimum 4 character password'),
        ]);

        $user = User::where('forget_password_token', $token)
            ->where('email', $request->email)
            ->first();

        if (!$user) {
            $notify_message = ['message' => trans('translate.Invalid token, please try again'), 'alert-type' => 'error'];
            return redirect()->back()->with($notify_message);
        }

        $user->password = Hash::make($request->password);
        $user->forget_password_token = null;
        $user->save();

        $notify_message = ['message' => trans('translate.Password reset successfully'), 'alert-type' => 'success'];
        return redirect()->route('user.login')->with($notify_message);
    }

    // ════════════════════════════════════════════════════════════════════════
    // ── Social Login (Google) ───────────────────────────────────────────────
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Redirect the user to Google's OAuth page.
     *
     * Loads OAuth credentials dynamically from GlobalSetting.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirect_to_google()
    {
        $this->configureSocialDriver('google', 'gmail');
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle the callback from Google OAuth.
     *
     * Creates or finds the user by email, logs them in, and redirects
     * to the intended URL or user dashboard.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function google_callback()
    {
        $this->configureSocialDriver('google', 'gmail');

        $socialUser = Socialite::driver('google')->user();
        $user = $this->create_user($socialUser, 'google');
        auth()->login($user);

        return $this->socialLoginRedirect();
    }

    // ════════════════════════════════════════════════════════════════════════
    // ── Social Login (Facebook) ─────────────────────────────────────────────
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Redirect the user to Facebook's OAuth page.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirect_to_facebook()
    {
        $this->configureSocialDriver('facebook', 'facebook');
        return Socialite::driver('facebook')->redirect();
    }

    /**
     * Handle the callback from Facebook OAuth.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function facebook_callback()
    {
        $this->configureSocialDriver('facebook', 'facebook');

        $socialUser = Socialite::driver('facebook')->user();
        $user = $this->create_user($socialUser, 'facebook');
        auth()->login($user);

        return $this->socialLoginRedirect();
    }

    // ════════════════════════════════════════════════════════════════════════
    // ── Private Helpers ─────────────────────────────────────────────────────
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Create or find a user from social login data.
     *
     * If the user's email doesn't exist in the database, a new account
     * is created with the social provider info and auto-verified.
     *
     * @param  object  $get_info  Socialite user object
     * @param  string  $provider  Provider name ('google' or 'facebook')
     * @return \App\Models\User
     */
    private function create_user($get_info, string $provider): User
    {
        $user = User::where('email', $get_info->email)->first();

        if (!$user) {
            $user = User::create([
                'name' => $get_info->name,
                'username' => Str::slug($get_info->name) . '-' . date('Ymdhis'),
                'email' => $get_info->email,
                'provider' => $provider,
                'provider_id' => $get_info->id,
                'status' => 'enable',
                'is_banned' => 'no',
                'email_verified_at' => date('Y-m-d H:i:s'),
                'verification_token' => null,
            ]);
        }

        return $user;
    }

    /**
     * Configure a Socialite driver with credentials from GlobalSetting.
     *
     * @param  string  $driver   Socialite driver name ('google' or 'facebook')
     * @param  string  $prefix   GlobalSetting key prefix ('gmail' or 'facebook')
     */
    private function configureSocialDriver(string $driver, string $prefix): void
    {
        $clientId = GlobalSetting::where('key', "{$prefix}_client_id")->first();
        $secretId = GlobalSetting::where('key', "{$prefix}_secret_id")->first();
        $redirectUrl = GlobalSetting::where('key', "{$prefix}_redirect_url")->first();

        \Config::set("services.{$driver}.client_id", $clientId->value);
        \Config::set("services.{$driver}.client_secret", $secretId->value);
        \Config::set("services.{$driver}.redirect", $redirectUrl->value);
    }

    /**
     * Handle post-social-login redirect with intended URL support.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    private function socialLoginRedirect()
    {
        $notify_message = ['message' => trans('translate.Login Successfully'), 'alert-type' => 'success'];

        if (session()->has('url.intended')) {
            $intended = session()->pull('url.intended');
            return redirect($intended)->with($notify_message);
        }

        return redirect()->route('user.dashboard')->with($notify_message);
    }

    /**
     * Return a redirect with an error notification.
     *
     * @param  string  $message  Error message to display
     * @return \Illuminate\Http\RedirectResponse
     */
    private function loginError(string $message)
    {
        return redirect()->back()->with(['message' => $message, 'alert-type' => 'error']);
    }
}