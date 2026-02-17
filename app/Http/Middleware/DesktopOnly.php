<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DesktopOnly
{
    /**
     * Block mobile and tablet devices from accessing admin routes.
     */
    public function handle(Request $request, Closure $next)
    {
        $userAgent = $request->header('User-Agent', '');

        $mobileKeywords = [
            'Mobile',
            'Android',
            'iPhone',
            'iPad',
            'iPod',
            'webOS',
            'BlackBerry',
            'Opera Mini',
            'Opera Mobi',
            'IEMobile',
            'Windows Phone',
            'Kindle',
            'Silk',
            'PlayBook',
        ];

        foreach ($mobileKeywords as $keyword) {
            if (stripos($userAgent, $keyword) !== false) {
                return response()->view('admin.desktop-only', [], 403);
            }
        }

        return $next($request);
    }
}