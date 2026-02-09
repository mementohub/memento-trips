<?php

namespace App\Providers;

// ── Framework Dependencies ──────────────────────────────────────────────────
use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

// ── Module Models ───────────────────────────────────────────────────────────
use Modules\Blog\App\Models\BlogCategory;
use Modules\Category\Entities\Category;
use Modules\Currency\App\Models\Currency;
use Modules\GlobalSetting\App\Models\GlobalSetting;
use Modules\Language\App\Models\Language;
use Modules\Page\App\Models\CustomPage;
use Modules\Page\App\Models\Footer;
use Modules\Wishlist\App\Models\Wishlist;

/**
 * AppServiceProvider
 *
 * Bootstraps application-wide settings, timezone configuration, and
 * global view composer data. Caches GlobalSettings permanently and
 * shares common data (language list, currencies, footer, wishlists)
 * with all views for layout rendering. Also integrates theme data
 * when the theme service is available.
 *
 * @package App\Providers
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
    //
    }

    /**
     * Bootstrap any application services.
     *
     * Loads global settings from cache, configures timezone, and
     * registers a global view composer for shared layout data.
     */
    public function boot(): void
    {
        try {
            // ── Cache Global Settings ───────────────────────────────────
            $setting = Cache::rememberForever('setting', function () {
                $setting_data = GlobalSetting::all();

                $setting = [];
                foreach ($setting_data as $data_item) {
                    $setting[$data_item->key] = $data_item->value;
                }

                return (object)$setting;
            });

            // ── Timezone Configuration ──────────────────────────────────
            $timezone = $setting->timezone ?? 'UTC';
            config(['app.timezone' => $timezone]);
            date_default_timezone_set($timezone);

            // ── Global View Composer ────────────────────────────────────
            View::composer('*', function ($view) {
                $general_setting = Cache::get('setting');

                $language_list = Language::where('status', 1)->get();
                $currency_list = Currency::where('status', 'active')->get();
                $custom_pages = CustomPage::where('status', 1)->get();
                $menu_categories = Category::where('status', 'enable')->latest()->get();
                $footer_blog_categories = BlogCategory::where('status', 1)->latest()->take(7)->get();
                $footer = Footer::first();

                // Build wishlist array for authenticated users
                $wishlist_array = [];
                if (Auth::guard('web')->check()) {
                    $wishlist_array = Wishlist::where('user_id', Auth::guard('web')->id())
                        ->pluck('item_id')
                        ->toArray();
                }

                $view->with([
                    'general_setting' => $general_setting,
                    'language_list' => $language_list,
                    'currency_list' => $currency_list,
                    'footer' => $footer,
                    'custom_pages' => $custom_pages,
                    'menu_categories' => $menu_categories,
                    'footer_blog_categories' => $footer_blog_categories,
                    'wishlist_array' => $wishlist_array,
                ]);
            });

            // ── Theme Integration ───────────────────────────────────────
            if (app()->bound('theme')) {
                $theme = app('theme');
                $currentTheme = $theme->current();

                view()->share('currentTheme', $currentTheme);
                view()->share('themeInfo', $theme->loadThemeInfo($currentTheme));

                if ($currentTheme && $theme->exists($currentTheme)) {
                    view()->share('themeAssets', $theme->getAssets());
                }
            }
        }
        catch (Exception $ex) {
            Log::info('AppServiceProvider: ' . $ex->getMessage());
            Artisan::call('optimize:clear');
        }
    }
}