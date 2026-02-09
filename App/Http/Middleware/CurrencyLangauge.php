<?php

namespace App\Http\Middleware;

// ── Framework Dependencies ──────────────────────────────────────────────────
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

// ── Module Models ───────────────────────────────────────────────────────────
use Modules\Currency\App\Models\Currency;
use Modules\Language\App\Models\Language;

/**
 * CurrencyLanguage Middleware
 *
 * Initializes the application's language locale and currency settings
 * from the session on each request. If no session values exist, loads
 * defaults from the database. Ensures the session always contains
 * valid language and currency data even if the stored values become
 * invalid (e.g., a language or currency is deleted).
 *
 * @package App\Http\Middleware
 */
class CurrencyLangauge
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
        $this->initializeLanguage();
        $this->initializeCurrency();

        // Ensure locale is always set from the session
        app()->setLocale(Session::get('front_lang', 'en'));

        return $next($request);
    }

    /**
     * Initialize language settings from session or database defaults.
     */
    private function initializeLanguage(): void
    {
        if (!Session::get('front_lang')) {
            // No session — load default language from database
            $lang = Language::where('is_default', 'Yes')->first()
                ?? Language::where('id', 1)->first();

            $this->setLanguageSession($lang);
        }
        else {
            // Validate that the stored language still exists
            $exists = Language::where('lang_code', Session::get('front_lang'))->first();
            if (!$exists) {
                Session::put('front_lang', 'en');
                Session::put('lang_dir', 'left_to_right');
                Session::put('front_lang_name', 'English');
            }
        }
    }

    /**
     * Initialize currency settings from session or database defaults.
     */
    private function initializeCurrency(): void
    {
        if (!Session::get('currency_code')) {
            // No session — load default currency from database
            $currency = Currency::where('is_default', 'yes')->first()
                ?? Currency::where('id', 1)->first();

            $this->setCurrencySession($currency);
        }
        else {
            // Validate that the stored currency still exists
            $exists = Currency::where('currency_code', Session::get('currency_code'))->first();
            if (!$exists) {
                $fallback = Currency::where('id', 1)->first();
                $this->setCurrencySession($fallback);
            }
        }
    }

    /**
     * Persist language data to the session.
     *
     * @param  \Modules\Language\App\Models\Language  $lang
     */
    private function setLanguageSession($lang): void
    {
        Session::put('front_lang', $lang->lang_code);
        Session::put('lang_dir', $lang->lang_direction);
        Session::put('front_lang_name', $lang->lang_name);
    }

    /**
     * Persist currency data to the session.
     *
     * @param  \Modules\Currency\App\Models\Currency  $currency
     */
    private function setCurrencySession($currency): void
    {
        Session::put('currency_name', $currency->currency_name);
        Session::put('currency_code', $currency->currency_code);
        Session::put('currency_icon', $currency->currency_icon);
        Session::put('currency_rate', $currency->currency_rate);
        Session::put('currency_position', $currency->currency_position);
    }
}