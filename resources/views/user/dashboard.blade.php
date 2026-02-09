@extends('user.master_layout')

@section('title')
    <title>Dashboard</title>
@endsection

@section('body-header')
   
    <div class="d-none d-md-block">
        <h3 class="crancy-header__title m-0">Dashboard</h3>
        <p class="crancy-header__text">Dashboard >> Dashboard</p>
    </div>
@endsection

@section('body-content')
    <section class="crancy-adashboard crancy-show dash-v2">
        <div class="container container__bscreen">

            {{-- Mobile hero --}}
            <div class="d-md-none">
                @include('user.partials.mobile_dashboard_hero', [
                    'total_booking' => $total_booking,
                    'total_transaction' => $total_transaction,
                    'support_tickets' => $support_tickets,
                    'wishlists' => $wishlists,
                ])
            </div>

            {{-- KPI cards (desktop) --}}
            <div class="row row__bscreen d-none d-md-flex">
                <div class="col-xxl-3 col-md-6 col-12 mg-top-30">
                    <div class="crancy-ecom-card crancy-ecom-card__v2 kpi-v2">
                        <div class="flex-main stat-inline">
                            <span class="stat-ico">@include('svg.total_earning')</span>
                            <div class="flex-1">
                                <div class="stat-label">Total bookings</div>
                                <div class="stat-value">{{ $total_booking }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-3 col-md-6 col-12 mg-top-30">
                    <div class="crancy-ecom-card crancy-ecom-card__v2 kpi-v2">
                        <div class="flex-main stat-inline">
                            <span class="stat-ico">@include('svg.available_balance')</span>
                            <div class="flex-1">
                                <div class="stat-label">Transactions</div>
                                <div class="stat-value">{{ currency($total_transaction) }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-3 col-md-6 col-12 mg-top-30">
                    <div class="crancy-ecom-card crancy-ecom-card__v2 kpi-v2">
                        <div class="flex-main stat-inline">
                            <span class="stat-ico">@include('svg.net_earning')</span>
                            <div class="flex-1">
                                <div class="stat-label">Support tickets</div>
                                <div class="stat-value">{{ $support_tickets }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-3 col-md-6 col-12 mg-top-30">
                    <div class="crancy-ecom-card crancy-ecom-card__v2 kpi-v2">
                        <div class="flex-main stat-inline">
                            <span class="stat-ico">@include('svg.wishlist_icon')</span>
                            <div class="flex-1">
                                <div class="stat-label">Wishlist</div>
                                <div class="stat-value">{{ $wishlists }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Latest bookings --}}
            <div class="row">
                <div class="col-12">
                    <div class="crancy-body">
                        <div class="crancy-dsinner">
                            <div class="crancy-table crancy-table--v3 mg-top-30 dash-bookings">

                                <div class="dash-section-head">
                                    <div>
                                        <h4 class="dash-section-title">Latest bookings</h4>
                                        <p class="dash-section-sub">Search and open details in one tap.</p>
                                    </div>
                                </div>

                                {{-- Cards container (mobile slider / desktop grid) --}}
                                <div id="bookingCards" class="booking-cards"></div>

                                
                                <div id="crancy-table__main_wrapper" class="dt-bootstrap5 no-footer">
                                    <table class="crancy-table__main crancy-table__main-v3 no-footer" id="dataTable">
                                        <thead class="crancy-table__head">
                                            <tr>
                                                <th>Booking Code</th>
                                                <th>Service</th>
                                                <th>Total</th>
                                                <th>Location</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>

                                        <tbody class="crancy-table__body">
                                            @foreach ($bookings as $booking)
                                                @php
                                                    $service = $booking->service;

                                                    
                                                    // <img src="{{ asset($service?->thumbnail?->file_path) }}">
                                                    $coverPath =
                                                        $service?->thumbnail?->file_path
                                                        ?? $service?->thumbnail?->path
                                                        ?? $service?->thumbnail?->url
                                                        // fallback-uri (dacă există și câmpuri "flat")
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

                                                    $title = $service?->translation?->title ?? $service?->title ?? 'Service';
                                                    $code = $booking->booking_code ?? 'N/A';
                                                    $total = currency($booking->total);
                                                    $location = $service?->location ?? 'N/A';
                                                    $status = (string) ($booking->booking_status ?? 'status');
                                                    $href = route('user.bookings.details', ['id' => $booking->id]);
                                                @endphp

                                                <tr
                                                    data-title="{{ e($title) }}"
                                                    data-code="{{ e($code) }}"
                                                    data-total="{{ e($total) }}"
                                                    data-location="{{ e($location) }}"
                                                    data-status="{{ e($status) }}"
                                                    data-href="{{ e($href) }}"
                                                    data-cover="{{ e($coverUrl ?? '') }}"
                                                >
                                                    <td>#{{ $code }}</td>
                                                    <td>{{ \Illuminate\Support\Str::limit($title, 60) }}</td>
                                                    <td>{{ $total }}</td>
                                                    <td>{{ \Illuminate\Support\Str::limit($location, 60) }}</td>
                                                    <td>{{ $status }}</td>
                                                    <td><a href="{{ $href }}">View details</a></td>
                                                </tr>
                                            @endforeach
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

@push('style_section')
<style>


/* desktop KPI polish */
.kpi-v2{ border-radius:18px; }
.kpi-v2 .stat-label{ font-weight:800; letter-spacing:.2px; }
.kpi-v2 .stat-value{ font-size:22px; font-weight:900; }

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

/* =========================
   DataTables controls – mobile inline minimal
   ========================= */
@media (max-width: 768px){
  .container__bscreen{ padding-left:16px; padding-right:16px; }

  /* primul row din wrapper: show entries + search */
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


.dash-bookings #dataTable{
  position:absolute !important;
  left:-9999px !important;
  top:-9999px !important;
  width:1px !important;
  height:1px !important;
  overflow:hidden !important;
}

/* wrapper cards */
.booking-cards{
  margin-top:10px;
  margin-bottom:12px;
}

/* mobile slider */
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

/* desktop grid */
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

/* cover */
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

/* status pill */
.booking-status{
  position:absolute;
  top:12px;
  right:12px;
  padding:8px 12px;
  border-radius:999px;
  font-weight:900;
  font-size:12px;
  letter-spacing:.2px;
  background: rgba(16,185,129,.14);
  color: rgba(16,185,129,1);
  border: 1px solid rgba(16,185,129,.18);
}

/* body */
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

/* meta */
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

/* CTA – compact, “app button” */
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

@push('js_section')
<script>
(function($){
  "use strict";

  function escapeHtml(str){
    return $('<div/>').text(str ?? '').html();
  }
  function statusNormalize(s){
    return (s ?? '').toString().trim().toLowerCase();
  }

  function renderCardsFromDataTable(dt){
    const $wrap = $('#bookingCards');
    if(!$wrap.length) return;

    const rows = dt.rows({ page: 'current', search: 'applied' }).nodes().toArray();

    if(!rows.length){
      $wrap.html('<div style="padding:12px 4px; font-weight:800; color:rgba(17,24,39,.55)">No bookings found.</div>');
      return;
    }

    const html = rows.map(function(tr){
      const $tr = $(tr);

      const title = $tr.data('title') || '';
      const code = $tr.data('code') || '';
      const total = $tr.data('total') || '';
      const location = $tr.data('location') || '';
      const status = $tr.data('status') || '';
      const href = $tr.data('href') || '#';
      const cover = $tr.data('cover') || '';

      const statusText = escapeHtml(statusNormalize(status) || 'status');

      const coverHtml = cover
        ? `<img src="${escapeHtml(cover)}" alt="" loading="lazy" onerror="this.remove();">`
        : '';

      return `
        <article class="booking-card">
          <a class="booking-cover" href="${escapeHtml(href)}">
            ${coverHtml}
            <span class="booking-status">${statusText}</span>
          </a>

          <div class="booking-body">
            <div class="booking-title">${escapeHtml(title)}</div>
            <div class="booking-code">#${escapeHtml(code)}</div>

            <div class="booking-meta">
              <div class="booking-meta-row">
                <span>Total</span>
                <strong>${escapeHtml(total)}</strong>
              </div>
              <div class="booking-meta-row">
                <span>Location</span>
                <strong>${escapeHtml(location)}</strong>
              </div>
            </div>

            <a class="booking-cta" href="${escapeHtml(href)}">
              <i class="fas fa-eye"></i>
              <span>View details</span>
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