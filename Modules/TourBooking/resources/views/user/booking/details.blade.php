@extends('user.master_layout')
@section('title')
    <title>{{ __('translate.Booking Details') }}</title>
@endsection

@section('body-header')
    <h3 class="crancy-header__title m-0">{{ __('translate.Booking Details') }}</h3>
    <p class="crancy-header__text">{{ __('translate.Dashboard') }} >> {{ __('translate.Booking Details') }}</p>
@endsection

@section('body-content')
@php
    $decode = function ($v) {
        if (is_array($v)) return $v;
        if (is_string($v)) { $d = json_decode($v, true); return json_last_error() === JSON_ERROR_NONE ? ($d ?: []) : []; }
        return [];
    };
    $ageBreakdown  = $decode($booking->age_breakdown ?? []);
    $ageQuantities = $decode($booking->age_quantities ?? []);
    $ageConfig     = $decode($booking->age_config ?? []);

    if (empty($ageBreakdown) && !empty($ageQuantities)) {
        foreach ($ageQuantities as $k => $qty) {
            $qty = (int)$qty;
            if ($qty <= 0) continue;
            $label = $ageConfig[$k]['label'] ?? ucfirst((string)$k);
            $price = (float)($ageConfig[$k]['price'] ?? 0);
            $ageBreakdown[$k] = ['label' => $label, 'qty' => $qty, 'price' => $price, 'line' => $price * $qty];
        }
    }

    $badgeClass = fn($s) => match (strtolower((string)$s)) {
        'confirmed','success','completed' => 'bd-badge--success',
        'pending'                         => 'bd-badge--warning',
        'cancelled'                       => 'bd-badge--danger',
        default                           => 'bd-badge--info',
    };

    // Reconstruct individual extra charge lines from service
    $reconstructedExtras = [];
    $reconstructedTotal  = 0;
    if ($booking->service_id) {
        $serviceExtras = \Modules\TourBooking\App\Models\ExtraCharge::where('service_id', $booking->service_id)
            ->where('status', 1)->get();
        $pax = ['adult' => 0, 'child' => 0, 'baby' => 0, 'infant' => 0];
        foreach ($ageBreakdown as $key => $row) { $pax[$key] = (int)($row['qty'] ?? 0); }
        if (array_sum($pax) === 0) {
            $pax['adult'] = (int)$booking->adults;
            $pax['child'] = (int)$booking->children;
            $pax['infant'] = (int)($booking->infants ?? 0);
        }
        $totalPax = array_sum($pax);
        $ticketSum = 0;
        foreach ($ageBreakdown as $row) { $ticketSum += (float)($row['line'] ?? 0); }

        foreach ($serviceExtras as $ex) {
            $isTax     = (bool)($ex->is_tax ?? false);
            $priceType = (string)($ex->price_type ?? 'flat');
            $price     = (float)($ex->price ?? 0);
            $lineAmt   = 0;

            if ($isTax) {
                $lineAmt = round(((float)($ex->tax_percentage ?? 0) / 100) * $ticketSum, 2);
            } elseif (in_array($priceType, ['flat','per_booking'])) {
                $lineAmt = $price;
            } elseif ($priceType === 'per_person') {
                $aP = (float)($ex->adult_price ?? $price); $cP = (float)($ex->child_price ?? 0);
                $iP = (float)($ex->infant_price ?? 0); $bP = (float)($ex->baby_price ?? 0);
                $lineAmt = ($aP > 0 || $cP > 0 || $iP > 0 || $bP > 0)
                    ? ($pax['adult']*$aP + $pax['child']*$cP + $pax['infant']*$iP + $pax['baby']*$bP)
                    : $price * $totalPax;
            }
            $lineAmt = round($lineAmt, 2);
            if ($lineAmt > 0 || (bool)($ex->is_mandatory ?? false)) {
                $reconstructedExtras[] = ['name' => $ex->name, 'amount' => $lineAmt, 'type' => $priceType];
                $reconstructedTotal += $lineAmt;
            }
        }
    }

    $pickupCharge = (float)($booking->pickup_charge ?? 0);
    if ($pickupCharge <= 0 && !empty($booking->pickup_point_id)) {
        $ppRecord = \DB::table('pickup_points')->where('id', (int)$booking->pickup_point_id)->first();
        if ($ppRecord && (float)($ppRecord->extra_charge ?? 0) > 0) {
            $base = (float)$ppRecord->extra_charge;
            $ct = (string)($ppRecord->charge_type ?? 'per_booking');
            $pickupCharge = $ct === 'per_person' ? $base * array_sum($pax) : $base;
        }
    }

    $canShowInvoice = in_array(strtolower((string)$booking->booking_status), ['confirmed','success','completed'], true)
        && in_array(strtolower((string)$booking->payment_status), ['success','completed','confirmed'], true);
@endphp

@include('tourbooking::components._booking_detail_styles')

<section class="crancy-adashboard crancy-show">
    <div class="container container__bscreen">
        <div class="row"><div class="col-12"><div class="crancy-body"><div class="crancy-dsinner">
            <div class="bd-page mg-top-30">

<div class="bd-header">
    <a href="{{ route('user.bookings.index') }}" class="bd-back"><i class="fa fa-arrow-left"></i> {{ __('translate.Back') }}</a>
    <span class="bd-code">#{{ $booking->booking_code }}</span>
    <span class="bd-badge {{ $badgeClass($booking->booking_status) }}">{{ ucfirst($booking->booking_status) }}</span>
    <span class="bd-badge {{ $badgeClass($booking->payment_status) }}">{{ ucfirst($booking->payment_status) }}</span>
    @php $travelDate = $booking->booking_date ?? $booking->check_in_date ?? null; @endphp
    <span class="bd-trip-date"><i class="fa fa-calendar-alt"></i> {{ $travelDate ? \Carbon\Carbon::parse($travelDate)->format('d M Y') : '—' }}</span>
    <span class="bd-header-spacer"></span>
</div>

<div class="bd-actions">
    @if($canShowInvoice)
    <a href="{{ route('user.bookings.invoice', $booking->booking_code) }}" target="_blank" class="bd-btn"><i class="fa fa-file-invoice"></i> {{ __('translate.View Invoice') }}</a>
    <a href="{{ route('user.bookings.invoice.download', $booking->booking_code) }}" class="bd-btn"><i class="fa fa-download"></i> {{ __('translate.Download Invoice') }}</a>
    @endif
    @if (!in_array(strtolower((string)$booking->booking_status), ['cancelled','completed']))
    <a href="#" class="bd-btn bd-btn--danger" data-bs-toggle="modal" data-bs-target="#cancelModal"><i class="fa fa-times"></i> {{ __('translate.Cancel Booking') }}</a>
    @endif
</div>

<div class="bd-summary">
    <div class="bd-summary-item">
        <div class="bd-summary-item__label">{{ __('translate.Total Amount') }}</div>
        <div class="bd-summary-item__value">{{ currency((float)$booking->total) }}</div>
    </div>
    <div class="bd-summary-item">
        <div class="bd-summary-item__label">{{ __('translate.Paid Amount') }}</div>
        <div class="bd-summary-item__value">{{ currency((float)$booking->paid_amount) }}</div>
    </div>
    <div class="bd-summary-item">
        <div class="bd-summary-item__label">{{ __('translate.Due Amount') }}</div>
        <div class="bd-summary-item__value">{{ (float)($booking->due_amount ?? 0) > 0 ? currency((float)$booking->due_amount) : '—' }}</div>
    </div>
</div>

<div class="bd-grid-2">
    <div>
        <div class="bd-card" style="margin-bottom:16px">
            <div class="bd-card__hdr">{{ __('translate.Billed To') }}</div>
            <div class="bd-card__body">
                <div class="bd-kv"><span class="bd-kv__k">{{ __('translate.Name') }}</span><span class="bd-kv__v">{{ $booking->customer_name ?? '—' }}</span></div>
                <div class="bd-kv"><span class="bd-kv__k">{{ __('translate.Phone') }}</span><span class="bd-kv__v">{{ $booking->customer_phone ?? '—' }}</span></div>
                <div class="bd-kv"><span class="bd-kv__k">{{ __('translate.Email') }}</span><span class="bd-kv__v">{{ $booking->customer_email ?? '—' }}</span></div>
                <div class="bd-kv"><span class="bd-kv__k">{{ __('translate.Address') }}</span><span class="bd-kv__v">{{ $booking->customer_address ?? '—' }}</span></div>
            </div>
        </div>
        <div class="bd-card">
            <div class="bd-card__hdr">{{ __('translate.Booking Information') }}</div>
            <div class="bd-card__body">
                <div class="bd-kv"><span class="bd-kv__k">{{ __('translate.Invoice No') }}</span><span class="bd-kv__v">#{{ $booking->booking_code }}</span></div>
                <div class="bd-kv"><span class="bd-kv__k">{{ __('translate.Payment Method') }}</span><span class="bd-kv__v">{{ $booking->payment_method ? ucfirst($booking->payment_method) : '—' }}</span></div>
                <div class="bd-kv"><span class="bd-kv__k">{{ __('translate.Booking Date') }}</span><span class="bd-kv__v">{{ $booking->created_at ? $booking->created_at->format('d M Y, H:i') : '—' }}</span></div>
                <div class="bd-kv"><span class="bd-kv__k">{{ __('translate.Travel Date') }}</span><span class="bd-kv__v">{{ $travelDate ? \Carbon\Carbon::parse($travelDate)->format('d M Y') : '—' }}</span></div>
            </div>
        </div>
    </div>

    <div class="bd-card">
        <div class="bd-card__hdr">{{ __('translate.Service Information') }}</div>
        <div class="bd-card__body">
            <div class="bd-kv"><span class="bd-kv__k">{{ __('translate.Title') }}</span><span class="bd-kv__v">{{ $booking->service->title ?? '—' }}</span></div>
            <div class="bd-kv"><span class="bd-kv__k">{{ __('translate.Location') }}</span><span class="bd-kv__v">{{ $booking->service->location ?? '—' }}</span></div>
            @if (!empty($booking->pickup_point_id))
            <div class="bd-kv"><span class="bd-kv__k">{{ __('translate.Pickup Point') }}</span><span class="bd-kv__v">{{ $booking->pickup_point_name ?? 'Selected' }}</span></div>
            @endif
            <div class="bd-kv"><span class="bd-kv__k">{{ __('translate.Adults') }}</span><span class="bd-kv__v">{{ (int)$booking->adults }}</span></div>
            <div class="bd-kv"><span class="bd-kv__k">{{ __('translate.Children') }}</span><span class="bd-kv__v">{{ (int)$booking->children }}</span></div>
            @if(!empty($ageBreakdown))
            <div class="bd-chips">
                @foreach($ageBreakdown as $row)
                <span class="bd-chip">{{ $row['label'] ?? 'Category' }} · {{ (int)($row['qty'] ?? 0) }}</span>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>

@if (!empty($booking->customer_notes))
<div class="bd-note"><div class="bd-note__label">{{ __('translate.Your Notes') }}</div><div class="bd-note__text">{{ $booking->customer_notes }}</div></div>
@endif

@if ($booking->cancellation_reason)
<div class="bd-note"><div class="bd-note__label">{{ __('translate.Cancellation reason') }}</div><div class="bd-note__text">{{ $booking->cancellation_reason }}</div></div>
@endif

<div class="bd-card bd-grid-full">
    <div class="bd-card__hdr">{{ __('translate.Price Details') }}</div>
    <table class="bd-price-table">
        <thead><tr><th>{{ __('translate.Description') }}</th><th>{{ __('translate.Amount') }}</th></tr></thead>
        <tbody>
            @if(!empty($ageBreakdown))
                @foreach($ageBreakdown as $row)
                    @php $unit=(float)($row['price']??0); $qty=(int)($row['qty']??0); $line=(float)($row['line']??($unit*$qty)); @endphp
                    <tr><td>{{ $row['label'] ?? 'Category' }} ({{ number_format($unit,2) }} × {{ $qty }})</td><td>{{ currency($line) }}</td></tr>
                @endforeach
            @else
                @if ($booking->is_per_person == 1)
                    <tr><td>{{ __('translate.Adult Price') }} ({{ number_format((float)$booking->adult_price,2) }} × {{ (int)$booking->adults }})</td><td>{{ currency(((float)$booking->adult_price)*((int)$booking->adults)) }}</td></tr>
                    @if ((int)$booking->children > 0)
                    <tr><td>{{ __('translate.Child Price') }} ({{ number_format((float)$booking->child_price,2) }} × {{ (int)$booking->children }})</td><td>{{ currency(((float)$booking->child_price)*((int)$booking->children)) }}</td></tr>
                    @endif
                @else
                    <tr><td>{{ __('translate.Service Price') }}</td><td>{{ currency((float)$booking->service_price) }}</td></tr>
                @endif
            @endif

            @if(!empty($reconstructedExtras))
                @foreach($reconstructedExtras as $rExtra)
                <tr><td>{{ $rExtra['name'] }}</td><td>{{ currency($rExtra['amount']) }}</td></tr>
                @endforeach
            @elseif(isset($extra_services) && $extra_services->count() > 0)
                @foreach($extra_services as $extra)
                <tr><td>{{ $extra->name }} ({{ \Illuminate\Support\Str::title(str_replace('_', ' ', $extra->price_type)) }})</td><td>{{ currency($extra->price) }}</td></tr>
                @endforeach
            @elseif ((float)($booking->extra_charges ?? 0) != 0)
            <tr><td>{{ __('translate.Extra charges') }}</td><td>{{ currency((float)$booking->extra_charges) }}</td></tr>
            @endif

            @if ($pickupCharge > 0)
            <tr><td>{{ __('translate.Pickup Point') }}: {{ $booking->pickup_point_name ?? 'Pickup Service' }}</td><td>{{ currency($pickupCharge) }}</td></tr>
            @endif

            @if (!empty($booking->tax) && (float)$booking->tax > 0)
            <tr><td>{{ __('translate.Tax') }} @if(!empty($booking->tax_percentage))({{ (float)$booking->tax_percentage }}%)@endif</td><td>{{ currency((float)$booking->tax) }}</td></tr>
            @endif
        </tbody>
        <tfoot><tr><td>{{ __('translate.Total') }}</td><td>{{ currency((float)$booking->total) }}</td></tr></tfoot>
    </table>
</div>

            </div>
        </div></div></div></div>
    </div>
</section>

{{-- Cancel modal --}}
@if (!in_array(strtolower((string)$booking->booking_status), ['cancelled','completed']))
<div class="modal fade" id="cancelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">{{ __('translate.Cancel Booking') }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form action="{{ route('user.bookings.cancel', $booking->booking_code) }}" method="POST">
            @csrf
            <div class="modal-body">
                <p>{{ __('translate.Are you sure you want to cancel this booking?') }}</p>
                <div class="form-group">
                    <label>{{ __('translate.Cancellation Reason') }} *</label>
                    <textarea class="form-control" name="cancellation_reason" rows="3" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="bd-btn" data-bs-dismiss="modal">{{ __('translate.Close') }}</button>
                <button type="submit" class="bd-btn bd-btn--danger">{{ __('translate.Cancel Booking') }}</button>
            </div>
        </form>
    </div></div>
</div>
@endif

{{-- Review --}}
@if (strtolower((string)$booking->booking_status) === 'completed' && !$booking->is_reviewed)
<div class="bd-card bd-grid-full" style="margin-top:18px;">
    <div class="bd-card__hdr">{{ __('translate.Leave a Review') }}</div>
    <div class="bd-card__body">
        <form action="{{ route('user.bookings.review', $booking->booking_code) }}" method="POST">
            @csrf
            <div class="form-group mb-3">
                <label>{{ __('translate.Rating') }} *</label>
                <select class="form-control" name="rating" required>
                    <option value="">{{ __('translate.Select') }}</option>
                    @for($i = 5; $i >= 1; $i--)
                    <option value="{{ $i }}">{{ $i }} ⭐</option>
                    @endfor
                </select>
            </div>
            <div class="form-group mb-3">
                <label>{{ __('translate.Title') }} *</label>
                <input type="text" class="form-control" name="title" required maxlength="100">
            </div>
            <div class="form-group mb-3">
                <label>{{ __('translate.Review') }} *</label>
                <textarea class="form-control" name="review_text" rows="4" required minlength="10" maxlength="1000"></textarea>
            </div>
            <button type="submit" class="bd-btn bd-btn--primary">{{ __('translate.Submit Review') }}</button>
        </form>
    </div>
</div>
@endif

@endsection