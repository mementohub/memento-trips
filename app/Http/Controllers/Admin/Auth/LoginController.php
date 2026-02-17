<?php

namespace App\Http\Controllers\Admin\Auth;

// ── Framework Dependencies ──────────────────────────────────────────────────
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

// ── Application Dependencies ────────────────────────────────────────────────
use App\Http\Controllers\Controller;
use App\Models\Admin;

/**
 * Admin LoginController
 *
 * Handles admin authentication including login, initial super admin
 * registration (first-time setup), and logout. Uses the 'admin' guard
 * for authentication to separate admin sessions from user sessions.
 *
 * @package App\Http\Controllers\Admin\Auth
 */
class LoginController extends Controller
{
    use AuthenticatesUsers;

    /** @var string  Default redirect path after admin login */
    protected $redirectTo = '/admin/dashboard';

    /**
     * Create a new controller instance.
     * Guest middleware applied except for logout.
     */
    public function __construct()
    {
        $this->middleware('guest:admin')->except('admin_logout');
    }

    // ════════════════════════════════════════════════════════════════════════
    // ── Login ───────────────────────────────────────────────────────────────
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Display the admin login page.
     *
     * Shows the registration form if no super admin exists yet,
     * otherwise shows the standard login form.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function custom_login_page()
    {
        $has_super_admin = Admin::exists();
        return view('admin.auth.login', ['has_super_admin' => $has_super_admin]);
    }

    /**
     * Authenticate an admin with email and password.
     *
     * Validates credentials, checks account active status, and
     * attempts login via the 'admin' guard.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store_login(Request $request)
    {
        $this->validate($request, [
            'email' => 'required',
            'password' => 'required',
        ], [
            'email.required' => trans('translate.Email is required'),
            'password.required' => trans('translate.Password is required'),
        ]);

        $admin = Admin::where('email', $request->email)->first();

        if (!$admin) {
            return $this->adminError(trans('translate.Email not found'));
        }

        if ($admin->status != $admin::STATUS_ACTIVE) {
            return $this->adminError(trans('translate.Inactive your account'));
        }

        if (!Hash::check($request->password, $admin->password)) {
            return $this->adminError(trans('translate.Credential does not match'));
        }

        $credentials = ['email' => $request->email, 'password' => $request->password];
        if (Auth::guard('admin')->attempt($credentials, $request->remember)) {
            $notify_message = ['message' => trans('translate.Login successfully'), 'alert-type' => 'success'];
            return redirect()->route('admin.dashboard')->with($notify_message);
        }

        return $this->adminError(trans('translate.Credential does not match'));
    }

    // ════════════════════════════════════════════════════════════════════════
    // ── First-Time Registration ─────────────────────────────────────────────
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Register the initial super admin account (first-time setup only).
     *
     * Only works when no admin exists in the database. Creates a
     * super_admin account and immediately logs them in.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store_register(Request $request)
    {
        if (Admin::exists()) {
            $notify_message = ['message' => trans('translate.Super admin already exist'), 'alert-type' => 'error'];
            return redirect()->back()->with($notify_message);
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:' . Admin::class],
            'password' => ['required', 'confirmed', 'min:4', 'max:100'],
        ], [
            'name.required' => trans('translate.Name is required'),
            'email.required' => trans('translate.Email is required'),
            'email.unique' => trans('translate.Email already exist'),
            'password.required' => trans('translate.Password is required'),
            'password.confirmed' => trans('translate.Confirm password does not match'),
            'password.min' => trans('translate.You have to provide minimum 4 character password'),
        ]);

        $super_admin = new Admin();
        $super_admin->name = $request->name;
        $super_admin->email = $request->email;
        $super_admin->status = 'enable';
        $super_admin->admin_type = 'super_admin';
        $super_admin->password = Hash::make($request->password);
        $super_admin->save();

        Auth::guard('admin')->attempt(['email' => $request->email, 'password' => $request->password]);

        $notify_message = ['message' => trans('translate.Super admin created successfully'), 'alert-type' => 'success'];
        return redirect()->route('admin.dashboard')->with($notify_message);
    }

    // ════════════════════════════════════════════════════════════════════════
    // ── Logout ──────────────────────────────────────────────────────────────
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Log the admin out and redirect to the login page.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function admin_logout()
    {
        Auth::guard('admin')->logout();

        $notify_message = ['message' => trans('translate.Logout successfully'), 'alert-type' => 'success'];
        return redirect()->route('admin.login')->with($notify_message);
    }

    // ════════════════════════════════════════════════════════════════════════
    // ── Private Helpers ─────────────────────────────────────────────────────
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Return a redirect with an admin error notification.
     *
     * @param  string  $message
     * @return \Illuminate\Http\RedirectResponse
     */
    private function adminError(string $message)
    {
        return redirect()->back()->with(['message' => $message, 'alert-type' => 'error']);
    }
}