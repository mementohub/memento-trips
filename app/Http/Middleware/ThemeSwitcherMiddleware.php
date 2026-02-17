<?php

namespace App\Http\Middleware;

// ── Framework Dependencies ──────────────────────────────────────────────────
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;

/**
 * ThemeSwitcherMiddleware
 *
 * Resolves the active CMS theme on each request. Supports three-tier
 * priority: (1) session preview via `?theme=` query parameter,
 * (2) database-persisted system default from `settings.active_theme`,
 * (3) config fallback (`themes.default`). Validates theme directory
 * existence with case-insensitive path checking.
 *
 * @package App\Http\Middleware
 */
class ThemeSwitcherMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if theme switching is enabled
        if (!config('themes.enable_switching', true)) {
            return $next($request);
        }

        // Handle theme preview via query parameter
        if ($request->has('theme')) {
            $requestedTheme = $request->theme;
            $availableThemes = config('themes.themes', []);

            if (in_array($requestedTheme, array_keys($availableThemes))
                && $this->themeExists($requestedTheme)) {
                Session::put('selected_theme', $requestedTheme);
            }
        }

        // Resolve active theme: Session → Database → Config
        $databaseTheme = $this->getDatabaseTheme();

        $selectedTheme = Session::get(
            'selected_theme',
            $databaseTheme ?? config('themes.default', 'theme1')
        );

        // Fallback if resolved theme no longer exists on disk
        if (!$this->themeExists($selectedTheme)) {
            $selectedTheme = config('themes.default', 'theme1');
        }

        app('theme')->set($selectedTheme);

        return $next($request);
    }

    /**
     * Get the active theme from the database settings table.
     *
     * @return string|null
     */
    private function getDatabaseTheme(): ?string
    {
        try {
            $settingsRow = DB::table('settings')->where('key', 'active_theme')->first();
            return $settingsRow?->value;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Check if a theme directory exists (case-insensitive path checking).
     *
     * @param  string  $theme  Theme identifier slug
     * @return bool
     */
    protected function themeExists(string $theme): bool
    {
        $possiblePaths = [
            base_path("Cms/themes/{$theme}"),
            base_path("cms/themes/{$theme}"),
            base_path("CMS/themes/{$theme}"),
        ];

        foreach ($possiblePaths as $path) {
            if (File::exists($path) && File::isDirectory($path)) {
                return true;
            }
        }

        return false;
    }
}