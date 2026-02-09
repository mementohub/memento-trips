<?php

namespace App\Http\Controllers\Admin;

// ── Framework Dependencies ──────────────────────────────────────────────────
use Carbon\Carbon;

// ── Application Dependencies ────────────────────────────────────────────────
use App\Http\Controllers\Controller;

// ── Module Models ───────────────────────────────────────────────────────────
use Modules\GlobalSetting\App\Models\GlobalSetting;
use Modules\TourBooking\App\Models\Booking;

/**
 * DashboardController
 *
 * Renders the admin dashboard with revenue analytics, booking statistics,
 * and a monthly income chart. Calculates commission splits based on
 * the global commission settings.
 *
 * @package App\Http\Controllers\Admin
 */
class DashboardController extends Controller
{
    /**
     * Display the admin dashboard with analytics data.
     *
     * Computes total income, commission, net income, and generates a
     * day-by-day income chart for the current month. Also fetches the
     * 10 most recent bookings for the activity feed.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function dashboard()
    {
        // ── Revenue Calculations ────────────────────────────────────────
        $booking = Booking::where('payment_status', 'success');
        $total_income = $booking->sum('total');
        $total_booking = Booking::where('payment_status', 'success')->count();

        $commission_type = GlobalSetting::where('key', 'commission_type')->value('value');
        $commission_per_sale = GlobalSetting::where('key', 'commission_per_sale')->value('value');

        $total_commission = 0.00;
        $net_income = $total_income ?? 0;
        if ($commission_type == 'commission') {
            $total_commission = ($commission_per_sale / 100) * ($total_income ?? 0);
            $net_income = ($total_income ?? 0) - $total_commission;
        }

        // ── Monthly Income Chart Data ───────────────────────────────────
        $lable = [];
        $data = [];
        $start = new Carbon('first day of this month');
        $last = new Carbon('last day of this month');
        $first_date = $start->format('Y-m-d');
        $today = date('Y-m-d');
        $length = date('d') - $start->format('d');

        for ($i = 1; $i <= $length + 1; $i++) {
            $date = ($i == 1) ? $first_date : $start->addDays(1)->format('Y-m-d');

            $sum = Booking::whereDate('created_at', $date)
                ->where('payment_status', 'success')
                ->sum('total');

            $data[] = $sum ?? 0;
            $lable[] = $i;
        }

        $data = json_encode($data);
        $lable = json_encode($lable);

        // ── Recent Bookings ─────────────────────────────────────────────
        $bookings = Booking::with(['service', 'user'])
            ->latest()
            ->take(10)
            ->get();

        return view('admin.dashboard', [
            'lable' => $lable,
            'data' => $data,
            'bookings' => $bookings ?? [],
            'total_income' => $total_income ?? 0,
            'total_commission' => $total_commission,
            'net_income' => $net_income,
            'total_sold' => $total_booking ?? 0,
        ]);
    }
}