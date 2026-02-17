<?php

namespace App\Http\Controllers\Admin;

// ── Framework Dependencies ──────────────────────────────────────────────────
use Illuminate\Http\Request;
use Illuminate\Support\Str;

// ── Application Dependencies ────────────────────────────────────────────────
use App\Http\Controllers\Controller;

/**
 * UploadController
 *
 * Handles file uploads from rich text editors (e.g., WYSIWYG editors)
 * used in the admin CMS frontend management sections.
 *
 * @package App\Http\Controllers\Admin
 */
class UploadController extends Controller
{
    /**
     * Handle image upload from an HTML editor.
     *
     * Validates the uploaded image, saves it to the public uploads/editor
     * directory with a random filename, and returns the public URL.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function editorImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $image = $request->file('image');
        $filename = Str::random(40) . '.' . $image->getClientOriginalExtension();

        // Ensure upload directory exists
        $uploadDir = 'uploads/editor';
        if (!file_exists(public_path($uploadDir))) {
            mkdir(public_path($uploadDir), 0755, true);
        }

        $image->move(public_path($uploadDir), $filename);

        return response()->json([
            'url' => asset($uploadDir . '/' . $filename),
        ]);
    }
}