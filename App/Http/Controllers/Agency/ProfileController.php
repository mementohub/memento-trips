<?php

namespace App\Http\Controllers\Agency;

use App\Http\Controllers\Controller;
use App\Http\Requests\BecomeAgencyRequest;
use App\Http\Requests\EditStudentProfileRequest;
use App\Http\Requests\PasswordChangeRequest;
use App\Models\AgencyClient;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Image;
use Modules\Coupon\App\Models\Coupon;
use Modules\Coupon\App\Models\CouponHistory;
use Modules\GlobalSetting\App\Models\GlobalSetting;
use Modules\NoticeBoard\App\Models\NoticeBoard;
use Modules\PaymentWithdraw\App\Models\SellerWithdraw;
use Modules\SupportTicket\App\Models\MessageDocument;
use Modules\SupportTicket\App\Models\SupportTicket;
use Modules\SupportTicket\App\Models\SupportTicketMessage;
use Modules\TourBooking\App\Models\Booking;
use Modules\TourBooking\App\Models\Service;
use Modules\Wishlist\App\Models\Wishlist;

class ProfileController extends Controller
{
    public function dashboard()
    {
        $user = Auth::guard('web')->user();

        // Agency CRM mode enabled only if DB has new columns
        $hasAgencyBookingFields = Schema::hasColumn('bookings', 'agency_user_id');

        if ($hasAgencyBookingFields) {
            // payment_status values considered "paid"
            $paidPaymentStatuses = ['success', 'completed'];

            // Latest bookings (agency-owned)
            $bookings = Booking::with(['service', 'user', 'agencyClient'])
                ->where('agency_user_id', $user->id)
                ->latest()
                ->take(10)
                ->get();

            // Core money metrics
            $total_income = (float) Booking::where('agency_user_id', $user->id)
                ->whereIn('payment_status', $paidPaymentStatuses)
                ->sum('total');

            $confirm_booking = (int) Booking::where('agency_user_id', $user->id)
                ->where('booking_status', 'confirmed')
                ->count();

            // Meaning becomes "distinct services booked by agency"
            $total_services = (int) Booking::where('agency_user_id', $user->id)
                ->distinct()
                ->count('service_id');

            // NEW KPIs (CRM) - safe even if table not migrated yet
            $hasAgencyClientsTable = Schema::hasTable('agency_clients') && Schema::hasColumn('agency_clients', 'agency_user_id');

            $total_clients = $hasAgencyClientsTable
                ? (int) AgencyClient::where('agency_user_id', $user->id)->count()
                : 0;

            $total_bookings = (int) Booking::where('agency_user_id', $user->id)->count();

            $pending_bookings = (int) Booking::where('agency_user_id', $user->id)
                ->where('booking_status', 'pending')
                ->count();

            $due_to_collect = (float) Booking::where('agency_user_id', $user->id)
                ->where('due_amount', '>', 0)
                ->whereNotIn('payment_status', $paidPaymentStatuses)
                ->sum('due_amount');

            // Commission logic (prefer snapshot per booking)
            $commission_type = (string) GlobalSetting::where('key', 'commission_type')->value('value');
            $commission_per_sale = (float) GlobalSetting::where('key', 'commission_per_sale')->value('value');

            $total_commission = 0.0;
            $net_income = $total_income;

            if (Schema::hasColumn('bookings', 'commission_amount')) {
                $total_commission = (float) Booking::where('agency_user_id', $user->id)
                    ->whereIn('payment_status', $paidPaymentStatuses)
                    ->sum('commission_amount');

                $net_income = (float) $total_income - (float) $total_commission;
            } else {
                if ('commission' === $commission_type && $commission_per_sale > 0) {
                    $total_commission = ($commission_per_sale / 100) * $total_income;
                    $net_income = $total_income - $total_commission;
                }
            }

            // Withdrawals (existing seller payout logic)
            $total_withdraw_amount = (float) SellerWithdraw::where('seller_id', $user->id)
                ->where('status', '!=', 'rejected')
                ->sum('total_amount');

            $current_balance = (float) $net_income - (float) $total_withdraw_amount;

            $pending_withdraw = (float) SellerWithdraw::where('seller_id', $user->id)
                ->where('status', 'pending')
                ->sum('total_amount');

            // Chart 1: daily paid revenue this month (from start of month to today)
            $labels = [];
            $series = [];
            $start = Carbon::now()->startOfMonth();
            $end = Carbon::now();

            $cursor = $start->copy();
            while ($cursor->lte($end)) {
                $labels[] = $cursor->format('d');

                $sum = (float) Booking::where('agency_user_id', $user->id)
                    ->whereDate('created_at', $cursor->toDateString())
                    ->whereIn('payment_status', $paidPaymentStatuses)
                    ->sum('total');

                $series[] = $sum;
                $cursor->addDay();
            }

            // keep old variable names used by blade
            $data = json_encode($series);
            $lable = json_encode($labels);

            // Chart 2: bookings by status (all time)
            $statusCountsRaw = Booking::where('agency_user_id', $user->id)
                ->selectRaw('booking_status, COUNT(*) as c')
                ->groupBy('booking_status')
                ->pluck('c', 'booking_status')
                ->toArray();

            $statusCounts = [
                'pending' => (int) ($statusCountsRaw['pending'] ?? 0),
                'confirmed' => (int) ($statusCountsRaw['confirmed'] ?? 0),
                'completed' => (int) ($statusCountsRaw['completed'] ?? 0),
                'cancelled' => (int) ($statusCountsRaw['cancelled'] ?? 0),
            ];

            // Top 5 clients by paid revenue (this month) - safe if table missing
            $top_clients = collect();

            if ($hasAgencyClientsTable) {
                $monthStart = Carbon::now()->startOfMonth();
                $monthEnd = Carbon::now()->endOfMonth();

                $top_clients = Booking::query()
                    ->where('agency_user_id', $user->id)
                    ->whereIn('payment_status', $paidPaymentStatuses)
                    ->whereNotNull('agency_client_id')
                    ->whereBetween('created_at', [$monthStart, $monthEnd])
                    ->selectRaw('agency_client_id, SUM(total) as revenue, COUNT(*) as bookings_count')
                    ->groupBy('agency_client_id')
                    ->orderByDesc('revenue')
                    ->with('agencyClient')
                    ->take(5)
                    ->get();
            }

            return view('agency.dashboard', [
                'lable' => $lable,
                'data' => $data,

                'total_income' => $total_income,
                'total_commission' => $total_commission,
                'net_income' => $net_income,
                'current_balance' => $current_balance,
                'total_withdraw_amount' => $total_withdraw_amount,
                'pending_withdraw' => $pending_withdraw,

                'confirm_booking' => $confirm_booking,
                'total_services' => $total_services,
                'bookings' => $bookings,

                'total_clients' => $total_clients,
                'total_bookings' => $total_bookings,
                'pending_bookings' => $pending_bookings,
                'due_to_collect' => $due_to_collect,
                'status_counts' => $statusCounts,
                'top_clients' => $top_clients,
            ]);
        }

        /**
         * FALLBACK (old seller logic) - safe before CRM migrations.
         */
        $servicesIds = Service::where('user_id', $user->id)->pluck('id')->toArray();

        $bookings = Booking::with(['service', 'user'])
            ->whereIn('service_id', $servicesIds)
            ->latest()
            ->take(10)
            ->get();

        $total_income = (float) Booking::whereIn('service_id', $servicesIds)
            ->where('payment_status', 'success')
            ->sum('total');

        $confirm_booking = (int) Booking::whereIn('service_id', $servicesIds)
            ->where('booking_status', 'confirmed')
            ->count();

        $total_services = (int) Service::where('user_id', $user->id)->count();

        $commission_type = (string) GlobalSetting::where('key', 'commission_type')->value('value');
        $commission_per_sale = (float) GlobalSetting::where('key', 'commission_per_sale')->value('value');

        $total_commission = 0.0;
        $net_income = $total_income;

        if ('commission' === $commission_type && $commission_per_sale > 0) {
            $total_commission = ($commission_per_sale / 100) * $total_income;
            $net_income = $total_income - $total_commission;
        }

        $total_withdraw_amount = (float) SellerWithdraw::where('seller_id', $user->id)
            ->where('status', '!=', 'rejected')
            ->sum('total_amount');

        $current_balance = (float) $net_income - (float) $total_withdraw_amount;

        $pending_withdraw = (float) SellerWithdraw::where('seller_id', $user->id)
            ->where('status', 'pending')
            ->sum('total_amount');

        // Chart (fallback): daily paid revenue this month
        $labels = [];
        $series = [];
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now();

        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $labels[] = $cursor->format('d');

            $sum = (float) Booking::whereDate('created_at', $cursor->toDateString())
                ->whereIn('service_id', $servicesIds)
                ->where('payment_status', 'success')
                ->sum('total');

            $series[] = $sum;
            $cursor->addDay();
        }

        $data = json_encode($series);
        $lable = json_encode($labels);

        return view('agency.dashboard', [
            'lable' => $lable,
            'data' => $data,
            'total_income' => $total_income,
            'total_commission' => $total_commission,
            'net_income' => $net_income,
            'current_balance' => $current_balance,
            'total_withdraw_amount' => $total_withdraw_amount,
            'pending_withdraw' => $pending_withdraw,
            'confirm_booking' => $confirm_booking,
            'total_services' => $total_services,
            'bookings' => $bookings,
        ]);
    }

    public function edit_profile()
    {
        $user = Auth::guard('web')->user();

        return view('agency.edit_profile', ['user' => $user]);
    }

    public function update_profile(EditStudentProfileRequest $request)
    {
        $user = Auth::guard('web')->user();

        $user->name = $request->name;
        $user->phone = $request->phone;
        $user->address = $request->address;
        $user->gender = $request->gender;
        $user->save();

        if ($request->file('image')) {
            $old_image = $user->image;

            $user_image = $request->image;
            $extention = $user_image->getClientOriginalExtension();

            $image_name = Str::slug($user->name).date('-Y-m-d-h-i-s-').rand(999, 9999).'.'.$extention;
            $image_name = 'uploads/custom-images/'.$image_name;

            Image::make($user_image)->save(public_path().'/'.$image_name);

            $user->image = $image_name;
            $user->save();

            if ($old_image && File::exists(public_path().'/'.$old_image)) {
                unlink(public_path().'/'.$old_image);
            }
        }

        $notify_message = trans('translate.Updated successfully');
        $notify_message = ['message' => $notify_message, 'alert-type' => 'success'];

        return redirect()->back()->with($notify_message);
    }

    public function change_password()
    {
        return view('agency.change_password');
    }

    public function update_password(PasswordChangeRequest $request)
    {
        $user = Auth::guard('web')->user();

        if (Hash::check($request->current_password, $user->password)) {
            $user->password = Hash::make($request->password);
            $user->save();

            $notify_message = trans('translate.Password changed successfully');
            $notify_message = ['message' => $notify_message, 'alert-type' => 'success'];

            return redirect()->back()->with($notify_message);
        }

        $notify_message = trans('translate.Current password does not match');
        $notify_message = ['message' => $notify_message, 'alert-type' => 'error'];

        return redirect()->back()->with($notify_message);
    }

    public function agency_profile(Request $request)
    {
        $user = Auth::guard('web')->user();
        $skills_expertises = json_decode($user->skills_expertise ?? '[]');

        return view('agency.agency_profile', [
            'user' => $user,
            'skills_expertises' => $skills_expertises,
        ]);
    }

    public function update_agency_profile(BecomeAgencyRequest $request)
    {
        $user = Auth::guard('web')->user();

        $user->agency_name = $request->agency_name;
        $user->agency_slug = $request->agency_slug;
        $user->website = $request->website;
        $user->location_map = $request->location_map;

        $user->about_me = $request->about_me;
        $user->country = $request->country;
        $user->state = $request->state;
        $user->city = $request->city;
        $user->address = $request->address;
        $user->facebook = $request->facebook;
        $user->linkedin = $request->linkedin;
        $user->twitter = $request->twitter;
        $user->instagram = $request->instagram;
        $user->save();

        if ($request->hasFile('agency_logo')) {
            $old_agency_logo = $user->agency_logo;

            if ($old_agency_logo && File::exists(public_path().'/'.$old_agency_logo)) {
                unlink(public_path().'/'.$old_agency_logo);
            }

            $file = $request->file('agency_logo');

            $imageName = 'uploads/custom-images/'.
                Str::slug($user->agency_name).'-'.
                now()->format('YmdHis').'-'.
                rand(1000, 9999).'.'.
                $file->getClientOriginalExtension();

            Image::make($file)->save(public_path($imageName));

            $user->agency_logo = $imageName;
            $user->save();
        }

        $notify_message = trans('translate.Updated successful');
        $notify_message = ['message' => $notify_message, 'alert-type' => 'success'];

        return redirect()->back()->with($notify_message);
    }

    public function account_delete()
    {
        return view('agency.account_delete');
    }

    public function confirm_account_delete(Request $request)
    {
        $user = Auth::guard('web')->user();

        $request->validate([
            'current_password' => 'required',
        ], [
            'current_password.required' => trans('translate.Current password is required'),
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            $notify_message = trans('translate.Current password does not match');
            $notify_message = ['message' => $notify_message, 'alert-type' => 'error'];

            return redirect()->back()->with($notify_message);
        }

        // Delete user image
        $user_image = $user->image;
        if ($user_image && File::exists(public_path().'/'.$user_image)) {
            unlink(public_path().'/'.$user_image);
        }

        $user_id = $user->id;

        Coupon::where('seller_id', $user_id)->delete();
        CouponHistory::where('seller_id', $user_id)->delete();
        CouponHistory::where('buyer_id', $user_id)->delete();

        NoticeBoard::where('user_id', $user_id)->delete();
        SellerWithdraw::where('seller_id', $user_id)->delete();
        Wishlist::where('user_id', $user_id)->delete();

        $support_tickets = SupportTicket::where('author_id', $user_id)->latest()->get();

        foreach ($support_tickets as $support_ticket) {
            $ticket_messages = SupportTicketMessage::with('documents')
                ->where('support_ticket_id', $support_ticket->id)
                ->get();

            foreach ($ticket_messages as $ticket_message) {
                $documents = MessageDocument::where('message_id', $ticket_message->id)
                    ->where('model_name', 'SupportTicketMessage')
                    ->get();

                foreach ($documents as $document) {
                    $exist_file_name = $document->file_name;

                    if ($exist_file_name) {
                        $path = public_path('uploads/custom-images').'/'.$exist_file_name;
                        if (File::exists($path)) {
                            unlink($path);
                        }
                    }

                    $document->delete();
                }

                $ticket_message->delete();
            }

            $support_ticket->delete();
        }

        $user->delete();
        Auth::guard('web')->logout();

        $notify_message = trans('translate.Your account deleted successful');
        $notify_message = ['message' => $notify_message, 'alert-type' => 'success'];

        return redirect()->route('user.login')->with($notify_message);
    }
}
