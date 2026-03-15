<?php

declare(strict_types=1);

namespace Modules\TourBooking\App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\TourBooking\App\Models\Booking;
use Modules\TourBooking\App\Models\Service;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

/**
 * BookingController
 *
 * Manages booking records — listing, status updates, detail views, and PDF invoice generation.
 *
 * @package Modules\TourBooking\App\Http\Controllers\User
 */
final class BookingController extends Controller
{


    public function index(Request $request): View
    {
        $query = Booking::with(['service:id,title,location'])
            ->where('user_id', auth()->user()->id)
            ->latest();

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('booking_code', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhereHas('service', function ($sq) use ($search) {
                      $sq->where('title', 'like', "%{$search}%")
                         ->orWhere('location', 'like', "%{$search}%");
                  });
            });
        }

        // Filters
        if ($status = $request->input('status')) {
            $query->where('booking_status', $status);
        }
        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }
        if ($location = $request->input('location')) {
            $query->whereHas('service', fn($q) => $q->where('location', $location));
        }

        $bookings = $query->paginate(20)->appends($request->query());

        // Filter options
        $locations = Service::whereNotNull('location')
            ->where('location', '!=', '')
            ->distinct()->pluck('location')->sort()->values();

        $statuses = ['pending', 'confirmed', 'cancelled', 'completed'];
        $showPaymentFilter = false;
        $paymentStatuses = [];

        return view('tourbooking::user.booking.index', compact(
            'bookings', 'locations', 'statuses', 'paymentStatuses', 'showPaymentFilter'
        ));
    }

    public function details(Request $request): View
    {
        $booking = Booking::with(['service.translation', 'user'])
            ->where('user_id', auth()->user()->id)
            ->findOrFail($request->id);

        $extra_services = \Modules\TourBooking\App\Models\ExtraCharge::whereIn('id', $booking?->extra_services ?? [])
            ->get();

        return view('tourbooking::user.booking.details', compact('booking', 'extra_services'));
    }

    /**
     * Cancel a booking.
     */
    public function cancelBooking(Request $request, $id): RedirectResponse
    {
        $booking = Booking::where('id', $id)
            ->where('user_id', Auth::user()->id)
            ->where('booking_status', '!=', 'cancelled')
            ->where('booking_status', '!=', 'completed')
            ->firstOrFail();

        $validated = $request->validate([
            'cancellation_reason' => 'required|string|max:500',
        ]);

        $booking->update([
            'booking_status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $validated['cancellation_reason'],
        ]);

        // Notification logic can be added here

        return redirect()->route('user.bookings.index')->with('success', 'Your booking has been cancelled.');
    }
}
