<?php

declare(strict_types=1);

namespace Modules\TourBooking\App\Http\Controllers\Agency;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use Modules\TourBooking\App\Models\Availability;
use Modules\TourBooking\App\Models\Booking;
use Modules\TourBooking\App\Models\ExtraCharge;
use Modules\TourBooking\App\Models\PickupPoint;
use Modules\TourBooking\App\Models\Service;

/**
 * BookingController
 *
 * Manages booking records — listing, status updates, detail views, and PDF invoice generation.
 *
 * @package Modules\TourBooking\App\Http\Controllers\Agency
 */
final class BookingController extends Controller
{
    /**
     * List bookings (Agency user view).
     * - If you have agency_user_id column, it lists bookings for that agency user.
     * - Else fallback to old behavior: list bookings for services owned by current user.
     */
    public function index(): View
    {
        $userId = Auth::id();

        $query = Booking::query()->with(['service', 'user']);

        if (Schema::hasColumn((new Booking())->getTable(), 'agency_user_id')) {
            $query->with(['agencyClient'])
                ->where('agency_user_id', $userId);
        } else {
            $myServicesIds = Service::where('user_id', $userId)->pluck('id')->toArray();
            $query->whereIn('service_id', $myServicesIds);
        }

        $bookings = $query->latest()->paginate(20);

        return view('tourbooking::agency.bookings.index', compact('bookings'));
    }

    /**
     * Show create form.
     */
    public function create(): View
    {
        $services = Service::where('status', 1)
            ->with(['pickupPoints'])
            ->orderBy('title')
            ->get();

        // Optional: if you have AgencyClient model/table for agency CRM
        $clients = class_exists(\App\Models\AgencyClient::class)
            ? \App\Models\AgencyClient::where('agency_user_id', Auth::id())
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get()
            : collect();

        return view('tourbooking::agency.bookings.create', compact('services', 'clients'));
    }

    /**
     * Store booking.
     *
     * ✅ Supports:
     * - cash / bank transfer (manual) with optional "mark as paid"
     * - card payment flow by redirecting to FRONT checkout (so user can pay via gateways)
     *
     * IMPORTANT:
     * - For card payments, we DO NOT create a booking here (to avoid duplicates).
     *   We store "agency context" in session and redirect to front checkout view route.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'service_id'        => ['required', 'exists:services,id'],

            // agency client (for new agency flow). If you still use user_id, you can pass it too.
            'agency_client_id'  => ['nullable', 'exists:agency_clients,id'],
            'user_id'           => ['nullable', 'exists:users,id'],

            // date
            'check_in_date'     => ['nullable', 'date_format:Y-m-d'],
            'check_out_date'    => ['nullable', 'date_format:Y-m-d', 'after_or_equal:check_in_date'],

            // legacy pax
            'adults'            => ['nullable', 'integer', 'min:0'],
            'children'          => ['nullable', 'integer', 'min:0'],
            'infants'           => ['nullable', 'integer', 'min:0'],

            // wizard pax (JSON/array)
            'age_quantities'    => ['nullable'],

            // prices (legacy/manual)
            'adult_price'       => ['nullable', 'numeric', 'min:0'],
            'child_price'       => ['nullable', 'numeric', 'min:0'],
            'infant_price'      => ['nullable', 'numeric', 'min:0'],

            // extras legacy
            'extra_charges'     => ['nullable', 'numeric', 'min:0'],

            // wizard extras payload (array or JSON)
            'extras'            => ['nullable'],
            'extra_services'    => ['nullable'],

            // pickup
            'pickup_point_id'     => ['nullable', 'exists:pickup_points,id'],
            'pickup_extra_charge' => ['nullable', 'numeric', 'min:0'],

            // manual discounts/tax
            'discount_amount'   => ['nullable', 'numeric', 'min:0'],
            'tax_amount'        => ['nullable', 'numeric', 'min:0'],

            // payments
            'payment_method'    => ['nullable', 'string'],  // cash | bank_transfer | stripe | payu | etc
            'mark_as_paid'      => ['nullable', 'boolean'], // only for cash/bank
            'payment_status'    => ['nullable', 'string'],  // optional override (cash/bank)
        ]);

        $service = Service::findOrFail($validated['service_id']);

        // Normalize date
        $checkInDate = !empty($validated['check_in_date'])
            ? (string) $validated['check_in_date']
            : Carbon::today()->toDateString();

        // Normalize pax
        $ageQuantities = $this->normalizeAgeQuantities($request->input('age_quantities'), $validated);

        // Guard: keep at least 1 adult
        if (($ageQuantities['adult'] ?? 0) < 1) {
            return back()->withErrors(['adults' => 'Adult must be at least 1.'])->withInput();
        }

        $totalPax = (int) ($ageQuantities['adult'] + $ageQuantities['child'] + $ageQuantities['baby'] + $ageQuantities['infant']);

        // Payment method
        $paymentMethodRaw = (string) ($validated['payment_method'] ?? 'cash');
        $paymentMethod = $this->normalizePaymentMethod($paymentMethodRaw);

        $isManualMethod = in_array($paymentMethod, ['cash', 'bank_transfer'], true);
        $isCardOrGateway = !$isManualMethod;

        // ---------
        // CARD / GATEWAY FLOW
        // ---------
        if ($isCardOrGateway) {
            // Optional agency client context (if you have agency_clients)
            $client = null;
            if (!empty($validated['agency_client_id']) && class_exists(\App\Models\AgencyClient::class)) {
                $client = \App\Models\AgencyClient::where('agency_user_id', Auth::id())
                    ->where('id', $validated['agency_client_id'])
                    ->first();
            }

            Session::put('agency_booking_context', [
                'agency_user_id'    => Auth::id(),
                'agency_client_id'  => $client?->id,

                'customer_name'     => $client?->full_name ?? null,
                'customer_email'    => $client?->email ?? null,
                'customer_phone'    => $client?->phone ?? null,
                'customer_address'  => $client?->address ?? null,

                'preferred_gateway' => $paymentMethod,
            ]);

            return redirect()->route('front.tourbooking.book.checkout.view', [
                'service_id'          => $service->id,
                'check_in_date'       => $checkInDate,
                'intended_from'       => 'agency_wizard',

                // legacy
                'person'              => $ageQuantities['adult'],
                'children'            => $ageQuantities['child'],

                // new
                'age_quantities'      => $ageQuantities,

                // pickup
                'pickup_point_id'     => $validated['pickup_point_id'] ?? null,
                'pickup_extra_charge' => $validated['pickup_extra_charge'] ?? null,

                // optional amounts
                'discount_amount'     => $validated['discount_amount'] ?? 0,
                'tax_amount'          => $validated['tax_amount'] ?? 0,

                // extras (front may ignore if unsupported)
                'extras'              => $request->input('extras'),
                'extra_services'      => $request->input('extra_services'),
            ]);
        }

        // ---------
        // MANUAL FLOW (cash/bank) — create booking here
        // ---------

        // Availability validation (if availability exists for date)
        $availability = Availability::where('service_id', $service->id)
            ->whereDate('date', '=', $checkInDate)
            ->first();

        if ($availability) {
            $spots = $this->getSpots($availability);
            if ($spots < $totalPax) {
                return back()->withErrors([
                    'check_in_date' => "No availability for {$checkInDate}. Spots: {$spots}, required: {$totalPax}.",
                ])->withInput();
            }
        }

        $prices = $this->getEffectivePrices($service, $availability, $validated);

        $tickets = [
            'adult'  => round($ageQuantities['adult']  * $prices['adult'], 2),
            'child'  => round($ageQuantities['child']  * $prices['child'], 2),
            'baby'   => round($ageQuantities['baby']   * $prices['baby'], 2),
            'infant' => round($ageQuantities['infant'] * $prices['infant'], 2),
        ];
        $ticketsSubtotal = round(array_sum($tickets), 2);

        // Extras
        $extrasTotal = 0.0;
        $extrasBreakdown = [];

        $extrasPayload = $this->normalizeExtrasPayload($request->input('extras'), $request->input('extra_services'));
        if (!empty($extrasPayload)) {
            $dbExtras = ExtraCharge::query()
                ->where('service_id', $service->id)
                ->get()
                ->keyBy('id');

            foreach ($dbExtras as $id => $extra) {
                $cfg = $this->normalizeExtra($extra);
                $payloadRow = $extrasPayload[(int)$id] ?? null;

                $isActive = (bool) ($cfg['is_mandatory'] || ($payloadRow['active'] ?? false));
                if (!$isActive) continue;

                $lineAmount = 0.0;

                if ($cfg['is_tax']) {
                    $pct = (float) ($cfg['tax_percentage'] ?? 0);
                    $lineAmount = ($pct / 100.0) * $ticketsSubtotal;
                } elseif ($cfg['charge_type'] === 'per_booking') {
                    $lineAmount = (float) $cfg['price'];
                } elseif ($cfg['charge_type'] === 'per_person') {
                    $lineAmount = (float) $cfg['price'] * $totalPax;
                } else {
                    $quantities = $this->normalizeAgeQuantities($payloadRow['quantities'] ?? null, []);
                    $useAll = (bool) ($cfg['apply_to_all_persons'] || $cfg['is_mandatory']);

                    if ($useAll) {
                        $quantities = $ageQuantities;
                    } else {
                        $quantities['adult']  = min($quantities['adult'],  $ageQuantities['adult']);
                        $quantities['child']  = min($quantities['child'],  $ageQuantities['child']);
                        $quantities['baby']   = min($quantities['baby'],   $ageQuantities['baby']);
                        $quantities['infant'] = min($quantities['infant'], $ageQuantities['infant']);
                    }

                    $pp = $cfg['prices_per_age'];
                    $lineAmount =
                        ($quantities['adult']  * (float)($pp['adult']  ?? 0)) +
                        ($quantities['child']  * (float)($pp['child']  ?? 0)) +
                        ($quantities['baby']   * (float)($pp['baby']   ?? 0)) +
                        ($quantities['infant'] * (float)($pp['infant'] ?? 0));
                }

                $lineAmount = round($lineAmount, 2);
                $extrasTotal += $lineAmount;

                $extrasBreakdown[] = [
                    'id'          => (int) $id,
                    'name'        => $cfg['name'],
                    'charge_type' => $cfg['charge_type'],
                    'is_tax'      => $cfg['is_tax'],
                    'amount'      => $lineAmount,
                ];
            }

            $extrasTotal = round($extrasTotal, 2);
        } else {
            $extrasTotal = round((float) ($validated['extra_charges'] ?? 0), 2);
        }

        // Pickup
        $pickupPointId = $validated['pickup_point_id'] ?? null;
        $pickupCharge = 0.0;
        $pickupPointName = null;

        if (!empty($pickupPointId)) {
            $pickupPoint = PickupPoint::where('service_id', $service->id)
                ->where('id', $pickupPointId)
                ->first();

            if ($pickupPoint) {
                $pickupPointName = $pickupPoint->name ?? $pickupPoint->title ?? null;

                if ($request->filled('pickup_extra_charge')) {
                    $pickupCharge = max(0, round((float) $request->input('pickup_extra_charge'), 2));
                } else {
                    $base = (float) ($pickupPoint->extra_charge ?? $pickupPoint->charge ?? $pickupPoint->price ?? 0);
                    $chargeType = $pickupPoint->charge_type ?? 'per_booking';
                    $pickupCharge = $chargeType === 'per_person' ? $base * $totalPax : $base;
                    $pickupCharge = round($pickupCharge, 2);
                }
            }
        }

        $discountAmount = round((float) ($validated['discount_amount'] ?? 0), 2);
        $taxAmount      = round((float) ($validated['tax_amount'] ?? 0), 2);

        $subtotal = round($ticketsSubtotal + $extrasTotal + $pickupCharge - $discountAmount, 2);
        $total    = round($subtotal + $taxAmount, 2);

        // Payment status for manual methods
        $wantsPaid = (bool) ($validated['mark_as_paid'] ?? false);
        $paymentStatusRaw = strtolower((string) ($validated['payment_status'] ?? ''));
        if (in_array($paymentStatusRaw, ['paid', 'completed', 'success'], true)) {
            $wantsPaid = true;
        }

        $paymentStatus = $wantsPaid ? 'completed' : 'pending';
        $paidAmount = $wantsPaid ? $total : 0.0;
        $dueAmount  = $wantsPaid ? 0.0 : $total;

        $bookingStatus = $wantsPaid ? 'confirmed' : 'pending';
        $isPerPerson = (int) ($service->is_per_person ?? 0) === 1;

        $booking = Booking::create([
            'booking_code'      => Booking::generateBookingCode(),
            'service_id'        => $service->id,

            // Optional fields if schema supports them
            'agency_user_id'    => Schema::hasColumn((new Booking())->getTable(), 'agency_user_id') ? Auth::id() : null,
            'agency_client_id'  => Schema::hasColumn((new Booking())->getTable(), 'agency_client_id') ? ($validated['agency_client_id'] ?? null) : null,

            // If you still use user_id on agency bookings, keep it
            'user_id'           => $validated['user_id'] ?? null,
        ]);

        // Safe-fill optional date columns
        if (Schema::hasColumn($booking->getTable(), 'check_in_date')) $booking->check_in_date = $checkInDate;
        if (Schema::hasColumn($booking->getTable(), 'check_out_date')) $booking->check_out_date = $validated['check_out_date'] ?? null;

        $booking->is_per_person   = $isPerPerson ? 1 : 0;
        $booking->adults          = (int) $ageQuantities['adult'];
        $booking->children        = (int) $ageQuantities['child'];
        $booking->infants         = (int) $ageQuantities['infant'];

        $booking->adult_price     = (float) $prices['adult'];
        $booking->child_price     = (float) $prices['child'];
        $booking->infant_price    = (float) $prices['infant'];
        $booking->service_price   = (float) $ticketsSubtotal;

        $booking->extra_charges   = (float) round($extrasTotal + $pickupCharge, 2);
        $booking->discount_amount = (float) $discountAmount;
        $booking->tax_amount      = (float) $taxAmount;

        $booking->subtotal        = (float) $subtotal;
        $booking->total           = (float) $total;
        $booking->paid_amount     = (float) $paidAmount;
        $booking->due_amount      = (float) $dueAmount;

        $booking->payment_method  = $paymentMethod;
        $booking->payment_status  = $paymentStatus;
        $booking->booking_status  = $bookingStatus;

        $booking->pickup_point_id   = $pickupPointId;
        $booking->pickup_point_name = $pickupPointName;
        $booking->pickup_charge     = (float) $pickupCharge;

        // JSON payloads (only if your Booking model has these casts/columns)
        if (Schema::hasColumn($booking->getTable(), 'age_quantities')) $booking->age_quantities = $ageQuantities;
        if (Schema::hasColumn($booking->getTable(), 'age_config')) $booking->age_config = $prices;
        if (Schema::hasColumn($booking->getTable(), 'age_breakdown')) {
            $booking->age_breakdown = [
                'tickets'           => $tickets,
                'tickets_subtotal'  => $ticketsSubtotal,
                'extras_total'      => $extrasTotal,
                'extras_breakdown'  => $extrasBreakdown,
                'pickup_charge'     => $pickupCharge,
                'discount_amount'   => $discountAmount,
                'tax_amount'        => $taxAmount,
                'subtotal'          => $subtotal,
                'total'             => $total,
            ];
        }

        $booking->save();

        return redirect()
            ->route('agency.tourbooking.bookings.show', $booking)
            ->with('success', __('Booking created successfully!'));
    }

    /**
     * Display a booking.
     */
    public function show(Booking $booking): View
    {
        $booking->load(['service', 'user']);

        // Authorization: either agency_user owns it, or service owner owns it (legacy)
        if (Schema::hasColumn($booking->getTable(), 'agency_user_id') && !empty($booking->agency_user_id)) {
            if ((int)$booking->agency_user_id !== (int)auth()->id()) {
                abort(403, 'Unauthorized access to booking.');
            }
        } else {
            if (!empty($booking->service) && (int)$booking->service->user_id !== (int)auth()->id()) {
                abort(403, 'Unauthorized access to booking.');
            }
        }

        // Extra services view (legacy field: extra_services array of IDs)
        $extra_services = ExtraCharge::whereIn('id', $booking?->extra_services ?? [])
            ->where('status', true)
            ->get();

        return view('tourbooking::agency.bookings.details', compact('booking', 'extra_services'));
    }

    /**
     * Edit booking.
     */
    public function edit(Booking $booking): View
    {
        $booking->load(['service', 'user']);
        $services = Service::where('status', 1)->get();

        return view('tourbooking::agency.bookings.edit', compact('booking', 'services'));
    }

    /**
     * Update booking (legacy-style update form).
     * NOTE: if you want to forbid editing paid/gateway bookings, add guards here.
     */
    public function update(Request $request, Booking $booking): RedirectResponse
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'check_in_date' => 'required|date',
            'check_out_date' => 'nullable|date|after_or_equal:check_in_date',
            'check_in_time' => 'nullable',
            'check_out_time' => 'nullable',
            'adults' => 'required|integer|min:1',
            'children' => 'nullable|integer|min:0',
            'infants' => 'nullable|integer|min:0',
            'service_price' => 'required|numeric|min:0',
            'child_price' => 'nullable|numeric|min:0',
            'infant_price' => 'nullable|numeric|min:0',
            'extra_charges' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'subtotal' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string',
            'payment_status' => 'required|in:pending,completed',
            'booking_status' => 'required|in:pending,confirmed,cancelled,completed',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_address' => 'nullable|string',
            'customer_notes' => 'nullable|string',
            'admin_notes' => 'nullable|string',
        ]);

        $validated['due_amount'] = $validated['total'] - ($validated['paid_amount'] ?? 0);

        // timestamps on status change
        if ($booking->booking_status !== $validated['booking_status']) {
            switch ($validated['booking_status']) {
                case 'confirmed':
                    $validated['confirmed_at'] = now();
                    break;
                case 'cancelled':
                    $validated['cancelled_at'] = now();
                    break;
                case 'completed':
                    $validated['completed_at'] = now();
                    break;
            }
        }

        $booking->update($validated);

        return redirect()->route('agency.tourbooking.bookings.show', $booking)
            ->with('success', 'Booking updated successfully.');
    }

    /**
     * Delete booking.
     */
    public function destroy(Booking $booking): RedirectResponse
    {
        $booking->delete();

        $notify_message = trans('translate.Booking deleted successfully');
        $notify_message = ['message' => $notify_message, 'alert-type' => 'success'];

        return redirect()->route('agency.tourbooking.bookings.index')->with($notify_message);
    }

    /**
     * Filter bookings by status.
     */
    public function getByStatus(string $status): View
    {
        $bookings = Booking::with(['service', 'user'])
            ->where('booking_status', $status)
            ->latest()
            ->paginate(15);

        return view('tourbooking::agency.bookings.index', compact('bookings'))
            ->with('statusFilter', $status);
    }

    public function pending(): View    { return $this->getByStatus('pending'); }
    public function confirmed(): View  { return $this->getByStatus('confirmed'); }
    public function completed(): View  { return $this->getByStatus('completed'); }
    public function cancelled(): View  { return $this->getByStatus('cancelled'); }

    /**
     * Update booking status (quick action).
     */
    public function updateStatus(Request $request, Booking $booking): RedirectResponse
    {
        $validated = $request->validate([
            'booking_status' => 'required|in:pending,confirmed,cancelled,completed',
            'admin_notes' => 'nullable|string',
        ]);

        if ($booking->booking_status !== $validated['booking_status']) {
            switch ($validated['booking_status']) {
                case 'confirmed':
                    $validated['confirmed_at'] = now();
                    break;
                case 'cancelled':
                    $validated['cancelled_at'] = now();
                    break;
                case 'completed':
                    $validated['completed_at'] = now();
                    break;
            }
        }

        $booking->update($validated);

        return back()->with('success', 'Booking status updated successfully.');
    }

    

    /**
     * Invoice view.
     */
    public function invoice(Booking $booking): View
    {
        $booking->load(['service', 'user', 'service.serviceType']);

        return view('tourbooking::agency.bookings.invoice', compact('booking'));
    }

    /**
     * Download invoice PDF.
     */
    public function downloadInvoicePdf(Booking $booking)
    {
        $booking->load(['service', 'user', 'service.serviceType']);

        $pdf = Pdf::loadView('tourbooking::agency.bookings.invoice', compact('booking'))
            ->setPaper('a4')
            ->setOption('margin-top', 10)
            ->setOption('margin-right', 10)
            ->setOption('margin-bottom', 10)
            ->setOption('margin-left', 10);

        $filename = 'invoice-' . $booking->booking_code . '.pdf';

        return $pdf->download($filename);
    }

    public function bookingConfirm(Request $request): RedirectResponse
    {
        $bookingId = $request->input('id');
        $booking = Booking::findOrFail($bookingId);

        $booking->update([
            'booking_status' => 'confirmed',
            'confirmed_at' => now(),
            'admin_notes' => $request->input('confirmation_message') ?? null,
        ]);

        $notify_message = trans('translate.Booking Confirmed Successfully');
        $notify_message = ['message' => $notify_message, 'alert-type' => 'success'];

        return redirect()->back()->with($notify_message);
    }

    public function bookingCancel(Request $request): RedirectResponse
    {
        $bookingId = $request->input('id');
        $booking = Booking::findOrFail($bookingId);

        $booking->update([
            'booking_status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $request->input('cancellation_reason') ?? null,
        ]);

        $notify_message = trans('translate.Booking Cancelled Successfully');
        $notify_message = ['message' => $notify_message, 'alert-type' => 'success'];

        return redirect()->back()->with($notify_message);
    }

    /* ============================================================
       Helpers (din codul nou)
    ============================================================ */

    private function normalizePaymentMethod(string $raw): string
    {
        $v = strtolower(trim($raw));
        if (in_array($v, ['bank', 'bank-transfer', 'banktransfer'], true)) return 'bank_transfer';
        if ($v === '') return 'cash';
        return $v;
    }

    private function normalizeAgeQuantities($ageQuantitiesRaw, array $validated): array
    {
        if (is_array($ageQuantitiesRaw)) {
            $a = $ageQuantitiesRaw;
        } elseif (is_string($ageQuantitiesRaw) && $ageQuantitiesRaw !== '') {
            $decoded = json_decode($ageQuantitiesRaw, true);
            $a = is_array($decoded) ? $decoded : [];
        } else {
            $a = [];
        }

        $adult  = (int) ($a['adult']  ?? $a['adults']  ?? ($validated['adults']  ?? 0));
        $child  = (int) ($a['child']  ?? $a['children']?? ($validated['children']?? 0));
        $baby   = (int) ($a['baby']   ?? 0);
        $infant = (int) ($a['infant'] ?? $a['infants'] ?? ($validated['infants'] ?? 0));

        return [
            'adult'  => max(0, $adult),
            'child'  => max(0, $child),
            'baby'   => max(0, $baby),
            'infant' => max(0, $infant),
        ];
    }

    private function getSpots($availability): int
    {
        if (!$availability) return 0;
        foreach (['available_spots', 'spots', 'available_seats', 'slots', 'slot'] as $field) {
            if (isset($availability->{$field}) && is_numeric($availability->{$field})) {
                return (int) $availability->{$field};
            }
        }
        return 0;
    }

    private function getEffectivePrices(Service $service, ?Availability $availability, array $validated): array
    {
        $adult  = array_key_exists('adult_price', $validated) ? (float) ($validated['adult_price'] ?? 0) : null;
        $child  = array_key_exists('child_price', $validated) ? (float) ($validated['child_price'] ?? 0) : null;
        $infant = array_key_exists('infant_price', $validated) ? (float) ($validated['infant_price'] ?? 0) : null;

        if ($adult !== null) {
            $fallback = $this->getBasePricesForService($service);
            return [
                'adult'  => round(max(0, $adult), 2),
                'child'  => round(max(0, $child ?? $fallback['child']), 2),
                'baby'   => round(max(0, $fallback['baby']), 2),
                'infant' => round(max(0, $infant ?? $fallback['infant']), 2),
            ];
        }

        $base = $this->getBasePricesForService($service);
        if ($availability) return $this->getPricesFromAvailabilityOrFallback($availability, $base);
        return $base;
    }

    private function getBasePricesForService(Service $service): array
    {
        $adult  = (float) ($service->price_per_person ?? $service->adult_price ?? $service->price ?? 0);
        $child  = (float) ($service->child_price ?? $service->per_children_price ?? 0);
        $baby   = (float) ($service->baby_price ?? 0);
        $infant = (float) ($service->infant_price ?? 0);

        if (!empty($service->age_categories)) {
            $decoded = is_array($service->age_categories)
                ? $service->age_categories
                : json_decode((string) $service->age_categories, true);

            if (is_array($decoded)) {
                $adult  = (float) ($decoded['adult']['price']  ?? $adult);
                $child  = (float) ($decoded['child']['price']  ?? $child);
                $baby   = (float) ($decoded['baby']['price']   ?? $baby);
                $infant = (float) ($decoded['infant']['price'] ?? $infant);
            }
        }

        return [
            'adult'  => round($adult, 2),
            'child'  => round($child, 2),
            'baby'   => round($baby, 2),
            'infant' => round($infant, 2),
        ];
    }

    private function getPricesFromAvailabilityOrFallback(Availability $availability, array $fallback): array
    {
        if (!empty($availability->age_categories)) {
            $decoded = is_array($availability->age_categories)
                ? $availability->age_categories
                : json_decode((string) $availability->age_categories, true);

            if (is_array($decoded)) {
                return [
                    'adult'  => round((float) ($decoded['adult']['price']  ?? $fallback['adult']), 2),
                    'child'  => round((float) ($decoded['child']['price']  ?? $fallback['child']), 2),
                    'baby'   => round((float) ($decoded['baby']['price']   ?? $fallback['baby']), 2),
                    'infant' => round((float) ($decoded['infant']['price'] ?? $fallback['infant']), 2),
                ];
            }
        }

        if (!empty($availability->prices)) {
            $decoded = is_array($availability->prices)
                ? $availability->prices
                : json_decode((string) $availability->prices, true);

            if (is_array($decoded)) {
                return [
                    'adult'  => round((float) ($decoded['adult']  ?? $fallback['adult']), 2),
                    'child'  => round((float) ($decoded['child']  ?? $fallback['child']), 2),
                    'baby'   => round((float) ($decoded['baby']   ?? $fallback['baby']), 2),
                    'infant' => round((float) ($decoded['infant'] ?? $fallback['infant']), 2),
                ];
            }
        }

        $adult = (float) ($availability->special_price ?? $fallback['adult']);
        $child = (float) ($availability->per_children_price ?? $fallback['child']);

        return [
            'adult'  => round($adult, 2),
            'child'  => round($child, 2),
            'baby'   => round($fallback['baby'], 2),
            'infant' => round($fallback['infant'], 2),
        ];
    }

    private function normalizeExtrasPayload($extrasRaw, $extraServicesRaw): array
    {
        $payload = [];

        if (is_array($extrasRaw)) {
            foreach ($extrasRaw as $row) {
                if (!is_array($row)) continue;
                $id = isset($row['id']) ? (int)$row['id'] : 0;
                if ($id > 0) $payload[$id] = $row;
            }
            if (!empty($payload)) return $payload;
        } elseif (is_string($extrasRaw) && $extrasRaw !== '') {
            $decoded = json_decode($extrasRaw, true);
            if (is_array($decoded)) {
                foreach ($decoded as $row) {
                    if (!is_array($row)) continue;
                    $id = isset($row['id']) ? (int)$row['id'] : 0;
                    if ($id > 0) $payload[$id] = $row;
                }
                if (!empty($payload)) return $payload;
            }
        }

        if (is_array($extraServicesRaw)) {
            foreach ($extraServicesRaw as $row) {
                if (!is_array($row)) continue;
                $id = isset($row['id']) ? (int)$row['id'] : 0;
                if ($id > 0) {
                    $row['active'] = $row['active'] ?? true;
                    $payload[$id] = $row;
                }
            }
        } elseif (is_string($extraServicesRaw) && $extraServicesRaw !== '') {
            $decoded = json_decode($extraServicesRaw, true);
            if (is_array($decoded)) {
                foreach ($decoded as $row) {
                    if (!is_array($row)) continue;
                    $id = isset($row['id']) ? (int)$row['id'] : 0;
                    if ($id > 0) {
                        $row['active'] = $row['active'] ?? true;
                        $payload[$id] = $row;
                    }
                }
            }
        }

        return $payload;
    }

    private function normalizeExtra($extra): array
    {
        $priceType = $extra->price_type ?? null;
        $isTax = (bool) ($extra->is_tax ?? false);

        $pricesPerAge = [
            'adult'  => (float) ($extra->adult_price ?? 0),
            'child'  => (float) ($extra->child_price ?? 0),
            'baby'   => (float) ($extra->baby_price ?? 0),
            'infant' => (float) ($extra->infant_price ?? 0),
        ];

        $hasAgeCats = false;
        if (!empty($extra->age_categories)) {
            $decoded = is_array($extra->age_categories)
                ? $extra->age_categories
                : json_decode((string) $extra->age_categories, true);

            $hasAgeCats = is_array($decoded) && !empty($decoded);

            if (is_array($decoded)) {
                $pricesPerAge['adult']  = (float) ($decoded['adult']['price']  ?? $pricesPerAge['adult']);
                $pricesPerAge['child']  = (float) ($decoded['child']['price']  ?? $pricesPerAge['child']);
                $pricesPerAge['baby']   = (float) ($decoded['baby']['price']   ?? $pricesPerAge['baby']);
                $pricesPerAge['infant'] = (float) ($decoded['infant']['price'] ?? $pricesPerAge['infant']);
            }
        }

        $hasPerAgePrices = ($pricesPerAge['adult'] + $pricesPerAge['child'] + $pricesPerAge['baby'] + $pricesPerAge['infant']) > 0;

        if ($isTax) {
            $chargeType = 'tax';
        } elseif ($hasAgeCats || ($priceType === 'per_person' && $hasPerAgePrices)) {
            $chargeType = 'per_age';
        } elseif ($priceType === 'per_person') {
            $chargeType = 'per_person';
        } else {
            $chargeType = 'per_booking';
        }

        $price = (float) ($extra->price ?? $extra->amount ?? 0);

        return [
            'id' => (int) $extra->id,
            'name' => (string) ($extra->name ?? $extra->title ?? ('Extra #' . $extra->id)),
            'charge_type' => $chargeType,
            'price_type' => $priceType,
            'is_mandatory' => (bool) ($extra->is_mandatory ?? false),
            'apply_to_all_persons' => (bool) ($extra->apply_to_all_persons ?? false),
            'is_tax' => $isTax,
            'tax_percentage' => (float) ($extra->tax_percentage ?? 0),
            'price' => round($price, 2),
            'prices_per_age' => [
                'adult'  => round($pricesPerAge['adult'], 2),
                'child'  => round($pricesPerAge['child'], 2),
                'baby'   => round($pricesPerAge['baby'], 2),
                'infant' => round($pricesPerAge['infant'], 2),
            ],
        ];
    }
}