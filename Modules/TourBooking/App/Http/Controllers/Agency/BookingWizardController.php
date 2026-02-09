<?php

declare(strict_types=1);

namespace Modules\TourBooking\App\Http\Controllers\Agency;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

use Modules\TourBooking\App\Models\Availability;
use Modules\TourBooking\App\Models\ExtraCharge;
use Modules\TourBooking\App\Models\PickupPoint;
use Modules\TourBooking\App\Models\Service;

/**
 * BookingWizardController
 *
 * Provides a step-by-step wizard flow for agencies to create bookings on behalf of clients.
 *
 * @package Modules\TourBooking\App\Http\Controllers\Agency
 */
final class BookingWizardController extends Controller
{
    /**
     * Wizard page (Agency Dashboard)
     */
    public function index(): \Illuminate\View\View
    {
        return view('tourbooking::agency.bookings.wizard');
    }

    /**
     * Step 1: clients list (search)
     */
    public function clients(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        $modelCandidates = [
            '\\App\\Models\\AgencyClient',
            '\\Modules\\TourBooking\\App\\Models\\AgencyClient',
        ];

        $modelClass = null;
        foreach ($modelCandidates as $candidate) {
            if (class_exists($candidate)) {
                $modelClass = $candidate;
                break;
            }
        }

        if (!$modelClass) {
            return response()->json(['data' => []]);
        }

        $m = new $modelClass();
        $table = $m->getTable();

        /** @var Builder $query */
        $query = $modelClass::query();

        if (Schema::hasColumn($table, 'agency_user_id')) {
            $query->where('agency_user_id', Auth::id());
        }

        if ($q !== '') {
            $query->where(function (Builder $b) use ($q, $table) {
                if (Schema::hasColumn($table, 'first_name')) {
                    $b->orWhere('first_name', 'like', "%{$q}%");
                }
                if (Schema::hasColumn($table, 'last_name')) {
                    $b->orWhere('last_name', 'like', "%{$q}%");
                }
                if (Schema::hasColumn($table, 'email')) {
                    $b->orWhere('email', 'like', "%{$q}%");
                }
                if (Schema::hasColumn($table, 'phone')) {
                    $b->orWhere('phone', 'like', "%{$q}%");
                }
                if (Schema::hasColumn($table, 'company')) {
                    $b->orWhere('company', 'like', "%{$q}%");
                }
                if (Schema::hasColumn($table, 'notes')) {
                    $b->orWhere('notes', 'like', "%{$q}%");
                }
                if (Schema::hasColumn($table, 'full_name')) {
                    $b->orWhere('full_name', 'like', "%{$q}%");
                }
            });
        }

        $items = $query->orderByDesc('id')->limit(25)->get();

        return response()->json([
            'data' => $items->map(function ($c) use ($table) {
                $name = null;

                if (Schema::hasColumn($table, 'full_name') && !empty($c->full_name)) {
                    $name = (string) $c->full_name;
                } else {
                    $name = trim(((string)($c->first_name ?? '')) . ' ' . ((string)($c->last_name ?? '')));
                }

                return [
                    'id' => (int) $c->id,
                    'name' => $name !== '' ? $name : ('Client #' . $c->id),
                    'email' => $c->email ?? null,
                    'phone' => $c->phone ?? null,
                ];
            })->values(),
        ]);
    }

    /**
     * Step 2: availabilityMap (datepicker)
     * IMPORTANT: în DB coloana este `available_spots`
     * IMPORTANT: services sunt pe `user_id` (sau agency_user_id dacă există)
     */
    public function availabilityMap(Request $request): JsonResponse
    {
        $days = (int) $request->input('days', 180);
        $days = max(1, min(366, $days));

        $serviceIds = $this->serviceQuery()->pluck('id')->all();
        if (empty($serviceIds)) {
            return response()->json(['data' => []]);
        }

        $from = now()->startOfDay()->toDateString();
        $to   = now()->addDays($days)->endOfDay()->toDateString();

        $rows = Availability::query()
            ->whereIn('service_id', $serviceIds)
            ->whereDate('date', '>=', $from)
            ->whereDate('date', '<=', $to)
            ->get(['date', 'available_spots', 'service_id']);

        $map = [];
        foreach ($rows as $r) {
            $date = (string) $r->date;
            $spots = (int) ($r->available_spots ?? 0);

            if (!isset($map[$date])) {
                $map[$date] = [
                    'is_available' => $spots > 0,
                    'spots'        => $spots,
                    'services'     => [(int) $r->service_id],
                ];
            } else {
                $map[$date]['spots'] = max((int) $map[$date]['spots'], $spots);
                $map[$date]['is_available'] = ((int) $map[$date]['spots'] > 0);
                $map[$date]['services'][] = (int) $r->service_id;
            }
        }

        return response()->json(['data' => $map]);
    }

    /**
     * Step 3: servicii + availability (date + pax)
     *
     * Returnează structura "UI friendly":
     * [
     *  id, title, slug, location,
     *  availability: { id, date, spots, required, is_available, label },
     *  prices: { base:{adult,child,baby,infant}, availability:{adult,child,baby,infant} }
     * ]
     */
    public function services(Request $request): JsonResponse
    {
        $term = trim((string) $request->get('q', ''));
        $date = (string) $request->get('date', '');
        $date = $this->normalizeDate($date);

        $pax = $this->normalizePax($request->get('pax', []));
        $totalPax = array_sum($pax);
        if ($totalPax < 1) {
            $totalPax = max(1, (int) ($pax['adult'] ?? 1));
        }

        $serviceTable = (new Service())->getTable();

        $q = $this->serviceQuery();

        if ($term !== '') {
            $q->where(function (Builder $qq) use ($term, $serviceTable) {
                if (Schema::hasColumn($serviceTable, 'title')) {
                    $qq->orWhere('title', 'like', "%{$term}%");
                }
                if (Schema::hasColumn($serviceTable, 'slug')) {
                    $qq->orWhere('slug', 'like', "%{$term}%");
                }
                if (Schema::hasColumn($serviceTable, 'location')) {
                    $qq->orWhere('location', 'like', "%{$term}%");
                }
            });
        }

        $services = $q->orderByDesc('id')->limit(200)->get();

        if ($services->isEmpty()) {
            return response()->json(['data' => []]);
        }

        $serviceIds = $services->pluck('id')->map(fn($x) => (int) $x)->all();

        $availabilityByService = Availability::query()
            ->whereIn('service_id', $serviceIds)
            ->whereDate('date', '=', $date)
            ->get()
            ->keyBy('service_id');

        $data = $services->map(function (Service $s) use ($availabilityByService, $date, $totalPax, $serviceTable) {
            /** @var Availability|null $av */
            $av = $availabilityByService->get((int) $s->id);

            $spots = $this->getSpots($av);
            $isAvailable = $av !== null && $spots >= $totalPax;

            $base = $this->getBasePricesForService($s);
            $availPrices = $av ? $this->getPricesFromAvailabilityOrFallback($av, $base) : $base;

            $label = $isAvailable ? 'Available' : ($av ? 'Not enough spots' : 'No availability');

            return [
                'id' => (int) $s->id,
                'title' => Schema::hasColumn($serviceTable, 'title') ? (string) ($s->title ?? ('Service #' . $s->id)) : ('Service #' . $s->id),
                'slug' => Schema::hasColumn($serviceTable, 'slug') ? ($s->slug ?? null) : null,
                'location' => Schema::hasColumn($serviceTable, 'location') ? ($s->location ?? null) : null,

                'availability' => [
                    'id' => $av?->id,
                    'date' => $date,
                    'spots' => $spots,
                    'required' => $totalPax,
                    'is_available' => $isAvailable,
                    'label' => $label,
                ],

                'prices' => [
                    'base' => $base,
                    'availability' => $availPrices,
                ],
            ];
        })->values();

        return response()->json(['data' => $data]);
    }

    /**
     * Step 4: extras pentru service
     */
    public function extras(Request $request, Service $service): JsonResponse
    {
        $pax = $this->normalizePax($request->query('pax', []));

        if (!$this->serviceBelongsToAgency($service)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $extras = ExtraCharge::query()
            ->where('service_id', $service->id)
            ->orderBy('id')
            ->get();

        $data = $extras->map(function ($e) use ($pax) {
            $cfg = $this->normalizeExtra($e);

            $defaultQty = [
                'adult' => $cfg['charge_type'] === 'per_age' ? $pax['adult'] : 0,
                'child' => $cfg['charge_type'] === 'per_age' ? $pax['child'] : 0,
                'baby' => $cfg['charge_type'] === 'per_age' ? $pax['baby'] : 0,
                'infant' => $cfg['charge_type'] === 'per_age' ? $pax['infant'] : 0,
            ];

            return $cfg + ['default_quantities' => $defaultQty];
        })->values();

        return response()->json(['data' => $data]);
    }

    /**
     * Step 4 (optional): pickup points
     */
    public function pickupPoints(Request $request, Service $service): JsonResponse
    {
        if (!$this->serviceBelongsToAgency($service)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $userLat = $request->query('user_lat');
        $userLng = $request->query('user_lng');

        $rows = PickupPoint::query()
            ->where('service_id', $service->id)
            ->orderByDesc('id')
            ->get();

        $data = $rows->map(function ($p) use ($userLat, $userLng) {
            $lat = $p->latitude ?? $p->lat ?? null;
            $lng = $p->longitude ?? $p->lng ?? null;

            $charge = (float) ($p->extra_charge ?? 0);
            $chargeType = (string) ($p->charge_type ?? 'per_booking');

            $distance = null;
            if ($userLat !== null && $userLng !== null && $lat !== null && $lng !== null) {
                $distance = $this->haversineKm((float) $userLat, (float) $userLng, (float) $lat, (float) $lng);
            }

            return [
                'id' => (int) $p->id,
                'name' => (string) ($p->name ?? 'Pickup'),
                'address' => $p->address ?? null,
                'coordinates' => ['lat' => $lat, 'lng' => $lng],
                'has_charge' => $charge > 0,
                'charge_type' => $chargeType,
                'charge' => $charge,
                'formatted_charge' => number_format($charge, 2, '.', ''),
                'is_default' => (bool) ($p->is_default ?? false),
                'distance_km' => $distance,
            ];
        })->values();

        return response()->json(['data' => $data]);
    }

    /**
     * Step 4 (optional): calc pickup charge
     * Acceptă: age_quantities SAU pax
     */
    public function calculatePickupCharge(Request $request): JsonResponse
    {
        $request->validate([
            'pickup_point_id' => ['required', 'integer'],
            'age_quantities' => ['nullable', 'array'],
            'pax' => ['nullable', 'array'],
        ]);

        /** @var PickupPoint|null $pp */
        $pp = PickupPoint::query()->find($request->integer('pickup_point_id'));
        if (!$pp) {
            return response()->json(['message' => 'Pickup point not found'], 404);
        }

        $service = Service::query()->find((int) $pp->service_id);
        if ($service && !$this->serviceBelongsToAgency($service)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $paxRaw = $request->input('age_quantities', $request->input('pax', []));
        $pax = $this->normalizePax($paxRaw);
        $totalPax = array_sum($pax);

        $base = (float) ($pp->extra_charge ?? 0);
        $chargeType = (string) ($pp->charge_type ?? 'per_booking');

        $amount = $chargeType === 'per_person' ? ($base * $totalPax) : $base;

        return response()->json([
            'data' => [
                'pickup_point_id' => (int) $pp->id,
                'charge_type' => $chargeType,
                'amount' => round($amount, 2),
            ],
        ]);
    }

    /**
     * Step 5: payment methods
     */
    public function paymentMethods(): JsonResponse
    {
        $methods = [
            ['key' => 'cash',      'label' => 'Cash (unpaid)',          'type' => 'offline', 'paid' => false],
            ['key' => 'cash_paid', 'label' => 'Cash (paid)',            'type' => 'offline', 'paid' => true],
            ['key' => 'bank',      'label' => 'Bank transfer (unpaid)', 'type' => 'offline', 'paid' => false],
            ['key' => 'bank_paid', 'label' => 'Bank transfer (paid)',   'type' => 'offline', 'paid' => true],
        ];

        if (Schema::hasTable('payment_gateways') && Schema::hasColumn('payment_gateways', 'key') && Schema::hasColumn('payment_gateways', 'value')) {
            $rows = DB::table('payment_gateways')
                ->where('key', 'like', '%_status')
                ->get(['key', 'value']);

            $enabled = [];
            foreach ($rows as $r) {
                $key = (string) $r->key;     // ex: stripe_status
                $val = (string) $r->value;   // ex: "1"
                if ($val !== '1') {
                    continue;
                }
                $gateway = strtolower(str_replace('_status', '', $key)); // ex: stripe
                $enabled[$gateway] = true;
            }

            $gatewayLabels = [
                'stripe' => 'Card (Stripe)',
                'paypal' => 'Card/PayPal',
                'paystack' => 'Card (Paystack)',
                'flutterwave' => 'Card (Flutterwave)',
                'mollie' => 'Card (Mollie)',
                'razorpay' => 'Card (Razorpay)',
                'payu' => 'Card (PayU)',
                'mercadopago' => 'Card (MercadoPago)',
                'midtrans' => 'Card (Midtrans)',
                'sslcommerz' => 'Card (SSLCommerz)',
                'instamojo' => 'Card (Instamojo)',
                'paytabs' => 'Card (PayTabs)',
                'toyyibpay' => 'Card (ToyyibPay)',
                'xendit' => 'Card (Xendit)',
            ];

            foreach (array_keys($enabled) as $gw) {
                $methods[] = [
                    'key' => $gw,
                    'label' => $gatewayLabels[$gw] ?? ('Card (' . ucfirst($gw) . ')'),
                    'type' => 'gateway',
                    'paid' => false,
                ];
            }
        }

        return response()->json(['data' => $methods]);
    }

    /**
     * Step 6: quote (breakdown)
     * Acceptă:
     *  - pax SAU age_quantities
     *  - extras SAU extras_payload
     */
    public function quote(Request $request): JsonResponse
    {
        $request->validate([
            'service_id' => ['required', 'integer'],
            'date' => ['required', 'date_format:Y-m-d'],

            'pax' => ['nullable', 'array'],
            'age_quantities' => ['nullable', 'array'],

            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'pickup_point_id' => ['nullable', 'integer'],

            'extras' => ['nullable', 'array'],
            'extras_payload' => ['nullable', 'array'],
        ]);

        $service = $this->serviceQuery()->find($request->integer('service_id'));
        if (!$service) {
            return response()->json(['message' => 'Service not found'], 404);
        }

        $date = (string) $request->input('date');

        $paxRaw = $request->input('pax', $request->input('age_quantities', []));
        $pax = $this->normalizePax($paxRaw);
        if (($pax['adult'] ?? 0) < 1) {
            return response()->json(['message' => 'Adult must be >= 1'], 422);
        }

        $totalPax = array_sum($pax);

        $availability = Availability::query()
            ->where('service_id', $service->id)
            ->whereDate('date', '=', $date)
            ->first();

        $spots = $this->getSpots($availability);
        if (!$availability || $spots < $totalPax) {
            return response()->json([
                'message' => 'No availability for selected date / pax',
                'data' => [
                    'availability' => [
                        'exists' => (bool) $availability,
                        'spots' => $spots,
                        'required' => $totalPax,
                    ],
                ],
            ], 422);
        }

        $basePrices = $this->getBasePricesForService($service);
        $prices = $this->getPricesFromAvailabilityOrFallback($availability, $basePrices);

        $tickets = [
            'adult' => round($pax['adult'] * $prices['adult'], 2),
            'child' => round($pax['child'] * $prices['child'], 2),
            'baby' => round($pax['baby'] * $prices['baby'], 2),
            'infant' => round($pax['infant'] * $prices['infant'], 2),
        ];
        $ticketsSubtotal = round(array_sum($tickets), 2);

        // extras
        $extrasPayload = (array) $request->input('extras', $request->input('extras_payload', []));
        $extrasTotal = 0.0;
        $extrasLines = [];

        $dbExtras = ExtraCharge::query()
            ->where('service_id', $service->id)
            ->get()
            ->keyBy('id');

        foreach ($dbExtras as $id => $extra) {
            $cfg = $this->normalizeExtra($extra);

            $payloadRow = collect($extrasPayload)->firstWhere('id', (int) $id);
            $isActive = (bool) ($cfg['is_mandatory'] || ($payloadRow['active'] ?? false));
            if (!$isActive) {
                continue;
            }

            $lineAmount = 0.0;

            if ($cfg['is_tax']) {
                $pct = (float) ($cfg['tax_percentage'] ?? 0);
                $lineAmount = ($pct / 100.0) * $ticketsSubtotal;
            } elseif ($cfg['charge_type'] === 'per_booking') {
                $lineAmount = (float) $cfg['price'];
            } elseif ($cfg['charge_type'] === 'per_person') {
                $lineAmount = (float) $cfg['price'] * $totalPax;
            } else {
                // per_age
                $quantities = $payloadRow['quantities'] ?? [];
                $quantities = $this->normalizePax($quantities);

                $useAll = (bool) ($cfg['apply_to_all_persons'] || $cfg['is_mandatory']);
                if ($useAll) {
                    $quantities = $pax;
                } else {
                    $quantities['adult'] = min($quantities['adult'], $pax['adult']);
                    $quantities['child'] = min($quantities['child'], $pax['child']);
                    $quantities['baby'] = min($quantities['baby'], $pax['baby']);
                    $quantities['infant'] = min($quantities['infant'], $pax['infant']);
                }

                $pp = $cfg['prices_per_age'];
                $lineAmount =
                    ($quantities['adult'] * (float) ($pp['adult'] ?? 0)) +
                    ($quantities['child'] * (float) ($pp['child'] ?? 0)) +
                    ($quantities['baby'] * (float) ($pp['baby'] ?? 0)) +
                    ($quantities['infant'] * (float) ($pp['infant'] ?? 0));
            }

            $lineAmount = round($lineAmount, 2);
            $extrasTotal += $lineAmount;

            $extrasLines[] = [
                'id' => (int) $id,
                'name' => $cfg['name'],
                'charge_type' => $cfg['charge_type'],
                'is_tax' => (bool) $cfg['is_tax'],
                'amount' => $lineAmount,
            ];
        }

        $extrasTotal = round($extrasTotal, 2);

        // pickup
        $pickupAmount = 0.0;
        $pickupPointId = $request->input('pickup_point_id');
        if (!empty($pickupPointId)) {
            $pp = PickupPoint::query()->find((int) $pickupPointId);
            if ($pp) {
                $base = (float) ($pp->extra_charge ?? 0);
                $chargeType = (string) ($pp->charge_type ?? 'per_booking');
                $pickupAmount = $chargeType === 'per_person' ? $base * $totalPax : $base;
                $pickupAmount = round($pickupAmount, 2);
            }
        }

        $discount = round((float) $request->input('discount_amount', 0), 2);
        $taxAmount = round((float) $request->input('tax_amount', 0), 2);

        $subtotal = round($ticketsSubtotal + $extrasTotal + $pickupAmount - $discount, 2);
        $total = round($subtotal + $taxAmount, 2);

        return response()->json([
            'data' => [
                'availability_id' => $availability->id ?? null,
                'spots' => $spots,
                'required_pax' => $totalPax,

                'pax' => $pax,
                'prices' => $prices,

                'tickets' => $tickets,
                'tickets_subtotal' => $ticketsSubtotal,

                'extras' => $extrasLines,
                'extras_total' => $extrasTotal,

                'pickup_amount' => $pickupAmount,
                'discount_amount' => $discount,
                'tax_amount' => $taxAmount,

                'subtotal' => $subtotal,
                'total' => $total,
            ],
        ]);
    }

    /* ============================================================
       Helpers (SQL-safe pentru schema ta)
    ============================================================ */

    private function serviceQuery(): Builder
    {
        $q = Service::query();
        $table = (new Service())->getTable();

        if (Schema::hasColumn($table, 'agency_user_id')) {
            $q->where('agency_user_id', Auth::id());
        } elseif (Schema::hasColumn($table, 'user_id')) {
            $q->where('user_id', Auth::id());
        }

        return $q;
    }

    private function serviceBelongsToAgency(Service $service): bool
    {
        $table = $service->getTable();

        if (Schema::hasColumn($table, 'agency_user_id')) {
            return (int) $service->agency_user_id === (int) Auth::id();
        }

        if (Schema::hasColumn($table, 'user_id')) {
            return (int) $service->user_id === (int) Auth::id();
        }

        return true;
    }

    private function normalizeDate(?string $date): string
    {
        $d = $date ? Carbon::parse($date) : Carbon::today();
        return $d->toDateString();
    }

    private function normalizePax($paxRaw): array
    {
        $pax = is_array($paxRaw) ? $paxRaw : [];

        $out = [
            'adult' => (int) ($pax['adult'] ?? $pax['adults'] ?? 0),
            'child' => (int) ($pax['child'] ?? $pax['children'] ?? 0),
            'baby' => (int) ($pax['baby'] ?? 0),
            'infant' => (int) ($pax['infant'] ?? $pax['infants'] ?? 0),
        ];

        foreach ($out as $k => $v) {
            $out[$k] = max(0, (int) $v);
        }

        return $out;
    }

    private function getSpots($availability): int
    {
        if (!$availability) {
            return 0;
        }

        if (isset($availability->available_spots) && is_numeric($availability->available_spots)) {
            return (int) $availability->available_spots;
        }

        foreach (['spots', 'available_seats', 'slots', 'slot'] as $field) {
            if (isset($availability->{$field}) && is_numeric($availability->{$field})) {
                return (int) $availability->{$field};
            }
        }

        return 0;
    }

    private function getBasePricesForService(Service $service): array
    {
        $adult = (float) ($service->price_per_person ?? $service->adult_price ?? $service->price ?? 0);
        $child = (float) ($service->child_price ?? 0);
        $baby = (float) ($service->baby_price ?? 0);
        $infant = (float) ($service->infant_price ?? 0);

        if (isset($service->age_categories) && !empty($service->age_categories)) {
            $decoded = is_array($service->age_categories)
                ? $service->age_categories
                : json_decode((string) $service->age_categories, true);

            if (is_array($decoded)) {
                $adult = (float) ($decoded['adult']['price'] ?? $adult);
                $child = (float) ($decoded['child']['price'] ?? $child);
                $baby = (float) ($decoded['baby']['price'] ?? $baby);
                $infant = (float) ($decoded['infant']['price'] ?? $infant);
            }
        }

        return [
            'adult' => round($adult, 2),
            'child' => round($child, 2),
            'baby' => round($baby, 2),
            'infant' => round($infant, 2),
        ];
    }

    private function getPricesFromAvailabilityOrFallback(Availability $availability, array $fallback): array
    {
        if (isset($availability->age_categories) && !empty($availability->age_categories)) {
            $decoded = is_array($availability->age_categories)
                ? $availability->age_categories
                : json_decode((string) $availability->age_categories, true);

            if (is_array($decoded)) {
                return [
                    'adult' => round((float) ($decoded['adult']['price'] ?? $fallback['adult']), 2),
                    'child' => round((float) ($decoded['child']['price'] ?? $fallback['child']), 2),
                    'baby' => round((float) ($decoded['baby']['price'] ?? $fallback['baby']), 2),
                    'infant' => round((float) ($decoded['infant']['price'] ?? $fallback['infant']), 2),
                ];
            }
        }

        if (isset($availability->prices) && !empty($availability->prices)) {
            $decoded = is_array($availability->prices)
                ? $availability->prices
                : json_decode((string) $availability->prices, true);

            if (is_array($decoded)) {
                return [
                    'adult' => round((float) ($decoded['adult'] ?? $fallback['adult']), 2),
                    'child' => round((float) ($decoded['child'] ?? $fallback['child']), 2),
                    'baby' => round((float) ($decoded['baby'] ?? $fallback['baby']), 2),
                    'infant' => round((float) ($decoded['infant'] ?? $fallback['infant']), 2),
                ];
            }
        }

        $adult = (float) ($availability->special_price ?? $fallback['adult']);
        $child = (float) ($availability->per_children_price ?? $fallback['child']);
        $baby = (float) ($availability->baby_price ?? $fallback['baby']);
        $infant = (float) ($availability->infant_price ?? $fallback['infant']);

        return [
            'adult' => round($adult, 2),
            'child' => round($child, 2),
            'baby' => round($baby, 2),
            'infant' => round($infant, 2),
        ];
    }

    private function normalizeExtra($extra): array
    {
        $priceType = $extra->price_type ?? null;
        $isTax = (bool) ($extra->is_tax ?? false);

        $pricesPerAge = [
            'adult' => (float) ($extra->adult_price ?? 0),
            'child' => (float) ($extra->child_price ?? 0),
            'baby' => 0.0,
            'infant' => (float) ($extra->infant_price ?? 0),
        ];

        $hasAgeCats = false;
        if (isset($extra->age_categories) && !empty($extra->age_categories)) {
            $decoded = is_array($extra->age_categories)
                ? $extra->age_categories
                : json_decode((string) $extra->age_categories, true);

            $hasAgeCats = is_array($decoded) && !empty($decoded);

            if (is_array($decoded)) {
                $pricesPerAge['adult'] = (float) ($decoded['adult']['price'] ?? $pricesPerAge['adult']);
                $pricesPerAge['child'] = (float) ($decoded['child']['price'] ?? $pricesPerAge['child']);
                $pricesPerAge['baby'] = (float) ($decoded['baby']['price'] ?? $pricesPerAge['baby']);
                $pricesPerAge['infant'] = (float) ($decoded['infant']['price'] ?? $pricesPerAge['infant']);
            }
        }

        $hasPerAgePrices = ($pricesPerAge['adult'] + $pricesPerAge['child'] + $pricesPerAge['baby'] + $pricesPerAge['infant']) > 0;

        $chargeType = 'per_booking';
        if ($isTax) {
            $chargeType = 'tax';
        } elseif ($hasAgeCats || ($priceType === 'per_person' && $hasPerAgePrices)) {
            $chargeType = 'per_age';
        } elseif ($priceType === 'per_person') {
            $chargeType = 'per_person';
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
                'adult' => round($pricesPerAge['adult'], 2),
                'child' => round($pricesPerAge['child'], 2),
                'baby' => round($pricesPerAge['baby'], 2),
                'infant' => round($pricesPerAge['infant'], 2),
            ],
        ];
    }

    private function haversineKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earth = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) ** 2 +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) ** 2;

        $c = 2 * asin(min(1, sqrt($a)));

        return round($earth * $c, 2);
    }
}