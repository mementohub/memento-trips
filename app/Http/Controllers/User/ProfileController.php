<?php

namespace App\Http\Controllers\User;

// ── Framework Dependencies ──────────────────────────────────────────────────
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

// ── Application Dependencies ────────────────────────────────────────────────
use App\Http\Controllers\Controller;
use App\Http\Requests\BecomeAgencyRequest;
use App\Http\Requests\EditStudentProfileRequest;
use App\Http\Requests\PasswordChangeRequest;

// ── Module Models ───────────────────────────────────────────────────────────
use Modules\Coupon\App\Models\Coupon;
use Modules\Coupon\App\Models\CouponHistory;
use Modules\PaymentWithdraw\App\Models\SellerWithdraw;
use Modules\SupportTicket\App\Models\MessageDocument;
use Modules\SupportTicket\App\Models\SupportTicket;
use Modules\SupportTicket\App\Models\SupportTicketMessage;
use Modules\TourBooking\App\Models\Booking;
use Modules\Wishlist\App\Models\Wishlist;

/**
 * User ProfileController
 *
 * Manages the regular user's profile area including dashboard stats,
 * profile editing with image uploads, password changes, agency
 * application submission, and account deletion with full data cleanup.
 *
 * @package App\Http\Controllers\User
 */
class ProfileController extends Controller
{
    // ════════════════════════════════════════════════════════════════════════
    // ── Dashboard ───────────────────────────────────────────────────────────
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Display the user dashboard with booking statistics.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function dashboard()
    {
        $user = Auth::guard('web')->user();

        $wishlists = Wishlist::where('user_id', $user->id)->count();
        $support_tickets = SupportTicket::where('author_id', $user->id)
            ->where('admin_type', 'admin')
            ->latest()
            ->count();

        $bookings = Booking::with(['service:id,title,location'])
            ->where('user_id', $user->id)
            ->latest()
            ->take(15)
            ->get();

        $total_booking = Booking::where('user_id', $user->id)->count();

        $total_transaction = Booking::where('user_id', $user->id)
            ->where('payment_status', 'success')
            ->sum('total');

        return view('user.dashboard', [
            'wishlists' => $wishlists,
            'support_tickets' => $support_tickets,
            'bookings' => $bookings,
            'total_booking' => $total_booking,
            'total_transaction' => $total_transaction,
        ]);
    }

    // ════════════════════════════════════════════════════════════════════════
    // ── Profile Management ──────────────────────────────────────────────────
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Display the profile edit form.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function edit_profile()
    {
        $user = Auth::guard('web')->user();
        return view('user.edit_profile', ['user' => $user]);
    }

    /**
     * Update the user's profile information and optional image.
     *
     * Handles text field updates and image upload with old image cleanup.
     *
     * @param  \App\Http\Requests\EditStudentProfileRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update_profile(EditStudentProfileRequest $request)
    {
        $user = Auth::guard('web')->user();
        $user->name = $request->name;
        $user->phone = $request->phone;
        $user->address = $request->address;
        $user->gender = $request->gender;
        $user->save();

        // Handle profile image upload
        if ($request->file('image')) {
            $old_image = $user->image;
            $user_image = $request->image;
            $extention = $user_image->getClientOriginalExtension();
            $image_name = Str::slug($user->name) . date('-Y-m-d-h-i-s-') . rand(999, 9999) . '.' . $extention;
            $image_name = 'uploads/custom-images/' . $image_name;

            Image::read($user_image)->save(public_path() . '/' . $image_name);

            $user->image = $image_name;
            $user->save();

            // Clean up old image
            if ($old_image && File::exists(public_path() . '/' . $old_image)) {
                unlink(public_path() . '/' . $old_image);
            }
        }

        $notify_message = ['message' => trans('translate.Updated successfully'), 'alert-type' => 'success'];
        return redirect()->back()->with($notify_message);
    }

    // ════════════════════════════════════════════════════════════════════════
    // ── Password Management ─────────────────────────────────────────────────
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Display the password change form.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function change_password()
    {
        return view('user.change_password');
    }

    /**
     * Update the user's password after verifying the current one.
     *
     * @param  \App\Http\Requests\PasswordChangeRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update_password(PasswordChangeRequest $request)
    {
        $user = Auth::guard('web')->user();

        if (Hash::check($request->current_password, $user->password)) {
            $user->password = Hash::make($request->password);
            $user->save();

            $notify_message = ['message' => trans('translate.Password changed successfully'), 'alert-type' => 'success'];
            return redirect()->back()->with($notify_message);
        }

        $notify_message = ['message' => trans('translate.Current password does not match'), 'alert-type' => 'error'];
        return redirect()->back()->with($notify_message);
    }

    // ════════════════════════════════════════════════════════════════════════
    // ── Agency Application ──────────────────────────────────────────────────
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Display the "Become an Agency" application form.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\View\View
     */
    public function create_agency(Request $request)
    {
        $user = Auth::guard('web')->user();
        return view('user.create_agency', ['user' => $user]);
    }

    /**
     * Submit an agency joining application.
     *
     * Saves the agency details, uploads the logo if provided, and
     * sets the joining request status to 'pending' for admin review.
     *
     * @param  \App\Http\Requests\BecomeAgencyRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function agency_application(BecomeAgencyRequest $request)
    {
        $user = Auth::guard('web')->user();

        $user->agency_name = $request->agency_name;
        $user->agency_slug = $request->agency_slug;
        $user->about_me = $request->about_me;
        $user->country = $request->country;
        $user->state = $request->state;
        $user->city = $request->city;
        $user->address = $request->address;
        $user->facebook = $request->facebook;
        $user->linkedin = $request->linkedin;
        $user->twitter = $request->twitter;
        $user->instagram = $request->instagram;
        $user->website = $request->website;
        $user->location_map = $request->location_map;
        $user->instructor_joining_request = 'pending';
        $user->save();

        // Upload agency logo
        if ($request->hasFile('agency_logo')) {
            $file = $request->file('agency_logo');
            $imageName = 'uploads/custom-images/' . Str::slug($user->agency_name) . '-' . now()->format('YmdHis') . '-' . rand(1000, 9999) . '.' . $file->getClientOriginalExtension();

            Image::read($file)->save(public_path($imageName));

            $user->agency_logo = $imageName;
            $user->save();
        }

        $notify_message = ['message' => trans('translate.Agency joining request send to admin. please awaiting for approval'), 'alert-type' => 'success'];
        return redirect()->back()->with($notify_message);
    }

    // ════════════════════════════════════════════════════════════════════════
    // ── Account Deletion ────────────────────────────────────────────────────
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Display the account deletion confirmation page.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function account_delete()
    {
        return view('user.account_delete');
    }

    /**
     * Permanently delete the user account and all associated data.
     *
     * Requires current password verification. Cascades deletion to:
     * profile image, coupons, coupon history, withdrawals, wishlists,
     * and support tickets with messages and attachments.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function confirm_account_delete(Request $request)
    {
        $user = Auth::guard('web')->user();

        $request->validate([
            'current_password' => 'required',
        ], [
            'current_password.required' => trans('translate.Current password is required'),
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            $notify_message = ['message' => trans('translate.Current password does not match'), 'alert-type' => 'error'];
            return redirect()->back()->with($notify_message);
        }

        // Delete profile image
        if ($user->image && File::exists(public_path() . '/' . $user->image)) {
            unlink(public_path() . '/' . $user->image);
        }

        // Cascade delete associated records
        $user_id = $user->id;
        Coupon::where('seller_id', $user_id)->delete();
        CouponHistory::where('seller_id', $user_id)->delete();
        CouponHistory::where('buyer_id', $user_id)->delete();
        SellerWithdraw::where('seller_id', $user_id)->delete();
        Wishlist::where('user_id', $user_id)->delete();

        // Delete support tickets with messages and file attachments
        $support_tickets = SupportTicket::where('author_id', $user->id)->latest()->get();
        foreach ($support_tickets as $support_ticket) {
            $ticket_messages = SupportTicketMessage::with('documents')
                ->where('support_ticket_id', $support_ticket->id)
                ->get();

            foreach ($ticket_messages as $ticket_message) {
                $documents = MessageDocument::where('message_id', $ticket_message->id)
                    ->where('model_name', 'SupportTicketMessage')
                    ->get();

                foreach ($documents as $document) {
                    if ($document->file_name && File::exists(public_path('uploads/custom-images') . '/' . $document->file_name)) {
                        unlink(public_path('uploads/custom-images') . '/' . $document->file_name);
                    }
                    $document->delete();
                }
                $ticket_message->delete();
            }
            $support_ticket->delete();
        }

        $user->delete();
        Auth::guard('web')->logout();

        $notify_message = ['message' => trans('translate.Your account deleted successful'), 'alert-type' => 'success'];
        return redirect()->route('user.login')->with($notify_message);
    }
}