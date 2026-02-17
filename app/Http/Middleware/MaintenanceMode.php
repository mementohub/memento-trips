<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Modules\GlobalSetting\App\Models\GlobalSetting;

/**
 * MaintenanceMode Middleware
 *
 * Checks the `maintenance_status` GlobalSetting and returns the
 * maintenance page if maintenance mode is enabled. Applied to
 * all frontend routes to block public access during updates.
 *
 * @package App\Http\Middleware
 */
class MaintenanceMode
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
        $maintenance_status = GlobalSetting::where('key', 'maintenance_status')->first();

        if ($maintenance_status && $maintenance_status->value == 1) {
            return response()->view('maintenance');
        }

        return $next($request);
    }
}