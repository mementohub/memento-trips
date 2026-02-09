<?php

namespace App\Http\Middleware;

// ── Framework Dependencies ──────────────────────────────────────────────────
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * DemoMode Middleware
 *
 * Prevents data-modifying requests (POST, PUT, PATCH, DELETE) when
 * the application is running in demo mode (`APP_MODE=DEMO`).
 * Login and logout routes are always allowed to pass through.
 * Returns a JSON error for AJAX requests or a redirect with a
 * flash notification for standard form submissions.
 *
 * @package App\Http\Middleware
 */
class DemoMode
{
    /** @var array  Routes that are always allowed even in demo mode */
    private const ALLOWED_ROUTES = [
        'user.store-login',
        'user.logout',
        'admin.store-login',
        'admin.logout',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Always allow login/logout routes
        if (Route::is(...self::ALLOWED_ROUTES)) {
            return $next($request);
        }

        // Block mutating requests in demo mode
        if (env('APP_MODE') === 'DEMO' && $request->isMethod('post') || $request->isMethod('delete') || $request->isMethod('put') || $request->isMethod('patch')) {
            if (env('APP_MODE') === 'DEMO') {
                if ($request->ajax()) {
                    return response()->json(['message' => 'This Is Demo Version. You Can Not Change Anything'], 403);
                }

                $notify_message = ['message' => trans('translate.This Is Demo Version. You Can Not Change Anything'), 'alert-type' => 'error'];
                return redirect()->back()->with($notify_message);
            }
        }

        return $next($request);
    }
}