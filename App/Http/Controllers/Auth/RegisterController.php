<?php

namespace App\Http\Controllers\Auth;

// ── Framework Dependencies ──────────────────────────────────────────────────
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Foundation\Auth\RegistersUsers;

// ── Application Dependencies ────────────────────────────────────────────────
use App\Helper\EmailHelper;
use App\Http\Controllers\Controller;
use App\Mail\UserRegistration;
use App\Models\User;
use App\Rules\Captcha;

// ── Module Models ───────────────────────────────────────────────────────────
use Modules\EmailSetting\App\Models\EmailTemplate;

/**
 * RegisterController
 *
 * Handles user registration with email verification. Creates new user
 * accounts, sends a verification email with a unique token link, and
 * processes email verification when the user clicks the link.
 *
 * @package App\Http\Controllers\Auth
 */
class RegisterController extends Controller
{
    use RegistersUsers;

    /** @var string  Default redirect path after registration */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     * Guest-only middleware applied.
     */
    public function __construct()
    {
        $this->middleware('guest:web');
    }

    // ════════════════════════════════════════════════════════════════════════
    // ── Registration Flow ───────────────────────────────────────────────────
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Display the custom registration page.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function custom_register_page()
    {
        $breadcrumb_title = trans('translate.Sign Up');
        return view('auth.register', ['breadcrumb_title' => $breadcrumb_title]);
    }

    /**
     * Register a new user account and send verification email.
     *
     * Creates the user with a random verification token, then sends
     * a verification email using the configured template (ID 4).
     * The user must click the verification link before they can log in.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store_register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', 'min:4', 'max:100'],
            'g-recaptcha-response' => new Captcha(),
        ], [
            'name.required' => trans('translate.Name is required'),
            'email.required' => trans('translate.Email is required'),
            'email.unique' => trans('translate.Email already exist'),
            'password.required' => trans('translate.Password is required'),
            'password.confirmed' => trans('translate.Confirm password does not match'),
            'password.min' => trans('translate.You have to provide minimum 4 character password'),
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => Str::slug($request->name) . '-' . date('Ymdhis'),
            'status' => 'enable',
            'is_banned' => 'no',
            'password' => Hash::make($request->password),
            'verification_token' => Str::random(100),
        ]);

        // Send verification email
        EmailHelper::mail_setup();
        $verification_link = route('user.register-verification') . '?verification_link=' . $user->verification_token . '&email=' . $user->email;
        $verification_link = '<a href="' . $verification_link . '">' . $verification_link . '</a>';

        try {
            $template = EmailTemplate::where('id', 4)->first();
            $subject = $template->subject;
            $message = $template->description;
            $message = str_replace('{{user_name}}', $request->name, $message);
            $message = str_replace('{{varification_link}}', $verification_link, $message);

            Mail::to($user->email)->send(new UserRegistration($message, $subject, $user));
        }
        catch (Exception $ex) {
            Log::info('Register mail : ' . $ex->getMessage());
        }

        $notify_message = trans('translate.Account created successful, a verification link has been send to your mail, please verify it');
        $notify_message = ['message' => $notify_message, 'alert-type' => 'success'];
        return redirect()->back()->with($notify_message);
    }

    // ════════════════════════════════════════════════════════════════════════
    // ── Email Verification ──────────────────────────────────────────────────
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Verify a user's email address using the verification token.
     *
     * Marks the email as verified and clears the token on success.
     * Redirects with an error if the token is invalid or already used.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register_verification(Request $request)
    {
        $user = User::where('verification_token', $request->verification_link)
            ->where('email', $request->email)
            ->first();

        if (!$user) {
            $notify_message = ['message' => trans('translate.Invalid token or email'), 'alert-type' => 'error'];
            return redirect()->route('user.login')->with($notify_message);
        }

        if ($user->email_verified_at != null) {
            $notify_message = ['message' => trans('translate.Email already verified'), 'alert-type' => 'error'];
            return redirect()->route('user.login')->with($notify_message);
        }

        $user->email_verified_at = date('Y-m-d H:i:s');
        $user->verification_token = null;
        $user->save();

        $notify_message = ['message' => trans('translate.Verification Successfully'), 'alert-type' => 'success'];
        return redirect()->route('user.login')->with($notify_message);
    }

    // ════════════════════════════════════════════════════════════════════════
    // ── Trait Overrides ─────────────────────────────────────────────────────
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }
}