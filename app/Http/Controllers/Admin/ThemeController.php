<?php

namespace App\Http\Controllers\Admin;

// ── Framework Dependencies ──────────────────────────────────────────────────
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;

// ── Application Dependencies ────────────────────────────────────────────────
use App\Facades\Theme;
use App\Http\Controllers\Controller;

/**
 * ThemeController
 *
 * Manages the platform's theme system from the admin panel. Provides
 * listing installed themes, viewing theme details, activating a theme
 * as the system default, and deleting non-active themes.
 *
 * @package App\Http\Controllers\Admin
 */
class ThemeController extends Controller
{
    /**
     * Display a listing of all installed themes.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        $themes = Theme::all();
        $active_theme = Theme::getActive();

        return view('admin.themes.index', compact('themes', 'active_theme'));
    }

    /**
     * Show the form for creating a new theme.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        return view('admin.themes.create');
    }

    /**
     * Activate the specified theme as the system default.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $theme  Theme identifier slug
     * @return \Illuminate\Http\RedirectResponse
     */
    public function activate(Request $request, $theme)
    {
        if (Theme::exists($theme) && Theme::activate($theme)) {
            $notify_message = ['message' => "Theme '{$theme}' has been activated.", 'alert-type' => 'success'];
        }
        else {
            $notify_message = ['message' => "Failed to activate theme '{$theme}'.", 'alert-type' => 'error'];
        }

        return redirect()->route('admin.themes.index')->with($notify_message);
    }

    /**
     * Show the details for a specific theme.
     *
     * @param  string  $theme  Theme identifier slug
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show($theme)
    {
        if (!Theme::exists($theme)) {
            $notify_message = ['message' => "Theme '{$theme}' does not exist.", 'alert-type' => 'error'];
            return redirect()->route('admin.themes.index')->with($notify_message);
        }

        $themeInfo = Theme::loadThemeInfo($theme);
        $screenshot = file_exists(base_path("cms/themes/{$theme}/screenshot.png"))
            ? asset("cms/themes/{$theme}/screenshot.png")
            : asset('backend/img/placeholder-image.jpg');

        return view('admin.themes.show', compact('theme', 'themeInfo', 'screenshot'));
    }

    /**
     * Remove the specified theme from the filesystem.
     *
     * Prevents deletion of the currently active theme.
     *
     * @param  string  $theme  Theme identifier slug
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($theme)
    {
        if ($theme === Theme::getActive()) {
            $notify_message = ['message' => "Cannot delete the active theme.", 'alert-type' => 'error'];
            return redirect()->route('admin.themes.index')->with($notify_message);
        }

        if (Theme::exists($theme)) {
            File::deleteDirectory(base_path("cms/themes/{$theme}"));
            $notify_message = ['message' => "Theme '{$theme}' has been deleted.", 'alert-type' => 'success'];
        }
        else {
            $notify_message = ['message' => "Theme '{$theme}' does not exist.", 'alert-type' => 'error'];
        }

        return redirect()->route('admin.themes.index')->with($notify_message);
    }
}