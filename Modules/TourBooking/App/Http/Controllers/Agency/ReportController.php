<?php

declare(strict_types=1);

namespace Modules\TourBooking\App\Http\Controllers\Agency;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Modules\TourBooking\App\Models\Booking;

/**
 * ReportController
 *
 * Generates booking reports with date range and status filtering for agency analytics.
 *
 * @package Modules\TourBooking\App\Http\Controllers\Agency
 */
final class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $userId = auth()->id();

        $from   = $request->input('from');
        $to     = $request->input('to');
        $status = $request->input('status');

        $bookingsTable = (new Booking())->getTable(); // usually "bookings"

        // date column
        $dateCol = Schema::hasColumn($bookingsTable, 'check_in_date') ? 'check_in_date' : 'created_at';
        $dateColQualified = $bookingsTable . '.' . $dateCol;

        // base query (IMPORTANT: fully-qualified columns)
        $base = Booking::query()
            ->from($bookingsTable)
            ->where($bookingsTable . '.agency_user_id', $userId);

        if (!empty($status)) {
            $base->where($bookingsTable . '.booking_status', $status);
        }
        if (!empty($from)) {
            $base->whereDate($dateColQualified, '>=', $from);
        }
        if (!empty($to)) {
            $base->whereDate($dateColQualified, '<=', $to);
        }

        // --- BY CLIENT ---
        $byClient = (clone $base)
            ->leftJoin('agency_clients as ac', function ($join) use ($bookingsTable) {
                $join->on('ac.id', '=', $bookingsTable . '.agency_client_id')
                    ->whereNull('ac.deleted_at');
            })
            ->selectRaw("
                {$bookingsTable}.agency_client_id,
                COALESCE(NULLIF(CONCAT_WS(' ', ac.first_name, ac.last_name), ''), ac.email, 'No client') as client_name,
                COUNT(*) as bookings_count,
                COALESCE(SUM({$bookingsTable}.total), 0) as total_value,
                COALESCE(SUM({$bookingsTable}.commission_amount), 0) as total_commission
            ")
            ->groupBy($bookingsTable . '.agency_client_id', 'client_name')
            ->orderByDesc('total_value')
            ->get();

        // --- BY SERVICE ---
        $byService = (clone $base)
            ->leftJoin('services as s', 's.id', '=', $bookingsTable . '.service_id')
            ->selectRaw("
                {$bookingsTable}.service_id,
                COALESCE(NULLIF(s.title, ''), CONCAT('Service #', {$bookingsTable}.service_id)) as service_name,
                COUNT(*) as bookings_count,
                COALESCE(SUM({$bookingsTable}.total), 0) as total_value,
                COALESCE(SUM({$bookingsTable}.commission_amount), 0) as total_commission
            ")
            ->groupBy($bookingsTable . '.service_id', 'service_name')
            ->orderByDesc('total_value')
            ->get();

        return view('tourbooking::agency.reports.index', compact('byClient', 'byService'));
    }
}