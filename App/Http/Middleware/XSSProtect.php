<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * XSSProtect Middleware
 *
 * Strips unauthorized HTML tags from all incoming request input to
 * prevent XSS (Cross-Site Scripting) attacks. A whitelist of safe
 * tags is preserved for rich-text content: basic formatting, tables,
 * lists, headings, and media embeds.
 *
 * @package App\Http\Middleware
 */
class XSSProtect
{
    /** @var string  HTML tags allowed to pass through the filter */
    private const ALLOWED_TAGS = '<span><p><a><b><i><u><strong><br><hr>'
        . '<table><tr><th><td><ul><ol><li>'
        . '<h1><h2><h3><h4><h5><h6>'
        . '<del><ins><sup><sub><pre><address>'
        . '<img><figure><embed><iframe><video><style>';

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
            $input = strip_tags(
                str_replace(['&lt;', '&gt;'], '', $input),
                self::ALLOWED_TAGS
            );
        });

        $request->merge($input);

        return $next($request);
    }
}