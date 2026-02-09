@extends('agency.master_layout')

@section('title')
    <title>{{ __('translate.Dashboard') }}</title>
@endsection

@section('body-header')
    {{-- Desktop header (păstrăm header-ul existent pe desktop) --}}
    <div class="d-none d-md-block">
        <h3 class="crancy-header__title m-0">{{ __('translate.Dashboard') }}</h3>
        <p class="crancy-header__text">{{ __('translate.Dashboard') }} >> {{ __('translate.Dashboard') }}</p>
    </div>
@endsection

@php
    use Illuminate\Support\Facades\Route;

    // Safe URLs
    $newBookingUrl = Route::has('agency.tourbooking.bookings.create')
        ? route('agency.tourbooking.bookings.create')
        : url('/agency/tourbooking/bookings/create');

    $newClientUrl = Route::has('agency.tourbooking.clients.create')
        ? route('agency.tourbooking.clients.create')
        : url('/agency/tourbooking/clients/create');

    $clientsUrl = Route::has('agency.tourbooking.clients.index')
        ? route('agency.tourbooking.clients.index')
        : url('/agency/tourbooking/clients');

    $reportsUrl = Route::has('agency.tourbooking.reports.index')
        ? route('agency.tourbooking.reports.index')
        : url('/agency/tourbooking/reports');

    // Normalize chart data
    $purchaseData = $data ?? '[]';
    if (is_string($purchaseData)) $purchaseData = json_decode($purchaseData, true);
    if (!is_array($purchaseData)) $purchaseData = [];

    $dateLabel = $lable ?? '[]';
    if (is_string($dateLabel)) $dateLabel = json_decode($dateLabel, true);
    if (!is_array($dateLabel)) $dateLabel = [];

    $statusCountsSafe = $status_counts ?? ['pending'=>0,'confirmed'=>0,'completed'=>0,'cancelled'=>0];
    if (!is_array($statusCountsSafe)) {
        $statusCountsSafe = ['pending'=>0,'confirmed'=>0,'completed'=>0,'cancelled'=>0];
    }
@endphp

@push('style_section')
    <link rel="stylesheet" href="{{ asset('backend/css/charts.min.css') }}">

    <style>
        /* =========================
           Dashboard V2 – premium (Agency)
           ========================= */

        .dash-v2 .container__bscreen{ padding-left:24px; padding-right:24px; }
        @media (max-width:768px){ .dash-v2 .container__bscreen{ padding-left:16px; padding-right:16px; } }

        /* desktop KPI polish */
        .kpi-v2{ border-radius:18px; }
        .kpi-v2 .stat-label{ font-weight:800; letter-spacing:.2px; }
        .kpi-v2 .stat-value{ font-size:22px; font-weight:900; }

        .flex-main{ display:flex; align-items:center; gap:14px; }
        .stat-inline .stat-ico{
            width:44px; height:44px;
            border-radius:999px;
            display:inline-flex; align-items:center; justify-content:center;
            background: rgba(255,66,0,.10);
            color: #ff4200;
        }
        .stat-inline .stat-ico svg{ width:22px; height:22px; }

        /* section head */
        .dash-section-head{
            display:flex;
            align-items:flex-end;
            justify-content:space-between;
            gap:16px;
            padding: 2px 4px 10px;
        }
        .dash-section-title{ margin:0; font-size:18px; font-weight:900; }
        .dash-section-sub{
            margin:4px 0 0;
            color: rgba(18, 25, 38, .65);
            font-size:13px;
            font-weight:650;
        }

        /* mobile hero */
        .agency-hero{
            margin-top:8px;
            background:#0f1a23;
            border-radius:20px;
            padding:16px;
            color:#fff;
            box-shadow: 0 14px 40px rgba(15,26,35,.25);
        }
        .agency-hero h4{ margin:0; font-weight:950; font-size:18px; }
        .agency-hero p{ margin:6px 0 0; opacity:.85; font-weight:650; font-size:13px; }
        .hero-stats{
            display:grid;
            grid-template-columns: repeat(2, minmax(0,1fr));
            gap:10px;
            margin-top:14px;
        }
        .hero-stat{
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.10);
            border-radius:16px;
            padding:12px;
        }
        .hero-stat .k{ font-size:11px; font-weight:900; opacity:.8; letter-spacing:.5px; text-transform:uppercase; }
        .hero-stat .v{ margin-top:6px; font-size:18px; font-weight:950; }

        .hero-actions{
            margin-top:12px;
            display:grid;
            grid-template-columns: repeat(2, minmax(0,1fr));
            gap:10px;
        }
        .hero-btn{
            border-radius:14px;
            padding:12px;
            text-decoration:none !important;
            font-weight:950;
            text-align:center;
            background:#fff;
            color:#0f1a23 !important;
        }

        /* =========================
           DataTables controls – mobile inline minimal
           ========================= */
        @media (max-width: 768px){
            #dataTable_wrapper .row:first-child{
                display:flex !important;
                flex-wrap:nowrap !important;
                align-items:center !important;
                gap:10px !important;
                margin:0 !important;
            }
            #dataTable_wrapper .row:first-child > [class*="col-"]{
                padding-left:0 !important;
                padding-right:0 !important;
            }
            #dataTable_wrapper .dataTables_length{ flex:0 0 auto; }
            #dataTable_wrapper .dataTables_filter{ flex:1 1 auto; }

            #dataTable_wrapper .dataTables_length label,
            #dataTable_wrapper .dataTables_filter label{
                font-size:0 !important;
                margin:0 !important;
                display:flex !important;
                align-items:center !important;
                gap:10px !important;
                width:100% !important;
            }

            #dataTable_wrapper .dataTables_length select{
                font-size:14px !important;
                font-weight:800;
                border-radius:14px;
                padding:10px 12px;
                border:1px solid rgba(17,24,39,.10);
                background:#fff;
                min-width:86px;
                height:auto;
            }

            #dataTable_wrapper .dataTables_filter input{
                font-size:14px !important;
                font-weight:750;
                border-radius:14px;
                padding:12px 12px;
                border:1px solid rgba(17,24,39,.10);
                width:100% !important;
                max-width:100% !important;
            }

            #dataTable_wrapper .row:last-child{ margin-top:10px; }
        }

        /* =========================
           Booking cards (slider mobile, grid desktop)
           ========================= */
        .dash-bookings{ overflow:hidden; position:relative; }

        /* ascundem tabelul real (dar îl ținem în DOM pentru pagination/search) */
        .dash-bookings #dataTable{
            position:absolute !important;
            left:-9999px !important;
            top:-9999px !important;
            width:1px !important;
            height:1px !important;
            overflow:hidden !important;
        }

        .booking-cards{ margin-top:10px; margin-bottom:12px; }

        @media (max-width: 768px){
            .booking-cards{
                display:flex;
                gap:14px;
                overflow-x:auto;
                padding-bottom:6px;
                scroll-snap-type:x mandatory;
                -webkit-overflow-scrolling: touch;
            }
            .booking-card{ flex:0 0 86%; scroll-snap-align:start; }
        }
        @media (min-width: 769px){
            .booking-cards{
                display:grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap:18px;
            }
        }
        @media (min-width: 1200px){
            .booking-cards{ grid-template-columns: repeat(3, minmax(0, 1fr)); }
        }

        .booking-card{
            background:#fff;
            border:1px solid rgba(17,24,39,.08);
            border-radius:18px;
            box-shadow: 0 10px 30px rgba(17,24,39,.06);
            overflow:hidden;
        }

        .booking-cover{
            display:block;
            position:relative;
            aspect-ratio: 16/9;
            background: linear-gradient(135deg, rgba(255,66,0,.10), rgba(17,24,39,.06));
        }
        .booking-cover img{
            width:100%;
            height:100%;
            object-fit:cover;
            display:block;
        }

        .booking-status{
            position:absolute;
            top:12px;
            right:12px;
            padding:8px 12px;
            border-radius:999px;
            font-weight:900;
            font-size:12px;
            letter-spacing:.2px;
            border: 1px solid rgba(17,24,39,.12);
            background: rgba(17,24,39,.06);
            color: rgba(17,24,39,.85);
        }
        .booking-status.is-pending{ background: rgba(245,158,11,.14); color: rgba(245,158,11,1); border-color: rgba(245,158,11,.18); }
        .booking-status.is-confirmed{ background: rgba(59,130,246,.14); color: rgba(59,130,246,1); border-color: rgba(59,130,246,.18); }
        .booking-status.is-completed{ background: rgba(16,185,129,.14); color: rgba(16,185,129,1); border-color: rgba(16,185,129,.18); }
        .booking-status.is-cancelled{ background: rgba(239,68,68,.14); color: rgba(239,68,68,1); border-color: rgba(239,68,68,.18); }

        .booking-body{ padding:14px 14px 12px; }
        .booking-title{
            font-size:16px;
            font-weight:950;
            line-height:1.2;
            margin:0;
            color: rgba(17,24,39,.92);
        }
        .booking-code{
            margin-top:6px;
            font-weight:850;
            color: rgba(17,24,39,.55);
        }

        .booking-meta{
            margin-top:12px;
            border-top:1px solid rgba(17,24,39,.08);
            padding-top:10px;
            display:grid;
            gap:8px;
        }
        .booking-meta-row{
            display:flex;
            align-items:flex-start;
            justify-content:space-between;
            gap:10px;
        }
        .booking-meta-row span{
            font-size:11px;
            font-weight:950;
            letter-spacing:.5px;
            text-transform:uppercase;
            color: rgba(17,24,39,.55);
        }
        .booking-meta-row strong{
            font-size:14px;
            font-weight:950;
            color: rgba(17,24,39,.92);
            text-align:right;
            line-height:1.2;
        }

        .booking-cta{
            margin-top:12px;
            width:100%;
            border-radius:14px;
            padding:12px 12px;
            display:flex;
            align-items:center;
            justify-content:center;
            gap:10px;
            text-decoration:none !important;
            background:#0f1a23;
            color:#fff !important;
            font-weight:950;
        }
        .booking-cta i.fa-chevron-right{ font-size:12px; opacity:.9; }

        /* pagination polish */
        #dataTable_wrapper .pagination .page-link{ border-radius:12px !important; }
    </style>
@endpush

@section('body-content')
    <section class="crancy-adashboard crancy-show dash-v2">
        <div class="container container__bscreen">

            {{-- Mobile hero --}}
            <div class="d-md-none">
                <div class="agency-hero">
                    <h4>{{ __('translate.Dashboard') }}</h4>
                    <p>{{ __('translate.Manage bookings, clients and revenue from one place.') }}</p>

                    <div class="hero-stats">
                        <div class="hero-stat">
                            <div class="k">{{ __('translate.Total Earnings') }}</div>
                            <div class="v">{{ currency($total_income ?? 0) }}</div>
                        </div>
                        <div class="hero-stat">
                            <div class="k">{{ __('translate.Total Bookings') }}</div>
                            <div class="v">{{ (int)($total_bookings ?? 0) }}</div>
                        </div>
                        <div class="hero-stat">
                            <div class="k">{{ __('translate.Total Clients') }}</div>
                            <div class="v">{{ (int)($total_clients ?? 0) }}</div>
                        </div>
                        <div class="hero-stat">
                            <div class="k">{{ __('translate.Available Balance') }}</div>
                            <div class="v">{{ currency($current_balance ?? 0) }}</div>
                        </div>
                    </div>

                    <div class="hero-actions">
                        <a class="hero-btn" href="{{ $newClientUrl }}">+ {{ __('translate.Add Client') }}</a>
                        <a class="hero-btn" href="{{ $newBookingUrl }}">+ {{ __('translate.New Booking') }}</a>
                        <a class="hero-btn" href="{{ $clientsUrl }}">{{ __('translate.Clients') }}</a>
                        <a class="hero-btn" href="{{ $reportsUrl }}">{{ __('translate.Reports') }}</a>
                    </div>
                </div>
            </div>

            {{-- KPI cards (desktop) --}}
            <div class="row row__bscreen d-none d-md-flex">
                <div class="col-xxl-3 col-md-6 col-12 mg-top-30">
                    <div class="crancy-ecom-card crancy-ecom-card__v2 kpi-v2">
                        <div class="flex-main stat-inline">
                            <span class="stat-ico">
                                {{-- money icon --}}
                                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.6"/>
                                    <path d="M8 13c0 1 1.6 2 4 2s4-1 4-2-1.6-2-4-2-4-1-4-2 1.6-2 4-2 4 1 4 2"
                                          stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                    <path d="M12 6.5v11" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                </svg>
                            </span>
                            <div class="flex-1">
                                <div class="stat-label">{{ __('translate.Total Earnings') }}</div>
                                <div class="stat-value">{{ currency($total_income ?? 0) }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-3 col-md-6 col-12 mg-top-30">
                    <div class="crancy-ecom-card crancy-ecom-card__v2 kpi-v2">
                        <div class="flex-main stat-inline">
                            <span class="stat-ico">
                                {{-- net icon --}}
                                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M4 14l5-5 4 4 7-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M20 6v6h-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <div class="flex-1">
                                <div class="stat-label">{{ __('translate.Net Earnings') }}</div>
                                <div class="stat-value">{{ currency($net_income ?? 0) }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-3 col-md-6 col-12 mg-top-30">
                    <div class="crancy-ecom-card crancy-ecom-card__v2 kpi-v2">
                        <div class="flex-main stat-inline">
                            <span class="stat-ico">
                                {{-- bookings icon --}}
                                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M7 4h10M7 8h10M7 12h7" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                    <path d="M6 4h12v16H6V4Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <div class="flex-1">
                                <div class="stat-label">{{ __('translate.Total Bookings') }}</div>
                                <div class="stat-value">{{ (int)($total_bookings ?? 0) }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-3 col-md-6 col-12 mg-top-30">
                    <div class="crancy-ecom-card crancy-ecom-card__v2 kpi-v2">
                        <div class="flex-main stat-inline">
                            <span class="stat-ico">
                                {{-- clients icon --}}
                                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"
                                          stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                    <circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="1.6"/>
                                    <path d="M20 8v6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                    <path d="M23 11h-6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                </svg>
                            </span>
                            <div class="flex-1">
                                <div class="stat-label">{{ __('translate.Total Clients') }}</div>
                                <div class="stat-value">{{ (int)($total_clients ?? 0) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Charts + Top clients (same content, better header) --}}
            <div class="row crancy-gap-30 mg-top-30">
                <div class="col-lg-8 col-12">
                    <div class="charts-main charts-home-one">
                        <div class="dash-section-head">
                            <div>
                                <h4 class="dash-section-title">{{ __('translate.Paid Revenue This Month') }}</h4>
                                <p class="dash-section-sub">{{ __('translate.Track revenue trend day by day.') }}</p>
                            </div>
                        </div>

                        <div class="charts-main__one">
                            <div class="crancy-chart__inside crancy-chart__three" style="height:320px;">
                                <canvas id="myChart_recent_statics"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-12">
                    <div class="charts-main charts-home-one">
                        <div class="dash-section-head">
                            <div>
                                <h4 class="dash-section-title">{{ __('translate.Bookings by Status') }}</h4>
                                <p class="dash-section-sub">{{ __('translate.Quick view of pipeline health.') }}</p>
                            </div>
                        </div>

                        <div class="charts-main__one">
                            <div class="crancy-chart__inside" style="height:320px;">
                                <canvas id="bookingStatusChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Top clients (month) --}}
                <div class="col-12">
                    <div class="crancy-table crancy-table--v3 mg-top-30">
                        <div class="dash-section-head">
                            <div>
                                <h4 class="dash-section-title">{{ __('translate.Top Clients This Month') }}</h4>
                                <p class="dash-section-sub">{{ __('translate.Who brings the most revenue this month.') }}</p>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="crancy-table__main crancy-table__main-v3">
                                <thead class="crancy-table__head">
                                <tr>
                                    <th>{{ __('translate.Client') }}</th>
                                    <th>{{ __('translate.Bookings') }}</th>
                                    <th>{{ __('translate.Revenue') }}</th>
                                </tr>
                                </thead>
                                <tbody class="crancy-table__body">
                                @forelse(($top_clients ?? []) as $row)
                                    <tr>
                                        <td>{{ $row->agencyClient?->full_name ?? ('#'.$row->agency_client_id) }}</td>
                                        <td>{{ (int)($row->bookings_count ?? 0) }}</td>
                                        <td>{{ currency((float)($row->revenue ?? 0)) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3">{{ __('translate.No data') }}</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Latest bookings (cards + datatable hidden) --}}
            <div class="row">
                <div class="col-12">
                    <div class="crancy-body">
                        <div class="crancy-dsinner">
                            <div class="crancy-table crancy-table--v3 mg-top-30 dash-bookings">

                                <div class="dash-section-head">
                                    <div>
                                        <h4 class="dash-section-title">{{ __('translate.Latest Bookings') }}</h4>
                                        <p class="dash-section-sub">{{ __('translate.Search and open details in one tap.') }}</p>
                                    </div>

                                    {{-- Desktop quick actions --}}
                                    <div class="d-none d-md-flex gap-2 flex-wrap">
                                        <a href="{{ $newClientUrl }}" class="crancy-btn crancy-action__btn crancy-action__edit">+ {{ __('translate.Add Client') }}</a>
                                        <a href="{{ $newBookingUrl }}" class="crancy-btn crancy-action__btn crancy-action__edit">+ {{ __('translate.New Booking') }}</a>
                                        <a href="{{ $clientsUrl }}" class="crancy-btn crancy-action__btn crancy-action__edit">{{ __('translate.Clients') }}</a>
                                        <a href="{{ $reportsUrl }}" class="crancy-btn crancy-action__btn crancy-action__edit">{{ __('translate.Reports') }}</a>
                                    </div>
                                </div>

                                {{-- Cards container (mobile slider / desktop grid) --}}
                                <div id="bookingCards" class="booking-cards"></div>

                                {{-- DataTable stays for search/pagination/info (table hidden) --}}
                                <div id="crancy-table__main_wrapper" class="dt-bootstrap5 no-footer">
                                    <table class="crancy-table__main crancy-table__main-v3 no-footer" id="dataTable">
                                        <thead class="crancy-table__head">
                                        <tr>
                                            <th>{{ __('translate.Booking Code') }}</th>
                                            <th>{{ __('translate.Client') }}</th>
                                            <th>{{ __('translate.Service Title') }}</th>
                                            <th>{{ __('translate.Total') }}</th>
                                            <th>{{ __('translate.Status') }}</th>
                                            <th>{{ __('translate.Action') }}</th>
                                        </tr>
                                        </thead>

                                        <tbody class="crancy-table__body">
                                        @forelse(($bookings ?? []) as $booking)
                                            @php
                                                $service = $booking->service;

                                                $coverPath =
                                                    $service?->thumbnail?->file_path
                                                    ?? $service?->thumbnail?->path
                                                    ?? $service?->thumbnail?->url
                                                    ?? $service?->thumbnail_image
                                                    ?? $service?->cover_image
                                                    ?? $service?->image
                                                    ?? $service?->main_image
                                                    ?? null;

                                                $coverUrl = null;
                                                if (!empty($coverPath)) {
                                                    $coverPath = trim((string) $coverPath);
                                                    $coverUrl = \Illuminate\Support\Str::startsWith($coverPath, ['http://', 'https://'])
                                                        ? $coverPath
                                                        : asset($coverPath);
                                                }

                                                $code   = $booking->booking_code ?? 'N/A';
                                                $client = $booking->agencyClient?->full_name ?? $booking->customer_name ?? 'N/A';
                                                $title  = $service?->translation?->title ?? $service?->title ?? ($booking?->service?->title ?? 'Service');
                                                $total  = currency((float)($booking->total ?? 0));
                                                $status = (string)($booking->booking_status ?? 'status');

                                                $detailsUrl = Route::has('agency.tourbooking.bookings.show')
                                                    ? route('agency.tourbooking.bookings.show', $booking->id)
                                                    : url('/agency/tourbooking/bookings/'.$booking->id);
                                            @endphp

                                            <tr
                                                data-title="{{ e($title) }}"
                                                data-code="{{ e($code) }}"
                                                data-client="{{ e($client) }}"
                                                data-total="{{ e($total) }}"
                                                data-status="{{ e($status) }}"
                                                data-href="{{ e($detailsUrl) }}"
                                                data-cover="{{ e($coverUrl ?? '') }}"
                                            >
                                                <td>#{{ $code }}</td>
                                                <td>{{ \Illuminate\Support\Str::limit($client, 40) }}</td>
                                                <td>{{ \Illuminate\Support\Str::limit($title, 50) }}</td>
                                                <td>{{ $total }}</td>
                                                <td>{{ $status }}</td>
                                                <td><a href="{{ $detailsUrl }}">{{ __('translate.Details') }}</a></td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6">{{ __('translate.No data') }}</td>
                                            </tr>
                                        @endforelse
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
@endsection

@push('js_section')
    <script src="{{ asset('backend/js/charts.js') }}"></script>

    <script>
        "use strict";

        /** -------- Chart 1: Paid Revenue This Month (Line) -------- */
        const purchase_data = @json($purchaseData);
        const date_lable    = @json($dateLabel);

        const lineEl = document.getElementById('myChart_recent_statics');
        if (lineEl) {
            const ctxLine = lineEl.getContext('2d');

            const gradientBgs = ctxLine.createLinearGradient(400, 100, 100, 400);
            gradientBgs.addColorStop(0, 'rgba(100, 64, 251, 0.10)');
            gradientBgs.addColorStop(1, 'rgba(100, 64, 251, 0.45)');

            new Chart(ctxLine, {
                type: 'line',
                data: {
                    labels: date_lable,
                    datasets: [{
                        label: 'Revenue',
                        data: purchase_data,
                        backgroundColor: gradientBgs,
                        borderColor: '#6440FBFF',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true,
                        pointRadius: 2,
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    scales: {
                        x: { grid: { display: false, drawBorder: false } },
                        y: { grid: { drawBorder: false, color: '#D7DCE7', borderDash: [5, 5] } }
                    },
                    plugins: { legend: { display: false } }
                }
            });
        }

        /** -------- Chart 2: Bookings by Status (Doughnut) -------- */
        const statusCounts = @json($statusCountsSafe);

        const doughnutEl = document.getElementById('bookingStatusChart');
        if (doughnutEl) {
            const ctxStatus = doughnutEl.getContext('2d');

            new Chart(ctxStatus, {
                type: 'doughnut',
                data: {
                    labels: ['Pending', 'Confirmed', 'Completed', 'Cancelled'],
                    datasets: [{
                        data: [
                            Number(statusCounts.pending ?? 0),
                            Number(statusCounts.confirmed ?? 0),
                            Number(statusCounts.completed ?? 0),
                            Number(statusCounts.cancelled ?? 0),
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    cutout: '68%',
                    plugins: { legend: { position: 'bottom' } }
                }
            });
        }
    </script>

    <script>
        (function($){
            "use strict";

            function escapeHtml(str){
                return $('<div/>').text(str ?? '').html();
            }
            function statusKey(s){
                return (s ?? '').toString().trim().toLowerCase();
            }
            function statusClass(s){
                const k = statusKey(s);
                if(k.includes('pend')) return 'is-pending';
                if(k.includes('conf')) return 'is-confirmed';
                if(k.includes('comp')) return 'is-completed';
                if(k.includes('canc')) return 'is-cancelled';
                return '';
            }

            function renderCardsFromDataTable(dt){
                const $wrap = $('#bookingCards');
                if(!$wrap.length) return;

                const rows = dt.rows({ page: 'current', search: 'applied' }).nodes().toArray();

                if(!rows.length){
                    $wrap.html('<div style="padding:12px 4px; font-weight:800; color:rgba(17,24,39,.55)">{{ __('translate.No data') }}</div>');
                    return;
                }

                const html = rows.map(function(tr){
                    const $tr = $(tr);

                    const title  = $tr.data('title') || '';
                    const code   = $tr.data('code') || '';
                    const client = $tr.data('client') || '';
                    const total  = $tr.data('total') || '';
                    const status = $tr.data('status') || '';
                    const href   = $tr.data('href') || '#';
                    const cover  = $tr.data('cover') || '';

                    const stText = escapeHtml(statusKey(status) || 'status');
                    const stCls  = statusClass(status);

                    const coverHtml = cover
                        ? `<img src="${escapeHtml(cover)}" alt="" loading="lazy" onerror="this.remove();">`
                        : '';

                    return `
                        <article class="booking-card">
                            <a class="booking-cover" href="${escapeHtml(href)}">
                                ${coverHtml}
                                <span class="booking-status ${stCls}">${stText}</span>
                            </a>

                            <div class="booking-body">
                                <div class="booking-title">${escapeHtml(title)}</div>
                                <div class="booking-code">#${escapeHtml(code)}</div>

                                <div class="booking-meta">
                                    <div class="booking-meta-row">
                                        <span>{{ __('translate.Client') }}</span>
                                        <strong>${escapeHtml(client)}</strong>
                                    </div>
                                    <div class="booking-meta-row">
                                        <span>{{ __('translate.Total') }}</span>
                                        <strong>${escapeHtml(total)}</strong>
                                    </div>
                                </div>

                                <a class="booking-cta" href="${escapeHtml(href)}">
                                    <i class="fas fa-eye"></i>
                                    <span>{{ __('translate.Details') }}</span>
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </div>
                        </article>
                    `;
                }).join('');

                $wrap.html(html);
            }

            $(document).ready(function(){
                if(!$('#dataTable').length) return;

                const dt = $.fn.dataTable.isDataTable('#dataTable')
                    ? $('#dataTable').DataTable()
                    : $('#dataTable').DataTable();

                const $search = $('#dataTable_wrapper .dataTables_filter input');
                if($search.length){
                    $search.attr('placeholder', 'Search bookings...');
                }

                renderCardsFromDataTable(dt);
                dt.on('draw', function(){ renderCardsFromDataTable(dt); });
            });

        })(jQuery);
    </script>
@endpush