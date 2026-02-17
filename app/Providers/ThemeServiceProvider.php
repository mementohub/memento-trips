<?php

namespace App\Providers;

// ── Framework Dependencies ──────────────────────────────────────────────────
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

// ── Application Dependencies ────────────────────────────────────────────────
use App\Themes\Core\Theme;

/**
 * ThemeServiceProvider
 *
 * Registers the CMS theme engine as a singleton and bootstraps
 * theme-related functionality including view path registration,
 * Blade component namespaces, theme routes, and custom Blade
 * directives (@themeasset, @themecontent, @themetrans, @themebreadcrumbs).
 *
 * @package App\Providers
 */
class ThemeServiceProvider extends ServiceProvider
{
    /**
     * Register the theme singleton and merge theme config.
     */
    public function register(): void
    {
        $this->app->singleton('theme', function () {
            $theme = new Theme();
            $active = $theme->getActive();
            return $theme->set($active);
        });

        $this->mergeConfigFrom(
            __DIR__ . '/../../config/themes.php', 'themes'
        );
    }

    /**
     * Bootstrap theme views, routes, functions, and Blade directives.
     */
    public function boot(): void
    {
        // Global theme() helper
        if (!function_exists('theme')) {
            function theme()
            {
                return app('theme');
            }
        }

        $this->registerThemeResources();
        $this->registerBladeDirectives();
    }

    // ════════════════════════════════════════════════════════════════════════
    // ── Private Helpers ─────────────────────────────────────────────────────
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Register theme views, functions file, component namespace, and routes.
     */
    private function registerThemeResources(): void
    {
        $theme = app('theme');
        $current = $theme->current();

        if (!$theme->exists($current)) {
            return;
        }

        $themePath = $theme->getThemePath($current);

        // View directory
        View::addLocation($themePath . '/views');

        // Theme functions file
        $functionsFile = $themePath . '/functions/functions.php';
        if (File::exists($functionsFile)) {
            require_once $functionsFile;
        }

        // Blade component namespace
        Blade::componentNamespace('Theme\\' . ucfirst($current) . '\\Components', 'theme');

        // Theme routes
        $routesFile = $themePath . '/routes.php';
        if (File::exists($routesFile)) {
            Route::middleware('web')
                ->namespace('Theme\\' . ucfirst($current) . '\\Controllers')
                ->name('theme.' . $current . '.')
                ->group($routesFile);
        }
    }

    /**
     * Register custom Blade directives for theme assets, content, and translations.
     */
    private function registerBladeDirectives(): void
    {
        Blade::directive('themeasset', function ($expression) {
            return "<?php echo theme()->asset($expression); ?>";
        });

        Blade::directive('themecontent', function ($expression) {
            return "<?php echo theme()->getContent($expression); ?>";
        });

        Blade::directive('themetrans', function ($expression) {
            return "<?php echo theme_trans($expression); ?>";
        });

        Blade::directive('themebreadcrumbs', function () {
            return "<?php echo theme_breadcrumbs(); ?>";
        });
    }
}