<?php

namespace App\Http\Controllers\Admin;

// ── Framework Dependencies ──────────────────────────────────────────────────
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

// ── Application Dependencies ────────────────────────────────────────────────
use App\Http\Controllers\Controller;
use App\Http\Requests\EditProfileRequest;
use App\Http\Requests\PasswordChangeRequest;

/**
 * ProfileController (Admin)
 *
 * Handles admin profile management including viewing/updating profile
 * information, uploading profile images, and changing passwords.
 *
 * @package App\Http\Controllers\Admin
 */
class ProfileController extends Controller
{
    /**
     * Create a new controller instance.
     * Applies admin authentication middleware.
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    // ════════════════════════════════════════════════════════════════════════
    // ── Profile Management ──────────────────────────────────────────────────
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Display the admin profile edit form.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function edit_profile()
    {
        $admin = Auth::guard('admin')->user();

        return view('admin.edit_profile', ['admin' => $admin]);
    }

    /**
     * Update the admin's profile information and optional image.
     *
     * Handles text field updates (name, email, social links, etc.) and
     * image upload with old image cleanup. Uses Intervention Image for
     * processing uploaded profile photos.
     *
     * @param  \App\Http\Requests\EditProfileRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function profile_update(EditProfileRequest $request)
    {
        $admin = Auth::guard('admin')->user();

        // Update text fields
        $admin->name = $request->name;
        $admin->email = $request->email;
        $admin->designation = $request->designation;
        $admin->facebook = $request->facebook;
        $admin->linkedin = $request->linkedin;
        $admin->twitter = $request->twitter;
        $admin->instagram = $request->instagram;
        $admin->about_me = $request->about_me;
        $admin->save();

        // Handle profile image upload
        if ($request->hasFile('image')) {
            $old_image = $admin->image;
            $user_image = $request->image;
            $extention = $user_image->getClientOriginalExtension();
            $image_name = Str::slug($request->name) . date('-Y-m-d-h-i-s-') . rand(999, 9999) . '.' . $extention;
            $image_name = 'uploads/website-images/' . $image_name;

            Image::make($request->image)->save(public_path($image_name));

            $admin->image = $image_name;
            $admin->save();

            // Clean up old profile image
            if ($old_image && File::exists(public_path() . '/' . $old_image)) {
                unlink(public_path() . '/' . $old_image);
            }
        }

        $notify_message = trans('translate.Update successfully');
        $notify_message = ['message' => $notify_message, 'alert-type' => 'success'];
        return redirect()->back()->with($notify_message);
    }

    // ════════════════════════════════════════════════════════════════════════
    // ── Password Management ─────────────────────────────────────────────────
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Update the admin's password after verifying the current one.
     *
     * @param  \App\Http\Requests\PasswordChangeRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update_password(PasswordChangeRequest $request)
    {
        $admin = Auth::guard('admin')->user();

        if (Hash::check($request->current_password, $admin->password)) {
            $admin->password = Hash::make($request->password);
            $admin->save();

            $notify_message = trans('translate.Password changed successfully');
            $notify_message = ['message' => $notify_message, 'alert-type' => 'success'];
            return redirect()->back()->with($notify_message);
        }

        $notify_message = trans('translate.Current password does not match');
        $notify_message = ['message' => $notify_message, 'alert-type' => 'error'];
        return redirect()->back()->with($notify_message);
    }
}