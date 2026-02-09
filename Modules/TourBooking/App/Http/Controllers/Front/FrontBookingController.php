<?php

declare(strict_types=1);

namespace Modules\TourBooking\App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

use Modules\TourBooking\App\Models\Availability;
use Modules\TourBooking\App\Models\Booking;
use Modules\TourBooking\App\Models\Coupon;
use Modules\TourBooking\App\Models\ExtraCharge;
use Modules\TourBooking\App\Models\PickupPoint;
use Modules\TourBooking\App\Models\Review;
use Modules\TourBooking\App\Models\Service;

use Barryvdh\DomPDF\Facade\Pdf;
use Modules\Currency\App\Models\Currency;
use Modules\PaymentGateway\App\Models\PaymentGateway;

/**
 * FrontBookingController
 *
 * Handles the front-end booking flow including checkout display, booking processing, availability checking, coupon validation, invoice generation, cancellation, and review submission.
 *
 * @package Modules\TourBooking\App\Http\Controllers\Front
 */
final class FrontBookingController extends Controller
{
    /**
     * Display the booking checkout for a service.
     */
    public function bookingCheckoutView(Request $request)
    {
        // ---- Payment settings ------------------------------------------------
        $payment_data = PaymentGateway::all();
        $payment_setting = [];
        foreach ($payment_data as $item) {
            $payment_setting[$item->key] = $item->value;
        }
        $payment_setting = (object) ($payment_setting ?? []);

        $razorpay_currency    = Currency::findOrFail($payment_setting->razorpay_currency_id);
        $flutterwave_currency = Currency::findOrFail($payment_setting->flutterwave_currency_id);
        $paystack_currency    = Currency::findOrFail($payment_setting->paystack_currency_id);

        $auth_user = Auth::guard('web')->user();
        
        $isAgencyUser = false;

// ---- agency flags + clients (direct DB, no wizard) ------------------------
$isAgencyUser = false;
$agencyClients = collect();

if ($auth_user) {
    $isAgencyEnabled = (int)($auth_user->is_seller ?? 0) === 1;
    $isApproved = (string)($auth_user->instructor_joining_request ?? '') === 'approved';
    $isAgencyUser = $isAgencyEnabled && $isApproved;

    if ($isAgencyUser) {
        $agencyClients = \DB::table('agency_clients')
            ->where('agency_user_id', $auth_user->id)
            ->whereNull('deleted_at')
            ->select([
                'id',
                'first_name',
                'last_name',
                'email',
                'phone',
                'country',
                'state',
                'city',
                'address',
                'notes',
            ])
            ->orderByDesc('id')
            ->limit(500)
            ->get();
    }
}

        /** @var Service $service */
        $service = Service::query()
            ->where('id', $request->service_id)
            ->where('status', true)
            ->with('availabilities')
            ->firstOrFail();

        // Store intended URL if coming from booking process
        if ($request->has('intended_from') && $request->intended_from === 'booking') {
            $intendedUrl = url()->current() . '?' . http_build_query($request->except(['intended_from']));
            session(['url.intended' => $intendedUrl]);
        }

        // ---- date required ---------------------------------------------------
        $date = $request->input('check_in_date');
        if (!$date) {
            return back()->with(['message' => __('translate.Please select a date'), 'alert-type' => 'error']);
        }

        // ---- availability: by id or by date ---------------------------------
        $availability = $this->resolveAvailability($service, $request, $date);

        // ---- quantities: age_quantities or legacy person/children -------------
        $qty = $this->resolveQuantities($request);

        // ---- spot check ------------------------------------------------------
        if ($availability && $availability->available_spots !== null) {
            $totalGuests = array_sum($qty);
            if ($totalGuests > (int) $availability->available_spots) {
                return back()->with([
                    'message' => trans('translate.Not enough available spots for the selected date'),
                    'alert-type' => 'error',
                ]);
            }
        }

        // ---- unit prices (adult/child/baby/infant) for selected date ----------
        $unit = $this->getUnitPricesForDate($service, $date, $availability);

        // ---- extras: parse selected IDs from request reminding (keys are IDs) -
        [$extraIds, $extraPayloadById] = $this->parseExtrasFromRequest($request);

        $extraCharges = $this->fetchExtraChargesForService($service, $extraIds);

        // ---- pickup ----------------------------------------------------------
        [$pickupCharge, $pickupPointName] = $this->computePickup($service, $request, $qty);

        // ---- build lines & total ---------------------------------------------
        $isPerPerson = (bool)($service->is_per_person ?? $service->price_per_person ?? false);

        $lines = [];
        $subtotal = 0.0;

        // Base service lines
        if ($isPerPerson) {
            foreach (['adult','child','baby','infant'] as $k) {
                $count = (int)($qty[$k] ?? 0);
                if ($count <= 0) continue;

                $price = (float)($unit[$k] ?? 0);
                $line  = $count * $price;
                $subtotal += $line;

                $lines[] = [
                    'label'    => ucfirst($k),
                    'key'      => $k,
                    'qty'      => $count,
                    'unit'     => $price,
                    'subtotal' => $line,
                    'is_extra' => false,
                ];
            }
        } else {
            $fixed = (float)($service->discount_price ?? $service->discounted_price ?? $service->full_price ?? 0);
            $lines[] = [
                'label'    => $service->translation->title ?? 'Service',
                'key'      => 'service',
                'qty'      => 1,
                'unit'     => $fixed,
                'subtotal' => $fixed,
                'is_extra' => false,
            ];
            $subtotal += $fixed;
        }

        // Extras lines (computed like in UI)
        $extrasTotal = 0.0;
        $extrasBreakdown = [];

        foreach ($extraCharges as $e) {
            $payload = $extraPayloadById[$e->id] ?? [];

            $extraCalc = $this->computeExtraLine($e, $qty, $payload);
            if ($extraCalc['subtotal'] <= 0) continue;

            $extrasTotal += (float)$extraCalc['subtotal'];

            $lines[] = [
                'label'    => $extraCalc['label'],
                'key'      => 'extra_' . $e->id,
                'qty'      => 1,
                'unit'     => (float)$extraCalc['subtotal'],
                'subtotal' => (float)$extraCalc['subtotal'],
                'is_extra' => true,
            ];

            $extrasBreakdown[] = [
                'id'        => (int)$e->id,
                'name'      => (string)$extraCalc['label'],
                'type'      => (string)$extraCalc['charge_type'],
                'subtotal'  => (float)$extraCalc['subtotal'],
                'details'   => $extraCalc['details'],
            ];
        }

        // Pickup line
        if ($pickupCharge > 0 && $pickupPointName) {
            $lines[] = [
                'label'    => 'Pickup: ' . $pickupPointName,
                'key'      => 'pickup_charge',
                'qty'      => 1,
                'unit'     => (float)$pickupCharge,
                'subtotal' => (float)$pickupCharge,
                'is_extra' => true,
            ];
        }

        $total = $subtotal + $extrasTotal + (float)$pickupCharge;

        // Compat vechi (pentru view-uri vechi care folosesc person/child)
        $personCount = (int)($qty['adult'] ?? 0);
        $childCount  = (int)($qty['child'] ?? 0);
        $personPrice = $personCount * (float)($unit['adult'] ?? 0);
        $childPrice  = $childCount  * (float)($unit['child'] ?? 0);

        $data = [
            'service'       => $service,
            'extras'        => $extraCharges,
            'lines'         => $lines,
            'subtotal'      => $subtotal,
            'extras_total'  => $extrasTotal,
            'pickup_charge' => $pickupCharge,
            'total'         => $total,

            'personCount'   => $personCount,
            'childCount'    => $childCount,
            'personPrice'   => $personPrice,
            'childPrice'    => $childPrice,

            'ageQuantities' => $qty,
        ];

        // Build age config & breakdown for storage
        $ageConfig = [];
        $ageBreakdown = [];
        foreach ($lines as $line) {
            if (!($line['is_extra'] ?? false) && isset($line['key'])) {
                $key = (string)$line['key'];
                $ageConfig[$key] = [
                    'label' => $line['label'],
                    'price' => $line['unit'],
                ];
                $ageBreakdown[$key] = [
                    'label' => $line['label'],
                    'qty'   => $line['qty'],
                    'price' => $line['unit'],
                    'line'  => $line['subtotal'],
                ];
            }
        }

        // ---- session cart (source of truth for payments) ---------------------
        session()->forget('payment_cart');
        session()->put('payment_cart', [
            'service_id'        => $service->id,
            'check_in_date'     => $date,
            'availability_id'   => $availability?->id,

            'age_quantities'    => $qty,
            'age_config'        => $ageConfig,
            'age_breakdown'     => $ageBreakdown,

            'subtotal'          => $subtotal,
            'extras_total'      => $extrasTotal,
            'extras_breakdown'  => $extrasBreakdown,
            'extra_ids'         => $extraCharges->pluck('id')->values()->all(),

            'pickup_point_id'   => $request->pickup_point_id,
            'pickup_charge'     => $pickupCharge,
            'pickup_point_name' => $pickupPointName,

            'total'             => $total,
        ]);

        return view('tourbooking::front.bookings.checkout-view', [
            'service'              => $service,
            'data'                 => $data,
            'payment_setting'      => $payment_setting,
            'razorpay_currency'    => $razorpay_currency,
            'flutterwave_currency' => $flutterwave_currency,
            'paystack_currency'    => $paystack_currency,
            'user'                 => $auth_user,
            'availability'         => $availability,
            
            'isAgencyUser'  => $isAgencyUser,
            'agencyClients' => $agencyClients,
        ]);
    }

    /**
     * Process a new booking.
     * IMPORTANT: dacă fluxul tău real folosește payment_cart, atunci metoda asta trebuie să consume din sesiune.
     * O las funcțională, dar fără să intre în conflict cu pricing-ul nou.
     */
    public function processBooking(Request $request, string $slug): RedirectResponse
    {
        $service = Service::where('slug', $slug)
            ->where('status', true)
            ->firstOrFail();

        $validated = $request->validate([
            'check_in_date'      => 'required|date|after_or_equal:today',
            'check_out_date'     => 'nullable|date|after_or_equal:check_in_date',
            'adults'             => 'required|integer|min:1',
            'children'           => 'nullable|integer|min:0',
            'infants'            => 'nullable|integer|min:0',
            'extra_services'     => 'nullable|array',
            'coupon_code'        => 'nullable|string',
            'customer_name'      => 'required|string|max:255',
            'customer_email'     => 'required|email|max:255',
            'customer_phone'     => 'required|string|max:20',
            'customer_address'   => 'nullable|string',
            'customer_notes'     => 'nullable|string',
            'payment_method'     => 'required|string|in:paypal,stripe,bank_transfer',
        ]);

        $this->verifyServiceAvailability($service, $validated['check_in_date'], $validated['check_out_date'] ?? null);

        $priceDetails = $this->calculateBookingPrice(
            $service,
            (int) $validated['adults'],
            (int) ($validated['children'] ?? 0),
            (int) ($validated['infants'] ?? 0),
            $validated['extra_services'] ?? [],
            $validated['coupon_code'] ?? null
        );

        $bookingData = [
            'service_id'       => $service->id,
            'booking_code'     => Booking::generateBookingCode(),
            'check_in_date'    => $validated['check_in_date'],
            'check_out_date'   => $validated['check_out_date'],
            'adults'           => $validated['adults'],
            'children'         => $validated['children'] ?? 0,
            'infants'          => $validated['infants'] ?? 0,

            'service_price'    => $service->discounted_price ?? $service->discount_price ?? $service->full_price,
            'child_price'      => $service->child_price,
            'infant_price'     => $service->infant_price,

            'extra_charges'    => $priceDetails['extra_charges'],
            'discount_amount'  => $priceDetails['discount_amount'],
            'tax_amount'       => $priceDetails['tax_amount'],
            'subtotal'         => $priceDetails['subtotal'],
            'total'            => $priceDetails['total'],

            'paid_amount'      => 0,
            'due_amount'       => $priceDetails['total'],
            'extra_services'   => $validated['extra_services'] ?? [],
            'coupon_code'      => $validated['coupon_code'] ?? null,

            'payment_method'   => $validated['payment_method'],
            'payment_status'   => 'pending',
            'booking_status'   => 'pending',

            'customer_name'    => $validated['customer_name'],
            'customer_email'   => $validated['customer_email'],
            'customer_phone'   => $validated['customer_phone'],
            'customer_address' => $validated['customer_address'] ?? null,
            'customer_notes'   => $validated['customer_notes'] ?? null,
        ];

        if (Auth::check()) {
            $bookingData['user_id'] = Auth::id();
        }

        $booking = Booking::create($bookingData);

        switch ($validated['payment_method']) {
            case 'paypal':
                return redirect()->route('front.tourbooking.payment.paypal', $booking->booking_code);
            case 'stripe':
                return redirect()->route('front.tourbooking.payment.stripe', $booking->booking_code);
            case 'bank_transfer':
            default:
                return redirect()->route('front.tourbooking.confirm-booking', $booking->booking_code);
        }
    }

    public function confirmBooking(string $code): View
    {
        $booking = Booking::where('booking_code', $code)
            ->with(['service', 'service.media', 'user'])
            ->firstOrFail();

        return view('tourbooking::front.bookings.confirm', compact('booking'));
    }

    public function bookingSuccess(string $code): View
    {
        $booking = Booking::where('booking_code', $code)
            ->with(['service', 'user'])
            ->firstOrFail();

        return view('tourbooking::front.bookings.success', compact('booking'));
    }

    public function bookingCancel(string $code): View
    {
        $booking = Booking::where('booking_code', $code)
            ->with(['service', 'user'])
            ->firstOrFail();

        return view('tourbooking::front.bookings.cancel', compact('booking'));
    }

    public function checkAvailability(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'service_id'     => 'required|exists:services,id',
            'check_in_date'  => 'required|date',
            'check_out_date' => 'nullable|date|after_or_equal:check_in_date',
            'adults'         => 'required|integer|min:1',
            'children'       => 'nullable|integer|min:0',
            'infants'        => 'nullable|integer|min:0',
        ]);

        $service = Service::findOrFail($validated['service_id']);

        try {
            $this->verifyServiceAvailability($service, $validated['check_in_date'], $validated['check_out_date'] ?? null);

            $priceDetails = $this->calculateBookingPrice(
                $service,
                (int) $validated['adults'],
                (int) ($validated['children'] ?? 0),
                (int) ($validated['infants'] ?? 0)
            );

            return response()->json([
                'available' => true,
                'message'   => 'Service is available for the selected dates.',
                'pricing'   => $priceDetails,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'available' => false,
                'message'   => $e->getMessage(),
            ], 422);
        }
    }

    public function validateCoupon(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'coupon_code'   => 'required|string',
            'service_id'    => 'required|exists:services,id',
            'check_in_date' => 'required|date',
            'subtotal'      => 'required|numeric|min:0',
        ]);

        $coupon = Coupon::where('code', $validated['coupon_code'])
            ->where('status', true)
            ->where(function ($query) {
                $query->where('expires_at', '>=', now())
                    ->orWhereNull('expires_at');
            })
            ->first();

        if (!$coupon) {
            return response()->json(['valid' => false, 'message' => 'Invalid or expired coupon code.'], 422);
        }

        if ($coupon->service_id && (int)$coupon->service_id !== (int)$validated['service_id']) {
            return response()->json(['valid' => false, 'message' => 'This coupon is not valid for the selected service.'], 422);
        }

        if ($coupon->usage_limit && $coupon->times_used >= $coupon->usage_limit) {
            return response()->json(['valid' => false, 'message' => 'This coupon has reached its usage limit.'], 422);
        }

        $subtotal = (float)$validated['subtotal'];
        $discountAmount = 0.0;

        if ($coupon->discount_type === 'percentage') {
            $discountAmount = $subtotal * ($coupon->discount_value / 100);
            if ($coupon->max_discount_amount && $discountAmount > $coupon->max_discount_amount) {
                $discountAmount = (float)$coupon->max_discount_amount;
            }
        } else {
            $discountAmount = (float)$coupon->discount_value;
            if ($discountAmount > $subtotal) $discountAmount = $subtotal;
        }

        return response()->json([
            'valid'           => true,
            'message'         => 'Coupon applied successfully.',
            'discount_amount' => $discountAmount,
            'coupon_data'     => $coupon,
        ]);
    }

    public function myBookings(): View
    {
        $bookings = Booking::where('user_id', Auth::id())
            ->with(['service', 'service.thumbnail'])
            ->latest()
            ->paginate(10);

        return view('tourbooking::front.bookings', compact('bookings'));
    }

    public function bookingDetails(string $code): View
    {
        $booking = Booking::where('booking_code', $code)
            ->where('user_id', Auth::id())
            ->with(['service', 'service.media', 'review'])
            ->firstOrFail();

        return view('tourbooking::front.bookings.details', compact('booking'));
    }

    public function invoice(string $code): View
    {
        $booking = Booking::where('booking_code', $code)
            ->where('user_id', Auth::id())
            ->with(['service', 'service.serviceType'])
            ->firstOrFail();

        return view('tourbooking::front.bookings.invoice', compact('booking'));
    }

    public function downloadInvoicePdf(string $code)
    {
        $booking = Booking::where('booking_code', $code)
            ->where('user_id', Auth::id())
            ->with(['service', 'service.serviceType'])
            ->firstOrFail();

        $pdf = Pdf::loadView('tourbooking::front.bookings.invoice', compact('booking'))
            ->setPaper('a4')
            ->setOption('margin-top', 10)
            ->setOption('margin-right', 10)
            ->setOption('margin-bottom', 10)
            ->setOption('margin-left', 10);

        return $pdf->download('invoice-' . $booking->booking_code . '.pdf');
    }

    public function cancelBooking(Request $request, string $code): RedirectResponse
    {
        $booking = Booking::where('booking_code', $code)
            ->where('user_id', Auth::id())
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

        return redirect()->route('front.tourbooking.my-bookings')
            ->with('success', 'Your booking has been cancelled.');
    }

    public function leaveReview(Request $request, string $code): RedirectResponse
    {
        $booking = Booking::where('booking_code', $code)
            ->where('user_id', Auth::id())
            ->where('booking_status', 'completed')
            ->where('is_reviewed', false)
            ->firstOrFail();

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review_text' => 'required|string|min:10|max:1000',
            'title' => 'required|string|max:100',
        ]);

        Review::create([
            'service_id' => $booking->service_id,
            'booking_id' => $booking->id,
            'user_id' => Auth::id(),
            'rating' => $validated['rating'],
            'title' => $validated['title'],
            'content' => $validated['review_text'],
            'status' => false,
        ]);

        $booking->update(['is_reviewed' => true]);

        return redirect()->route('front.tourbooking.my-bookings')
            ->with('success', 'Your review has been submitted and is pending approval.');
    }

    /* =======================================================================
     * Helpers
     * ======================================================================= */

    private function resolveAvailability(Service $service, Request $request, string $date): ?Availability
    {
        $availability = null;

        if ($request->filled('availability_id')) {
            $a = Availability::find($request->availability_id);
            if ($a && (int)$a->service_id === (int)$service->id) $availability = $a;
        }

        if (!$availability) {
            $availability = Availability::where('service_id', $service->id)
                ->whereDate('date', $date)
                ->first();
        }

        return $availability;
    }

    private function resolveQuantities(Request $request): array
    {
        $qty = ['adult'=>0,'child'=>0,'baby'=>0,'infant'=>0];

        $aq = $request->input('age_quantities');
        if (is_array($aq)) {
            foreach ($qty as $k => $_) {
                $qty[$k] = max(0, (int)($aq[$k] ?? 0));
            }
            if (array_sum($qty) > 0) return $qty;
        }

        // legacy fallback
        $qty['adult'] = max(0, (int)$request->input('person', 0));
        $qty['child'] = max(0, (int)$request->input('children', 0));

        return $qty;
    }

    /**
     * 1) parse extras IDs safely (IMPORTANT: keys are IDs in your URL structure)
     * Returns: [array $ids, array $payloadById]
     */
    private function parseExtrasFromRequest(Request $request): array
    {
        $raw = $request->input('extras', []);
        $ids = [];
        $payloadById = [];

        if (is_string($raw)) {
            $ids = preg_split('/[\s,]+/', $raw, -1, PREG_SPLIT_NO_EMPTY);
            $ids = collect($ids)->filter(fn($v) => is_numeric($v))->map(fn($v) => (int)$v)->unique()->values()->all();
            return [$ids, []];
        }

        if (!is_array($raw)) {
            return [[], []];
        }

        $keys = array_keys($raw);
        $isAssoc = $keys !== range(0, count($raw) - 1);

        if ($isAssoc) {
            foreach ($raw as $id => $payload) {
                if (!is_numeric($id)) continue;
                $payload = is_array($payload) ? $payload : [];

                // respect active=1 if present
                if (array_key_exists('active', $payload) && (int)($payload['active'] ?? 0) !== 1) {
                    continue;
                }

                $ids[] = (int)$id;
                $payloadById[(int)$id] = $payload;
            }
        } else {
            foreach ($raw as $item) {
                if (is_numeric($item)) {
                    $ids[] = (int)$item;
                    continue;
                }
                if (is_array($item) && isset($item['id']) && is_numeric($item['id'])) {
                    if (isset($item['active']) && (int)$item['active'] !== 1) continue;
                    $ids[] = (int)$item['id'];
                    $payloadById[(int)$item['id']] = $item;
                }
            }
        }

        $ids = collect($ids)->unique()->values()->all();

        return [$ids, $payloadById];
    }

    private function fetchExtraChargesForService(Service $service, array $extraIds)
    {
        if (empty($extraIds)) return collect();

        $columns = [
            'id', 'name',
            'price', 'general_price',
            'price_type',
            'adult_price', 'child_price', 'infant_price',
            'apply_to_all_persons',
            'is_tax', 'tax_percentage',
            'is_mandatory',
        ];

        if (Schema::hasColumn('extra_charges', 'age_categories')) {
            $columns[] = 'age_categories';
        }

        $q = ExtraCharge::query()
            ->select($columns)
            ->where('status', true)
            ->whereIn('id', $extraIds);

        // dacă există service_id pe extra_charges, filtrăm (evită să “furi” extra din alt service)
        if (Schema::hasColumn('extra_charges', 'service_id')) {
            $q->where('service_id', $service->id);
        }

        return $q->get();
    }

    /**
     * Compute pickup cost (already supports age quantities).
     */
    private function computePickup(Service $service, Request $request, array $qty): array
    {
        $pickupCharge = 0.0;
        $pickupName = null;

        if (!$request->filled('pickup_point_id')) {
            return [$pickupCharge, $pickupName];
        }

        $pickupPoint = PickupPoint::find($request->pickup_point_id);
        if (!$pickupPoint) return [$pickupCharge, $pickupName];

        if ((int)$pickupPoint->service_id !== (int)$service->id) return [$pickupCharge, $pickupName];

        $pickupCharge = (float)$pickupPoint->calculateExtraCharge($qty);
        $pickupName = $pickupPoint->name;

        return [$pickupCharge, $pickupName];
    }

    /**
     * IMPORTANT: This matches your Blade logic:
     * - if age_categories enabled => per_age
     * - else if price_type=per_person => per_person (BUT if adult/child/infant columns exist => we still compute per-age)
     * - else => per_booking
     *
     * Also respects:
     * - apply_to_all_persons
     * - quantities from request (extras[id][quantities][adult]...) if apply_to_all_persons = 0
     */
    private function computeExtraLine(ExtraCharge $e, array $bookingQty, array $payload = []): array
    {
        $priceType = (string)($e->price_type ?? 'flat'); // flat|per_person
        $general   = (float)($e->general_price ?? $e->price ?? 0);

        $adult  = (float)($e->adult_price ?? 0);
        $child  = (float)($e->child_price ?? 0);
        $infant = (float)($e->infant_price ?? 0);

        $ageCats = [];
        if (property_exists($e, 'age_categories') || isset($e->age_categories)) {
            $ageCats = $this->normalizeAgeCategories($e->age_categories ?? null);
        }

        $enabledAge = collect($ageCats)->filter(fn($c) => !empty($c['enabled']))->toArray();

        // decide charge type
        $hasAgePrices = !empty($enabledAge);
        $hasLegacyPerAgeCols = ($adult > 0 || $child > 0 || $infant > 0);

        $chargeType = $hasAgePrices
            ? 'per_age'
            : ($priceType === 'per_person' ? ($hasLegacyPerAgeCols ? 'per_age' : 'per_person') : 'per_booking');

        // which qty to use for this extra?
        $applyAll = (bool)($e->apply_to_all_persons ?? false);

        $useQty = $bookingQty;
        if (!$applyAll) {
            // if user selected quantities for this extra, use them
            $q = $payload['quantities'] ?? null;
            if (is_array($q)) {
                $useQty = [
                    'adult'  => max(0, (int)($q['adult'] ?? 0)),
                    'child'  => max(0, (int)($q['child'] ?? 0)),
                    'baby'   => max(0, (int)($q['baby'] ?? 0)),
                    'infant' => max(0, (int)($q['infant'] ?? 0)),
                ];
            }
        }

        $details = [];
        $subtotal = 0.0;

        if ($chargeType === 'per_booking') {
            $subtotal = max(0.0, $general);
            $details[] = ['mode' => 'per_booking', 'amount' => $subtotal];
        }

        if ($chargeType === 'per_person') {
            $people = array_sum($useQty);
            $unit = max(0.0, $general);
            $subtotal = $people * $unit;
            $details[] = ['mode' => 'per_person', 'people' => $people, 'unit' => $unit, 'amount' => $subtotal];
        }

        if ($chargeType === 'per_age') {
            // prices per age: JSON enabled overrides, else legacy cols
            $prices = [
                'adult'  => $adult,
                'child'  => $child,
                'baby'   => $child,
                'infant' => $infant,
            ];

            if (!empty($enabledAge)) {
                foreach ($enabledAge as $k => $cfg) {
                    if (!empty($cfg['enabled'])) {
                        $prices[$k] = (float)($cfg['price'] ?? 0);
                    }
                }
            }

            foreach (['adult','child','baby','infant'] as $k) {
                $c = (int)($useQty[$k] ?? 0);
                if ($c <= 0) continue;
                $p = (float)($prices[$k] ?? 0);
                $line = $c * $p;
                $subtotal += $line;

                $details[] = ['age' => $k, 'qty' => $c, 'unit' => $p, 'line' => $line];
            }
        }

        $subtotal = (float)max(0.0, $subtotal);

        return [
            'id'          => (int)$e->id,
            'label'       => (string)$e->name,
            'charge_type' => $chargeType,
            'subtotal'    => $subtotal,
            'details'     => $details,
        ];
    }

    private function normalizeAgeCategories($raw): array
    {
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            $raw = (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) ? $decoded : [];
        } elseif ($raw instanceof \Illuminate\Support\Collection) {
            $raw = $raw->toArray();
        } elseif (is_object($raw)) {
            $raw = (array)$raw;
        }
        if (!is_array($raw)) $raw = [];

        $keys = ['adult','child','baby','infant'];
        $out = [];
        foreach ($keys as $k) {
            $row = $raw[$k] ?? [];
            $out[$k] = [
                'enabled' => (bool)($row['enabled'] ?? false),
                'price'   => $row['price'] ?? null,
            ];
        }
        return $out;
    }

    private function getUnitPricesForDate(Service $service, string $date, ?Availability $availability): array
    {
        // Dacă ai method-ul ăsta și e corect, îl folosim.
        if (method_exists($service, 'effectivePriceSetForDate')) {
            $unit = (array)$service->effectivePriceSetForDate($date);
            return array_merge(['adult'=>0,'child'=>0,'baby'=>0,'infant'=>0], $unit);
        }

        // Fallback safe
        $adult  = (float)($availability?->special_price ?? $service->price_per_person ?? $service->discount_price ?? $service->discounted_price ?? $service->full_price ?? 0);
        $child  = (float)($availability?->per_children_price ?? $service->child_price ?? $adult);
        $infant = (float)($service->infant_price ?? $adult);

        return [
            'adult'  => $adult,
            'child'  => $child,
            'baby'   => $child,
            'infant' => $infant,
        ];
    }

    /**
     * Verify service availability for the selected date.
     */
    private function verifyServiceAvailability(Service $service, string $checkInDate, ?string $checkOutDate = null): bool
    {
        $checkInDate  = \Carbon\Carbon::parse($checkInDate);
        $checkOutDate = $checkOutDate ? \Carbon\Carbon::parse($checkOutDate) : $checkInDate;

        $hasAvailabilityRecords = $service->availabilities()->exists();

        if ($hasAvailabilityRecords) {
            $availability = $service->availabilities()
                ->where('date', $checkInDate->format('Y-m-d'))
                ->where('is_available', true)
                ->first();

            if (!$availability) {
                throw new \Exception('The service is not available for the selected date.');
            }

            if ($availability->available_spots !== null) {
                $existingBookingsCount =
                    Booking::where('service_id', $service->id)
                        ->where('booking_status', '!=', 'cancelled')
                        ->whereDate('check_in_date', $checkInDate)
                        ->sum('adults')
                    +
                    Booking::where('service_id', $service->id)
                        ->where('booking_status', '!=', 'cancelled')
                        ->whereDate('check_in_date', $checkInDate)
                        ->sum('children');

                if ($existingBookingsCount >= $availability->available_spots) {
                    throw new \Exception('Not enough spots available for the selected date.');
                }
            }
        }

        $conflictingBookings = Booking::where('service_id', $service->id)
            ->where('booking_status', '!=', 'cancelled')
            ->where(function ($query) use ($checkInDate, $checkOutDate) {
                $query->whereBetween('check_in_date', [$checkInDate, $checkOutDate])
                    ->orWhereBetween('check_out_date', [$checkInDate, $checkOutDate])
                    ->orWhere(function ($q) use ($checkInDate, $checkOutDate) {
                        $q->where('check_in_date', '<=', $checkInDate)
                            ->where('check_out_date', '>=', $checkOutDate);
                    });
            })
            ->exists();

        if ($conflictingBookings) {
            throw new \Exception('The service is already booked for the selected dates.');
        }

        return true;
    }

    /**
     * Calculate booking price details.
     * (kept for API/old endpoints; does not break new checkout)
     */
    private function calculateBookingPrice(
        Service $service,
        int $adults,
        int $children = 0,
        int $infants = 0,
        array $extraServices = [],
        ?string $couponCode = null
    ): array {
        $basePrice = 0.0;

        $isPerPerson = (bool)($service->price_per_person ?? $service->is_per_person ?? false);
        if ($isPerPerson) {
            $basePrice =
                ($adults   * (float)($service->discounted_price ?? $service->discount_price ?? 0)) +
                ($children * (float)($service->child_price ?? 0)) +
                ($infants  * (float)($service->infant_price ?? 0));
        } else {
            $basePrice = (float)($service->discounted_price ?? $service->discount_price ?? $service->full_price ?? 0);
        }

        // Extra charges simplified for old endpoints:
        $extraChargesAmount = 0.0;
        if (!empty($extraServices)) {
            $ids = array_map('intval', array_keys($extraServices));
            $charges = ExtraCharge::whereIn('id', $ids)->get();

            foreach ($charges as $charge) {
                $quantity = (int)($extraServices[$charge->id] ?? 1);
                $extraChargesAmount += ((float)$charge->price) * $quantity;
            }
        }

        $subtotal = $basePrice + $extraChargesAmount;

        $discountAmount = 0.0;
        if ($couponCode) {
            $coupon = Coupon::where('code', $couponCode)
                ->where('status', true)
                ->where(function ($query) {
                    $query->where('expires_at', '>=', now())->orWhereNull('expires_at');
                })
                ->first();

            if ($coupon && (!$coupon->service_id || (int)$coupon->service_id === (int)$service->id)) {
                if ($coupon->discount_type === 'percentage') {
                    $discountAmount = $subtotal * ((float)$coupon->discount_value / 100);
                    if ($coupon->max_discount_amount && $discountAmount > (float)$coupon->max_discount_amount) {
                        $discountAmount = (float)$coupon->max_discount_amount;
                    }
                } else {
                    $discountAmount = (float)$coupon->discount_value;
                    if ($discountAmount > $subtotal) $discountAmount = $subtotal;
                }
            }
        }

        $taxAmount = 0.0;
        $taxPercentage = (float)config('tourbooking.tax_percentage', 0);
        if ($taxPercentage > 0) {
            $taxAmount = ($subtotal - $discountAmount) * ($taxPercentage / 100);
        }

        $total = $subtotal - $discountAmount + $taxAmount;

        return [
            'base_price'       => $basePrice,
            'extra_charges'    => $extraChargesAmount,
            'subtotal'         => $subtotal,
            'discount_amount'  => $discountAmount,
            'tax_amount'       => $taxAmount,
            'total'            => $total,
        ];
    }
}