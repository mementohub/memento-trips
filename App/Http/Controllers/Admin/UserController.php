<?php

namespace App\Http\Controllers\Admin;

// ── Framework Dependencies ──────────────────────────────────────────────────
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

// ── Application Dependencies ────────────────────────────────────────────────
use App\Helper\EmailHelper;
use App\Http\Controllers\Controller;
use App\Mail\InstructorApproval;
use App\Models\User;

// ── Module Models ───────────────────────────────────────────────────────────
use Modules\Coupon\App\Models\Coupon;
use Modules\Coupon\App\Models\CouponHistory;
use Modules\EmailSetting\App\Models\EmailTemplate;
use Modules\GlobalSetting\App\Models\GlobalSetting;
use Modules\PaymentWithdraw\App\Models\SellerWithdraw;
use Modules\SupportTicket\App\Models\MessageDocument;
use Modules\SupportTicket\App\Models\SupportTicket;
use Modules\SupportTicket\App\Models\SupportTicketMessage;
use Modules\TourBooking\App\Models\Booking;
use Modules\TourBooking\App\Models\Service;
use Modules\Wishlist\App\Models\Wishlist;

/**
 * UserController (Admin)
 *
 * Manages users and agencies (sellers) from the admin panel. Provides
 * listing, viewing, updating, status toggling, and deletion for both
 * regular users and agency accounts. Also handles agency joining
 * request approval/rejection workflows with email notifications.
 *
 * @package App\Http\Controllers\Admin
 */
class UserController extends Controller
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
    // ── User Management ─────────────────────────────────────────────────────
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Display the list of active users.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function user_list()
    {
        $users = User::where('status', 'enable')->latest()->get();
        $title = trans('translate.User List');

        return view('admin.user.user_list', ['users' => $users, 'title' => $title]);
    }

    /**
     * Display the list of pending (disabled) users.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function pending_user()
    {
        $users = User::where('status', 'disable')->latest()->get();
        $title = trans('translate.Pending User');

        return view('admin.user.user_list', ['users' => $users, 'title' => $title]);
    }

    /**
     * Display a detailed view of a single user with booking statistics.
     *
     * @param  int  $id  User ID
     * @return \Illuminate\Contracts\View\View
     */
    public function user_show($id)
    {
        $user = User::findOrFail($id);
        $wallet_balance = 0.0;

        $totalConfirmedBookingCount = Booking::where('user_id', $user->id)
            ->where('booking_status', 'confirmed')
            ->count();

        $confirmAmount = Booking::where('user_id', $user->id)
            ->where('payment_status', 'success')
            ->sum('total');

        $user_bookings = Booking::with(['service:id,title,location'])
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        return view('admin.user.user_show', [
            'user' => $user,
            'total_confirmed_booking' => $totalConfirmedBookingCount,
            'confirm_amount' => $confirmAmount,
            'wallet_balance' => $wallet_balance,
            'user_bookings' => $user_bookings,
        ]);
    }

    /**
     * Update a user's profile information from the admin panel.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id  User ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $rules = [
            'name' => 'required',
            'phone' => 'required',
            'address' => 'required|max:220',
        ];
        $customMessages = [
            'name.required' => trans('translate.Name is required'),
            'phone.required' => trans('translate.Phone is required'),
            'address.required' => trans('translate.Address is required'),
        ];
        $this->validate($request, $rules, $customMessages);

        $user->name = $request->name;
        $user->phone = $request->phone;
        $user->address = $request->address;
        $user->gender = $request->gender;
        $user->status = $request->status ? 'enable' : 'disable';
        $user->is_top_seller = $request->is_top_seller ? 'enable' : 'disable';
        $user->save();

        $notify_message = trans('translate.User updated successful');
        $notify_message = ['message' => $notify_message, 'alert-type' => 'success'];
        return redirect()->back()->with($notify_message);
    }

    /**
     * Delete a user and all associated data.
     *
     * Cascades deletion to: coupons, coupon history, withdrawals,
     * wishlists, support tickets (with messages and attachments),
     * and the user's profile image.
     *
     * @param  int  $id  User ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function user_destroy($id)
    {
        $user = User::findOrFail($id);
        $user_id = $user->id;

        // Delete user profile image
        if ($user->image && File::exists(public_path() . '/' . $user->image)) {
            unlink(public_path() . '/' . $user->image);
        }

        // Delete associated records
        Coupon::where('seller_id', $user_id)->delete();
        CouponHistory::where('seller_id', $user_id)->delete();
        CouponHistory::where('buyer_id', $user_id)->delete();
        SellerWithdraw::where('seller_id', $user_id)->delete();
        Wishlist::where('user_id', $user_id)->delete();

        // Delete support tickets with messages and attachments
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

        $notify_message = trans('translate.Delete Successfully');
        $notify_message = ['message' => $notify_message, 'alert-type' => 'success'];
        return redirect()->route('admin.user-list')->with($notify_message);
    }

    /**
     * Toggle a user's active/disabled status via AJAX.
     *
     * @param  int  $id  User ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function user_status($id)
    {
        $user = User::findOrFail($id);
        $user->status = ($user->status == 'enable') ? 'disable' : 'enable';
        $user->save();

        $message = trans('translate.Status Changed Successfully');
        return response()->json($message);
    }

    // ════════════════════════════════════════════════════════════════════════
    // ── Agency (Seller) Management ──────────────────────────────────────────
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Display the list of active agencies (sellers).
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function seller_list()
    {
        $users = User::where('status', 'enable')->where('is_seller', 1)->latest()->get();
        $title = trans('translate.Agency List');

        return view('admin.seller.seller_list', ['users' => $users, 'title' => $title]);
    }

    /**
     * Display the list of pending (disabled) agencies.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function pending_seller()
    {
        $users = User::where('status', 'disable')->where('is_seller', 1)->latest()->get();
        $title = trans('translate.Pending Seller');

        return view('admin.seller.seller_list', ['users' => $users, 'title' => $title]);
    }

    /**
     * Display the list of pending agency joining requests.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function seller_joining_request()
    {
        $users = User::where('instructor_joining_request', 'pending')->latest()->get();
        $title = trans('translate.Agency Joining Request');

        return view('admin.seller.seller_joining_request', ['users' => $users, 'title' => $title]);
    }

    /**
     * Display the details of an agency joining request.
     *
     * @param  int  $user_id  User ID of the applicant
     * @return \Illuminate\Contracts\View\View
     */
    public function seller_joining_detail($user_id)
    {
        $user = User::findOrFail($user_id);
        $skills_expertises = json_decode($user->skills_expertise);

        return view('admin.seller.seller_joining_detail', [
            'user' => $user,
            'skills_expertises' => $skills_expertises,
        ]);
    }

    /**
     * Approve an agency joining request and send approval email.
     *
     * Sets the user as an approved seller and sends a notification
     * email using the InstructorApproval mailable (template ID 5).
     *
     * @param  int  $user_id  User ID to approve
     * @return \Illuminate\Http\RedirectResponse
     */
    public function seller_joining_approval($user_id)
    {
        $user = User::findOrFail($user_id);
        $user->instructor_joining_request = 'approved';
        $user->is_seller = 1;
        $user->save();

        // Send approval notification email
        EmailHelper::mail_setup();
        try {
            $template = EmailTemplate::find(5);
            $message = $template->description;
            $subject = $template->subject;
            $message = str_replace('{{user_name}}', $user->name, $message);

            Mail::to($user->email)->send(new InstructorApproval($message, $subject));
        }
        catch (Exception $ex) {
            Log::info($ex->getMessage());
        }

        $notify_message = trans('translate.Instructor application approval successful');
        $notify_message = ['message' => $notify_message, 'alert-type' => 'success'];
        return redirect()->back()->with($notify_message);
    }

    /**
     * Reject an agency joining request and send rejection email with reason.
     *
     * Sends a notification email using the InstructorApproval mailable
     * (template ID 6) with the rejection reason.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $user_id  User ID to reject
     * @return \Illuminate\Http\RedirectResponse
     */
    public function seller_joining_reject(Request $request, $user_id)
    {
        $user = User::findOrFail($user_id);
        $user->instructor_joining_request = 'rejected';
        $user->save();

        // Send rejection notification email
        EmailHelper::mail_setup();
        try {
            $template = EmailTemplate::find(6);
            $message = $template->description;
            $subject = $template->subject;
            $message = str_replace('{{user_name}}', $user->name, $message);
            $message = str_replace('{{reason}}', $request->reason, $message);

            Mail::to($user->email)->send(new InstructorApproval($message, $subject));
        }
        catch (Exception $ex) {
            Log::info($ex->getMessage());
        }

        $notify_message = trans('translate.A rejection reason send to instructor mail');
        $notify_message = ['message' => $notify_message, 'alert-type' => 'success'];
        return redirect()->back()->with($notify_message);
    }

    /**
     * Display a detailed view of an agency with financial statistics.
     *
     * Calculates total income, commission, net income, withdrawal
     * history, and current balance for the agency.
     *
     * @param  int  $id  User ID of the agency
     * @return \Illuminate\Contracts\View\View
     */
    public function seller_show($id)
    {
        $user = User::findOrFail($id);

        // ── Revenue Calculations for Agency ─────────────────────────────
        $myServicesIds = Service::where('user_id', $user->id)->pluck('id')->toArray();

        $total_income = Booking::whereIn('service_id', $myServicesIds)
            ->where('payment_status', 'success')
            ->sum('total');

        $commission_type = GlobalSetting::where('key', 'commission_type')->value('value');
        $commission_per_sale = GlobalSetting::where('key', 'commission_per_sale')->value('value');

        $total_commission = 0.00;
        $net_income = $total_income;
        if ($commission_type == 'commission') {
            $total_commission = ($commission_per_sale / 100) * $total_income;
            $net_income = $total_income - $total_commission;
        }

        // ── Withdrawal Balance ──────────────────────────────────────────
        $pending_success_list = SellerWithdraw::where('seller_id', $user->id)
            ->where('status', '!=', 'rejected')
            ->sum('total_amount');

        $total_withdraw_amount = $pending_success_list;
        $current_balance = $net_income - $total_withdraw_amount;

        $pending_withdraw = SellerWithdraw::where('seller_id', $user->id)
            ->where('status', 'pending')
            ->sum('total_amount');

        // ── Agency Bookings ─────────────────────────────────────────────
        $agency_bookings = Booking::with(['service', 'user'])
            ->whereIn('service_id', $myServicesIds)
            ->latest()
            ->get();

        return view('admin.seller.seller_show', [
            'user' => $user,
            'total_income' => $total_income,
            'total_commission' => $total_commission,
            'net_income' => $net_income,
            'current_balance' => $current_balance,
            'total_withdraw_amount' => $total_withdraw_amount,
            'pending_withdraw' => $pending_withdraw,
            'agency_bookings' => $agency_bookings,
        ]);
    }
}