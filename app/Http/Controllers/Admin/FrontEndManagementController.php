<?php

namespace App\Http\Controllers\Admin;

// ── Framework Dependencies ──────────────────────────────────────────────────
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

// ── Application Dependencies ────────────────────────────────────────────────
use App\Http\Controllers\Controller;
use App\Models\Frontend;
use App\Services\FrontendFieldService;

/**
 * FrontEndManagementController
 *
 * Powers the admin CMS for managing frontend content sections. Handles
 * both common (global) settings and theme-specific settings, supporting
 * multilingual content with separate translations per language code.
 * Images are only managed through the default (English) language.
 *
 * @package App\Http\Controllers\Admin
 */
class FrontEndManagementController extends Controller
{
    /** @var FrontendFieldService  Service for field type validation and processing */
    protected $fieldService;

    /**
     * Inject the FrontendFieldService dependency.
     *
     * @param  FrontendFieldService  $fieldService
     */
    public function __construct(FrontendFieldService $fieldService)
    {
        $this->fieldService = $fieldService;
    }

    // ════════════════════════════════════════════════════════════════════════
    // ── Section Listing ─────────────────────────────────────────────────────
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Display the frontend management index with all content sections.
     *
     * Merges common (global) and theme-specific settings, groups them
     * by page, and sorts by defined order.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        $commonSettings = app('theme')->getCommonSettings();
        $activeTheme = app('theme')->current();
        $themeSettings = app('theme')->getThemeSettings();

        $sections = array_merge($commonSettings, $themeSettings);
        $sections = $this->sortSectionsByOrder($sections);
        $sectionsByPage = $this->groupSectionsByPage($sections);

        $themes = app('theme')->all();

        return view('admin.frontend-management.index', compact(
            'sections', 'sectionsByPage', 'themes', 'activeTheme'
        ));
    }

    // ════════════════════════════════════════════════════════════════════════
    // ── Section Editing ─────────────────────────────────────────────────────
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Display the edit form for a specific content section.
     *
     * Loads the section's field definitions and current data values,
     * handling translations for non-English languages. Creates a new
     * translation entry if one doesn't exist for the requested language.
     *
     * @param  string  $key  Section identifier key
     * @return \Illuminate\Contracts\View\View
     */
    public function section($key)
    {
        $lang_code = request('lang_code', 'en');
        $sections = app('theme')->getMergedSettings();

        if (!isset($sections[$key])) {
            abort(404, "Section not found for key: $key");
        }

        $section = $sections[$key];
        $contentType = isset($section['content']) ? 'content' : (isset($section['element']) ? 'element' : null);

        if (!$contentType) {
            abort(404, "Content or Element not found for section: $key");
        }

        $dataKeys = $key . '.' . $contentType;
        $content = $section[$contentType];
        $frontend = Frontend::where('data_keys', $dataKeys)->first();

        // Resolve data values based on language
        if ($lang_code === 'en') {
            $dataValues = $frontend ? $frontend->data_values : [];
        }
        else {
            if ($frontend) {
                $translations = json_decode($frontend->data_translations, true) ?? [];
                $translation = collect($translations)->first(function ($item) use ($lang_code) {
                    return $item['language_code'] === $lang_code;
                });

                if ($translation) {
                    $dataValues = $translation['values'];
                }
                else {
                    // Create initial translation from English values
                    $dataValues = $frontend->data_values;
                    unset($dataValues['images']);

                    $translations[] = [
                        'language_code' => $lang_code,
                        'values' => $frontend->data_values,
                    ];
                    $frontend->data_translations = json_encode($translations);
                    $frontend->save();
                }
            }
            else {
                $dataValues = [];
            }
        }

        // Load theme info for theme-specific sections
        $themeInfo = null;
        if (isset($section['theme'])) {
            $themeInfo = app('theme')->loadThemeInfo($section['theme']);
        }

        $page_title = $section['name'] ?? trans('translate.Frontend Management');

        return view('admin.frontend-management.edit', compact(
            'page_title', 'key', 'content', 'dataValues',
            'frontend', 'contentType', 'lang_code', 'themeInfo'
        ));
    }

    // ════════════════════════════════════════════════════════════════════════
    // ── Section Storage ─────────────────────────────────────────────────────
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Store or update a content section's data.
     *
     * Processes field values according to their type definitions, handles
     * image uploads (English only), and manages multilingual translations.
     * Images are preserved across language switches and only modifiable
     * through the default English language.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $key  Section identifier key
     * @param  int|null  $id  Frontend record ID (null for new records)
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, $key, $id = null)
    {
        $lang_code = $request->get('lang_code');
        if (!$lang_code) {
            return back()->with('error', 'Language code is required');
        }

        // Load and validate section definition
        $sections = app('theme')->getMergedSettings();
        if (!isset($sections[$key])) {
            abort(404, "Section not found for key: $key");
        }

        $section = $sections[$key];
        $contentType = isset($section['content']) ? 'content' : (isset($section['element']) ? 'element' : null);
        if (!$contentType) {
            abort(404, "Content or Element not found for section: $key");
        }

        // ── Build Validation Rules ──────────────────────────────────────
        $rules = [];
        foreach ($section[$contentType] as $fieldName => $field) {
            if (is_array($field)) {
                $fieldType = $field['type'] ?? 'text';
                $fieldOptions = $field['options'] ?? [];

                // Make image fields optional if existing image is provided
                if ($fieldType === 'image' && $request->has("{$fieldName}_existing") && $request->get("{$fieldName}_existing")) {
                    $fieldOptions['required'] = false;
                }

                $rules[$fieldName] = $this->fieldService->getValidationRules($fieldType, $fieldOptions);
            }
        }

        // ── Process Field Values ────────────────────────────────────────
        $data = $request->except(['_token', '_method', 'type', 'lang_code']);
        $frontend = $id ?Frontend::findOrFail($id) : new Frontend();
        $existingData = $frontend->data_values ?? [];
        $translations = json_decode($frontend->data_translations ?? '[]', true) ?? [];
        $processedData = [];

        foreach ($section[$contentType] as $fieldName => $field) {
            if (is_array($field)) {
                $fieldType = $field['type'] ?? 'text';
                $fieldOptions = $field['options'] ?? [];

                // Handle direct image fields (English only)
                if ($fieldType === 'image' && $lang_code === 'en') {
                    $processedData[$fieldName] = $this->processSingleImage(
                        $request, $fieldName, $existingData[$fieldName] ?? null
                    );
                    continue;
                }

                // Handle images array (English only)
                if ($fieldName === 'images' && is_array($field) && $lang_code === 'en') {
                    $processedData[$fieldName] = $this->processImagesArray(
                        $request, $fieldName, $field, $existingData[$fieldName] ?? []
                    );
                    continue;
                }

                // Process other field types
                $processedData[$fieldName] = $this->fieldService->processFieldValue(
                    $data[$fieldName] ?? null, $fieldType, $fieldOptions
                );
            }
            else {
                $processedData[$fieldName] = $data[$fieldName] ?? $field;
            }
        }

        // ── Handle Translations ─────────────────────────────────────────
        if ($lang_code === 'en') {
            $this->saveEnglishContent($frontend, $processedData, $existingData, $translations);
        }
        else {
            $this->saveTranslatedContent($frontend, $processedData, $lang_code, $translations);
        }

        // Set data keys for new records
        if (!$frontend->data_keys) {
            $frontend->data_keys = $key . '.' . $contentType;
        }

        $frontend->data_translations = json_encode($translations);
        $frontend->save();

        return redirect()->back()->with('success', trans('translate.Update successfully'));
    }

    // ════════════════════════════════════════════════════════════════════════
    // ── Field Templates ─────────────────────────────────────────────────────
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Get an HTML field template for dynamic frontend form building.
     *
     * Returns rendered HTML for the requested field type with placeholder
     * values that can be replaced by the frontend JavaScript.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function getFieldTemplate(Request $request)
    {
        $type = $request->get('type', 'text');

        if (!in_array($type, array_keys(config('frontend-fields.field_types')))) {
            return response()->json(['error' => 'Invalid field type'], 400);
        }

        $viewName = config("frontend-fields.field_types.{$type}.view");
        if (!$viewName) {
            return response()->json(['error' => 'Template not found'], 404);
        }

        $html = view($viewName, [
            'name' => '__NAME__',
            'label' => '__LABEL__',
            'value' => null,
            'required' => '__REQUIRED__',
            'help' => null,
        ])->render();

        return response($html);
    }

    // ════════════════════════════════════════════════════════════════════════
    // ── Private Helpers ─────────────────────────────────────────────────────
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Process a single image field upload, keeping existing if no new file.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $fieldName
     * @param  string|null  $existingImage
     * @return string|null  Path to the saved image
     */
    private function processSingleImage(Request $request, string $fieldName, ?string $existingImage): ?string
    {
        if ($request->hasFile($fieldName)) {
            $image = $request->file($fieldName);
            $imageName = time() . '_' . $fieldName . '.' . $image->getClientOriginalExtension();

            // Delete old file
            if ($existingImage && File::exists(public_path($existingImage))) {
                unlink(public_path($existingImage));
            }

            $image->move(public_path('uploads/website-images'), $imageName);
            return 'uploads/website-images/' . $imageName;
        }

        if ($request->has("{$fieldName}_existing")) {
            $existing = $request->get("{$fieldName}_existing");
            if (!empty($existing)) {
                return $existing;
            }
        }

        return $existingImage;
    }

    /**
     * Process an array of image uploads for multi-image fields.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $fieldName
     * @param  array  $field  Field definition array
     * @param  array  $existingImages  Currently stored images
     * @return array  Processed image paths keyed by image key
     */
    private function processImagesArray(Request $request, string $fieldName, array $field, array $existingImages = []): array
    {
        $processedImages = [];

        foreach ($field as $imageKey => $imageOptions) {
            $fileKey = "{$fieldName}_{$imageKey}";

            if ($request->hasFile($fileKey) || $request->hasFile($imageKey)) {
                // Upload new image (try prefixed key first, then direct key)
                $image = $request->hasFile($fileKey) ? $request->file($fileKey) : $request->file($imageKey);
                $imageName = time() . '_' . $imageKey . '.' . $image->getClientOriginalExtension();

                // Delete old file
                $oldImage = $existingImages[$imageKey] ?? null;
                if ($oldImage && File::exists(public_path($oldImage))) {
                    unlink(public_path($oldImage));
                }

                $image->move(public_path('uploads/website-images'), $imageName);
                $processedImages[$imageKey] = 'uploads/website-images/' . $imageName;
            }
            else {
                // Preserve existing image
                $existingImageKey = "{$fieldName}_{$imageKey}_existing";
                if ($request->has($existingImageKey) && !empty($request->get($existingImageKey))) {
                    $processedImages[$imageKey] = $request->get($existingImageKey);
                }
                elseif (isset($existingImages[$imageKey])) {
                    $processedImages[$imageKey] = $existingImages[$imageKey];
                }
            }
        }

        return $processedImages;
    }

    /**
     * Save English (default) content and update translations.
     *
     * @param  Frontend  $frontend
     * @param  array  $processedData
     * @param  array  $existingData
     * @param  array  &$translations  By reference — updated in place
     */
    private function saveEnglishContent(Frontend $frontend, array $processedData, array $existingData, array &$translations): void
    {
        // Preserve existing images if not updated
        if (isset($existingData['images']) && !isset($processedData['images'])) {
            $processedData['images'] = $existingData['images'];
        }

        $frontend->data_values = $processedData;

        // Update or create English translation entry
        $translationExists = false;
        foreach ($translations as $key => $translation) {
            if ($translation['language_code'] === 'en') {
                $translations[$key]['values'] = $processedData;
                $translationExists = true;
                break;
            }
        }

        if (!$translationExists) {
            $translations[] = ['language_code' => 'en', 'values' => $processedData];
        }
    }

    /**
     * Save translated content for a non-English language.
     *
     * Images from the English version are preserved in the translation
     * to maintain visual consistency across languages.
     *
     * @param  Frontend  $frontend
     * @param  array  $processedData
     * @param  string  $lang_code
     * @param  array  &$translations  By reference — updated in place
     */
    private function saveTranslatedContent(Frontend $frontend, array $processedData, string $lang_code, array &$translations): void
    {
        $translatedData = $processedData;

        // Preserve English images in translation
        if (isset($frontend->data_values['images'])) {
            $translatedData['images'] = $frontend->data_values['images'];
        }

        // Update or create translation entry
        $translationExists = false;
        foreach ($translations as $key => $translation) {
            if ($translation['language_code'] === $lang_code) {
                $translations[$key]['values'] = $translatedData;
                $translationExists = true;
                break;
            }
        }

        if (!$translationExists) {
            $translations[] = ['language_code' => $lang_code, 'values' => $translatedData];
        }

        // Handle new records without English data
        if (empty($frontend->data_values)) {
            $frontend->data_values = [];
            $hasEnglish = collect($translations)->contains('language_code', 'en');
            if (!$hasEnglish) {
                $translations[] = ['language_code' => 'en', 'values' => []];
            }
        }
    }

    /**
     * Sort sections by their defined order, defaulting to 999.
     *
     * @param  array  $sections
     * @return array  Sorted sections
     */
    private function sortSectionsByOrder(array $sections): array
    {
        uasort($sections, function ($a, $b) {
            return ($a['order'] ?? 999) <=> ($b['order'] ?? 999);
        });
        return $sections;
    }

    /**
     * Group sections by their page assignment for tabbed display.
     *
     * Ensures 'global' and 'home' pages appear first, followed by
     * remaining pages in alphabetical order.
     *
     * @param  array  $sections
     * @return array  Sections grouped by page
     */
    private function groupSectionsByPage(array $sections): array
    {
        $sectionsByPage = [];

        foreach ($sections as $key => $section) {
            $page = $section['page'] ?? 'other';
            $sectionsByPage[$page][$key] = $section;
        }

        uksort($sectionsByPage, function ($a, $b) {
            if ($a === 'global')
                return -1;
            if ($b === 'global')
                return 1;
            if ($a === 'home')
                return -1;
            if ($b === 'home')
                return 1;
            return strcmp($a, $b);
        });

        return $sectionsByPage;
    }
}