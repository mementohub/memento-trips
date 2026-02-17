<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * HtmlSpecialchars Middleware
 *
 * Sanitizes all incoming request input by encoding HTML special
 * characters (quotes, ampersands, angle brackets) to prevent
 * HTML injection. Applied globally to escape user-submitted data
 * before it reaches controllers.
 *
 * @package App\Http\Middleware
 */
class HtmlSpecialchars
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
        $input = array_filter($request->all());

        array_walk_recursive($input, function (&$input) {
            $input = htmlspecialchars($input, ENT_QUOTES);
        });

        $request->merge($input);

        return $next($request);
    }
}