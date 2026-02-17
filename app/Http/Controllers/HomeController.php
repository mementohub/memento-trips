<?php

namespace App\Http\Controllers;

// ── Framework Dependencies ──────────────────────────────────────────────────
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

// ── Application Dependencies ────────────────────────────────────────────────
use App\Facades\Theme;
use App\Models\User;
use App\Rules\Captcha;

// ── Module Models ───────────────────────────────────────────────────────────
use Modules\Blog\App\Models\Blog;
use Modules\Blog\App\Models\BlogCategory;
use Modules\Blog\App\Models\BlogComment;
use Modules\Category\App\Models\Category;
use Modules\Currency\App\Models\Currency;
use Modules\FAQ\App\Models\Faq;
use Modules\GlobalSetting\App\Models\GlobalSetting;
use Modules\Language\App\Models\Language;
use Modules\Page\App\Models\ContactUs;
use Modules\Page\App\Models\CustomPage;
use Modules\Page\App\Models\PrivacyPolicy;
use Modules\Page\App\Models\TermAndCondition;
use Modules\Partner\App\Models\Partner;
use Modules\SeoSetting\App\Models\SeoSetting;
use Modules\Team\App\Models\Team;
use Modules\Testimonial\App\Models\Testimonial;

/**
 * HomeController
 *
 * Handles all public-facing pages of the platform including the homepage,
 * about page, blog listing/detail, contact, FAQ, pricing, legal pages,
 * team pages, and locale/currency/theme switching.
 *
 * @package App\Http\Controllers
 */
class HomeController extends Controller
{
    // ════════════════════════════════════════════════════════════════════════
    // ── Homepage & Theme ────────────────────────────────────────────────────
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Display the homepage with theme-specific content.
     *
     * Loads the currently active theme, resolves its settings from the
     * database, and renders the theme's index view with all required data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index(Request $request)
    {
        try {
            $seo_setting = SeoSetting::where('id', 1)->first();
            $breadcrumb_title = trans('translate.Home');

            // Allow temporary theme preview via ?theme= query parameter
            if ($request->has('theme')) {
                $requestedTheme = $request->get('theme');
                if (theme()->exists($requestedTheme)) {
                    theme()->setTemporary($requestedTheme);
                }
            }

            $selectedTheme = theme()->current();
            $data = [];

            // Load theme-specific content sections from settings.json
            $themeSettings = theme()->getThemeSettings();
            foreach ($themeSettings as $key => $section) {
                $contentKey = $key . '.content';
                $contentData = getContent($contentKey, true);
                if ($contentData) {
                    $data[str_replace($selectedTheme . '_', '', $key)] = $contentData;
                }
            }

            // Common data shared across all themes
            $data['seo_setting'] = $seo_setting;
            $data['breadcrumb_title'] = $breadcrumb_title;
            $data['social_links'] = getContent('social_links.element');
            $data['categories'] = Category::take(3)->get();
            $data['theme_info'] = theme()->loadThemeInfo($selectedTheme);
            $data['current_theme'] = $selectedTheme;

            return theme()->view('index', $data);
        }
        catch (\Exception $e) {
            Log::error('Error in HomeController index: ' . $e->getMessage());
            return redirect('404');
        }
    }

    /**
     * Switch to a different theme permanently.
     *
     * Validates that the requested theme exists and activates it as the
     * system default via the database-backed theme manager.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $theme  Theme identifier slug
     * @return \Illuminate\Http\RedirectResponse
     */
    public function switchTheme(Request $request, $theme)
    {
        if (!theme()->exists($theme)) {
            $notify_message = trans('translate.Theme not found');
            return back()->with(['message' => $notify_message, 'alert-type' => 'error']);
        }

        if (theme()->activate($theme)) {
            $notify_message = trans('translate.Theme switched successfully');
            return back()->with(['message' => $notify_message, 'alert-type' => 'success']);
        }

        $notify_message = trans('translate.Error switching theme');
        return back()->with(['message' => $notify_message, 'alert-type' => 'error']);
    }

    /**
     * Preview a theme variation without permanently activating it.
     *
     * Creates a temporary theme instance, loads its views and settings,
     * and renders the index page using that theme's namespace.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function themeVariation(Request $request)
    {
        try {
            $requestedTheme = $request->query('theme');

            if (!$requestedTheme || !theme()->exists($requestedTheme)) {
                return redirect()->route('home');
            }

            // Create temporary theme instance for preview
            $tempTheme = new \App\Themes\Core\Theme($requestedTheme);
            $themePath = $tempTheme->getThemePath($requestedTheme);

            // Load theme-specific content sections
            $data = [];
            $themeSettings = $tempTheme->getThemeSettings();
            foreach ($themeSettings as $key => $section) {
                $contentKey = $key . '.content';
                $contentData = getContent($contentKey, true);
                if ($contentData) {
                    $data[str_replace($requestedTheme . '_', '', $key)] = $contentData;
                }
            }

            // Common data
            $seo_setting = SeoSetting::where('id', 1)->first();
            $data['seo_setting'] = $seo_setting;
            $data['breadcrumb_title'] = trans('translate.Home');
            $data['social_links'] = getContent('social_links.element');
            $data['categories'] = Category::take(3)->get();
            $data['theme_info'] = $tempTheme->loadThemeInfo($requestedTheme);
            $data['current_theme'] = $requestedTheme;

            // Load theme functions file if it exists
            $functionsFile = $themePath . '/functions/functions.php';
            if (file_exists($functionsFile)) {
                include_once $functionsFile;
            }

            // Register the 'theme' view namespace for the requested theme
            $viewFactory = app('view');
            $viewFactory->addNamespace('theme', $themePath . '/views');

            return $viewFactory->make("theme::index", $data);
        }
        catch (\Exception $e) {
            Log::error('Theme variation error: ' . $e->getMessage());
            return redirect()->route('home');
        }
    }

    // ════════════════════════════════════════════════════════════════════════
    // ── Static Pages ────────────────────────────────────────────────────────
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Display the About Us page.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function about_us()
    {
        $seo_setting = SeoSetting::where('id', 3)->first();
        $breadcrumb_title = trans('translate.About Us');

        $about_us = getContent('about_page_about_section.content', true);
        $what_we_do = getContent('about_page_what_we_do.content', true);
        $about_cta = getContent('about_page_cta.content', true);

        return view('about_us', [
            'seo_setting' => $seo_setting,
            'breadcrumb_title' => $breadcrumb_title,
            'about_us' => $about_us,
            'what_we_do' => $what_we_do,
            'about_cta' => $about_cta,
        ]);
    }

    /**
     * Display the Contact Us page.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function contact_us()
    {
        $contact_us = ContactUs::first();
        $seo_setting = SeoSetting::where('id', 4)->first();
        $breadcrumb_title = trans('translate.Contact Us');

        return view('contact_us', [
            'contact_us' => $contact_us,
            'seo_setting' => $seo_setting,
            'breadcrumb_title' => $breadcrumb_title,
        ]);
    }

    /**
     * Display the FAQ page.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function faq()
    {
        $faq = getContent('faq_section.content', true);
        $footer_cta = getContent('footer_cta.content', true);
        $seo_setting = SeoSetting::where('id', 4)->first();
        $breadcrumb_title = trans('translate.Frequently Asked Question');

        return view('faq', [
            'faq' => $faq,
            'seo_setting' => $seo_setting,
            'breadcrumb_title' => $breadcrumb_title,
            'footer_cta' => $footer_cta,
        ]);
    }

    /**
     * Display the Pricing page.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function pricing()
    {
        $footer_cta = getContent('footer_cta.content', true);
        $seo_setting = SeoSetting::where('id', 4)->first();
        $breadcrumb_title = trans('translate.Pricing Plan');

        return view('pricing', [
            'seo_setting' => $seo_setting,
            'breadcrumb_title' => $breadcrumb_title,
            'footer_cta' => $footer_cta,
        ]);
    }

    /**
     * Display the Privacy Policy page.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function privacy_policy()
    {
        $privacy_policy = PrivacyPolicy::first();
        $seo_setting = SeoSetting::where('id', 9)->first();
        $breadcrumb_title = trans('translate.Privacy Policy');

        return view('privacy_policy', [
            'privacy_policy' => $privacy_policy,
            'seo_setting' => $seo_setting,
            'breadcrumb_title' => $breadcrumb_title,
        ]);
    }

    /**
     * Display the Terms & Conditions page.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function terms_conditions()
    {
        $terms_conditions = TermAndCondition::first();
        $seo_setting = SeoSetting::where('id', 6)->first();
        $breadcrumb_title = trans('translate.Terms & Conditions');

        return view('terms_conditions', [
            'terms_conditions' => $terms_conditions,
            'seo_setting' => $seo_setting,
            'breadcrumb_title' => $breadcrumb_title,
        ]);
    }

    /**
     * Display a dynamic custom page by slug.
     *
     * @param  string  $slug  URL slug of the custom page
     * @return \Illuminate\Contracts\View\View
     */
    public function custom_page($slug)
    {
        $custom_page = CustomPage::where('slug', $slug)->firstOrFail();
        $breadcrumb_title = $custom_page->page_name;

        return view('custom_page', [
            'custom_page' => $custom_page,
            'breadcrumb_title' => $breadcrumb_title,
        ]);
    }

    // ════════════════════════════════════════════════════════════════════════
    // ── Blog ────────────────────────────────────────────────────────────────
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Display the blog listing page with optional search and category filters.
     *
     * Supports two layout modes: full-width grid or sidebar layout.
     * Filters by category and search term across translated fields and tags.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\View\View
     */
    public function blogs(Request $request)
    {
        // Determine layout and pagination
        $page_view = 'blogs';
        $paginate_qty = 9;
        if ($request->page_view === 'blogs_with_sidebar') {
            $page_view = 'blogs_with_sidebar';
            $paginate_qty = 6;
        }

        $blogs = Blog::with('author')->where('status', 1);

        // Filter by category
        if ($request->category) {
            $blogs = $blogs->where('blog_category_id', $request->category);
        }

        // Search across translated title/description and tags
        if ($request->search) {
            $blogs = $blogs->where(function ($query) use ($request) {
                $query->whereHas('front_translate', function ($subQuery) use ($request) {
                        $subQuery->where('title', 'like', '%' . $request->search . '%')
                            ->orWhere('description', 'like', '%' . $request->search . '%');
                    }
                    )
                        ->orWhereJsonContains('tags', [['value' => $request->search]]);
                });
        }

        $blogs = $blogs->paginate($paginate_qty);
        $seo_setting = SeoSetting::where('id', 2)->first();
        $breadcrumb_title = trans('translate.Our Blogs');
        $latest_blogs = Blog::with('author')->where('status', 1)->take(5)->get();
        $blog_categories = BlogCategory::where('status', 1)->get();
        $tags_array = $this->extractBlogTags();

        return view($page_view, [
            'blogs' => $blogs,
            'seo_setting' => $seo_setting,
            'breadcrumb_title' => $breadcrumb_title,
            'latest_blogs' => $latest_blogs,
            'blog_categories' => $blog_categories,
            'tags_array' => $tags_array,
        ]);
    }

    /**
     * Display a single blog post and increment its view count.
     *
     * @param  string  $slug  URL slug of the blog post
     * @return \Illuminate\Contracts\View\View
     */
    public function blog($slug)
    {
        $blog = Blog::with('author')->where('status', 1)->where('slug', $slug)->firstOrFail();

        // Increment view counter
        $blog->views = $blog->views + 1;
        $blog->save();

        $blog_comments = BlogComment::where('blog_id', $blog->id)
            ->where('status', 1)
            ->latest()
            ->get();

        $breadcrumb_title = trans('translate.Blog Details');
        $latest_blogs = Blog::with('author')->where('status', 1)->take(5)->get();
        $blog_categories = BlogCategory::where('status', 1)->get();
        $tags_array = $this->extractBlogTags();

        return view('blog_detail', [
            'blog' => $blog,
            'blog_comments' => $blog_comments,
            'breadcrumb_title' => $breadcrumb_title,
            'latest_blogs' => $latest_blogs,
            'blog_categories' => $blog_categories,
            'tags_array' => $tags_array,
        ]);
    }

    /**
     * Store a new comment on a blog post.
     *
     * Comments are created with status=0 (pending moderation) and require
     * name, email, and comment text. reCAPTCHA validation is applied.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id  Blog post ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store_blog_comment(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|max:255|email',
            'comment' => 'required',
            'g-recaptcha-response' => new Captcha(),
        ], [
            'name.required' => trans('translate.Name is required'),
            'email.required' => trans('translate.Email is required'),
            'comment.required' => trans('translate.Comment is required'),
        ]);

        $blog_comment = new BlogComment();
        $blog_comment->blog_id = $id;
        $blog_comment->name = $request->name;
        $blog_comment->email = $request->email;
        $blog_comment->comment = $request->comment;
        $blog_comment->status = 0;
        $blog_comment->save();

        $notify_message = trans('translate.Comment submited successfully');
        $notify_message = ['message' => $notify_message, 'alert-type' => 'success'];
        return redirect()->back()->with($notify_message);
    }

    /**
     * Extract unique tags from all published blog posts.
     *
     * @return array  Flat array of unique tag strings
     */
    private function extractBlogTags(): array
    {
        $blog_for_tags = Blog::where('status', 1)->select('status', 'tags')->get();
        $tags_array = [];

        foreach ($blog_for_tags as $blog_for_tag) {
            if ($blog_for_tag->tags) {
                foreach (json_decode($blog_for_tag->tags) ?? [] as $blog_tag) {
                    if (!in_array($blog_tag->value, $tags_array)) {
                        $tags_array[] = $blog_tag->value;
                    }
                }
            }
        }

        return $tags_array;
    }

    // ════════════════════════════════════════════════════════════════════════
    // ── Team ────────────────────────────────────────────────────────────────
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Display the team listing page.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function teams()
    {
        $teams = Team::select('id', 'slug', 'image', 'facebook', 'twitter', 'linkedin', 'instagram')
            ->with('translate:id,team_id,lang_code,name,designation')
            ->latest()
            ->get();

        $seo_setting = SeoSetting::where('id', 7)->first();
        $breadcrumb_title = trans('translate.Our Teams');

        return view('teams', [
            'teams' => $teams,
            'seo_setting' => $seo_setting,
            'breadcrumb_title' => $breadcrumb_title,
        ]);
    }

    /**
     * Display a single team member's profile page.
     *
     * @param  string  $slug  URL slug of the team member
     * @return \Illuminate\Contracts\View\View
     */
    public function teamPerson($slug)
    {
        $team = Team::with('translate')->where('slug', $slug)->firstOrFail();
        $seo_setting = SeoSetting::where('id', 7)->first();
        $breadcrumb_title = trans('translate.Our Teams');

        return view('team_single', [
            'team' => $team,
            'seo_setting' => $seo_setting,
            'breadcrumb_title' => $breadcrumb_title,
        ]);
    }

    // ════════════════════════════════════════════════════════════════════════
    // ── Locale & Currency Switching ─────────────────────────────────────────
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Switch the frontend language/locale.
     *
     * Stores the selected language code, name, and direction in the session
     * so all subsequent requests render in the chosen language.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function language_switcher(Request $request)
    {
        $request_lang = Language::where('lang_code', $request->lang_code)->first();

        Session::put('front_lang', $request->lang_code);
        Session::put('front_lang_name', $request_lang->lang_name);
        Session::put('lang_dir', $request_lang->lang_direction);

        app()->setLocale($request->lang_code);

        $notify_message = trans('translate.Language switched successful');
        if (config('app.mode') == 'DEMO') {
            $notify_message = ['message' => $notify_message, 'alert-type' => 'success', 'demo_mode' => 'Demo mode not tranlsate all language'];
        }
        else {
            $notify_message = ['message' => $notify_message, 'alert-type' => 'success'];
        }

        return redirect()->back()->with($notify_message);
    }

    /**
     * Switch the active currency for price display.
     *
     * Stores the selected currency's name, code, icon, rate, and display
     * position in the session for use in price formatting helpers.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function currency_switcher(Request $request)
    {
        $request_currency = Currency::where('currency_code', $request->currency_code ?? 'USD')->first();

        Session::put('currency_name', $request_currency->currency_name);
        Session::put('currency_code', $request_currency->currency_code);
        Session::put('currency_icon', $request_currency->currency_icon);
        Session::put('currency_rate', $request_currency->currency_rate);
        Session::put('currency_position', $request_currency->currency_position);

        $notify_message = trans('translate.Currency switched successful');
        $notify_message = ['message' => $notify_message, 'alert-type' => 'success'];
        return redirect()->back()->with($notify_message);
    }

    // ════════════════════════════════════════════════════════════════════════
    // ── File Downloads ──────────────────────────────────────────────────────
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Download a file from the custom-images uploads directory.
     *
     * @param  string  $file  Filename to download
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download_file($file)
    {
        $filepath = public_path() . "/uploads/custom-images/" . $file;
        return response()->download($filepath);
    }
}