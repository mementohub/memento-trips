<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * CheckAgency Middleware
 *
 * Ensures the authenticated user is an approved agency (seller).
 * Redirects unauthenticated users to the login page, and redirects
 * non-agency or unapproved users to the user dashboard with an
 * appropriate error message.
 *
 * @package App\Http\Middleware
 */
class CheckAgency
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $auth_user = Auth::guard('web')->user();

        if (!$auth_user) {
            return redirect()->route('user.login');
        }

        $isAgencyEnabled = (int)($auth_user->is_seller ?? 0) === 1;
        $isApproved = ($auth_user->instructor_joining_request ?? null) === 'approved';

        if ($isAgencyEnabled && $isApproved) {
            return $next($request);
        }

        $messageKey = $isAgencyEnabled
            ? 'translate.Agency joining request send to admin. please awaiting for approval'
            : 'translate.Unable to access agency dashboard';

        $notify_message = ['message' => trans($messageKey), 'alert-type' => 'error'];

        return redirect()->route('user.dashboard')->with($notify_message);
    }
}