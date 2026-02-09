@extends('user.master_layout')

@section('title')
    <title>Bookings</title>
@endsection

@section('body-header')
   
    <div class="d-none d-md-block">
        <h3 class="crancy-header__title m-0">Bookings</h3>
        <p class="crancy-header__text">Bookings >> Bookings</p>
    </div>
@endsection

@section('body-content')
    <section class="crancy-adashboard crancy-show bookings-v2">
        <div class="container container__bscreen">

            
            <div class="d-md-none bookings-mobile-head">
                <div class="bookings-mobile-kicker">My account</div>
                <div class="bookings-mobile-title">Bookings</div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="crancy-body">
                        <div class="crancy-dsinner">
                            <div class="crancy-table crancy-table--v3 mg-top-30 bookings-wrap">

                                <div class="dash-section-head">
                                    <div>
                                        <h4 class="dash-section-title">My bookings</h4>
                                        <p class="dash-section-sub">Search and open details in one tap.</p>
                                    </div>
                                </div>

                                {{-- Cards container (DataTables -> cards render) --}}
                                <div id="bookingCards" class="booking-cards"></div>

                                {{-- Keep DataTable for search + pagination (table hidden via CSS) --}}
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
                                                    $service = $booking?->service;

                                                    
                                                    $thumb = $service?->thumbnail?->file_path ?? null;

                                                    
                                                    $fallback =
                                                        $service?->image
                                                        ?? $service?->thumb_image
                                                        ?? $service?->thumbnail_image
                                                        ?? $service?->cover_image
                                                        ?? $service?->banner_image
                                                        ?? $service?->main_image
                                                        ?? null;

                                                    $rawCover = $thumb ?: $fallback;

                                                    $coverUrl = null;
                                                    if (!empty($rawCover)) {
                                                        $rawCover = trim((string) $rawCover);
                                                        $coverUrl = \Illuminate\Support\Str::startsWith($rawCover, ['http://','https://'])
                                                            ? $rawCover
                                                            : asset($rawCover);
                                                    }

                                                    $title = $service?->title ?? 'Service';
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
                                {{-- /datatable wrapper --}}

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

.bookings-mobile-head{
  margin-top:12px;
  margin-bottom:10px;
}
.bookings-mobile-kicker{
  font-weight:800;
  color: rgba(17,24,39,.55);
  font-size:13px;
}
.bookings-mobile-title{
  font-size:28px;
  font-weight:950;
  line-height:1.05;
  margin-top:4px;
  color: rgba(17,24,39,.92);
}

/* section head */
.dash-section-head{
  display:flex;
  align-items:flex-end;
  justify-content:space-between;
  gap:16px;
  padding: 2px 4px 10px;
}
.dash-section-title{
  margin:0;
  font-size:18px;
  font-weight:900;
}
.dash-section-sub{
  margin:4px 0 0;
  color: rgba(18, 25, 38, .65);
  font-size:13px;
  font-weight:650;
}


@media (max-width: 768px){
  .bookings-v2{
    padding-bottom: 110px !important;
  }
  .container__bscreen{
    padding-left:16px;
    padding-right:16px;
  }
}

/* =========================
   DataTables controls (mobile minimal inline)
   ========================= */
@media (max-width: 768px){
  #dataTable_wrapper .row:first-child{
    align-items:center;
    gap:10px;
    margin: 0 !important;
  }
  #dataTable_wrapper .row:first-child > [class*="col-"]{
    padding-left:0;
    padding-right:0;
  }

  /* label text hidden, keep controls */
  #dataTable_wrapper .dataTables_length label,
  #dataTable_wrapper .dataTables_filter label{
    font-size:0 !important;
    margin:0 !important;
    display:flex;
    align-items:center;
    gap:10px;
    width:100%;
  }

  #dataTable_wrapper .dataTables_length{
    width: 120px !important;
    flex: 0 0 auto !important;
  }
  #dataTable_wrapper .dataTables_filter{
    flex: 1 1 auto !important;
  }

  #dataTable_wrapper .dataTables_length select{
    font-size:14px !important;
    font-weight:850;
    border-radius:14px;
    padding:10px 12px;
    border:1px solid rgba(17,24,39,.10);
    background:#fff;
    width:100%;
  }

  #dataTable_wrapper .dataTables_filter input{
    font-size:14px !important;
    font-weight:750;
    border-radius:14px;
    padding:12px 12px;
    border:1px solid rgba(17,24,39,.10);
    width:100% !important;
  }

  #dataTable_wrapper .row:last-child{
    margin-top:10px;
  }
}

/* =========================
   Booking cards (slider mobile, grid desktop)
   ========================= */
.bookings-wrap{
  overflow:hidden;
  position: relative;
}

/* hide real table (we render cards) */
.bookings-wrap #dataTable{
  position:absolute !important;
  left:-9999px !important;
  top:-9999px !important;
  width:1px !important;
  height:1px !important;
  overflow:hidden !important;
}

/* cards container */
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
    padding: 2px 2px 6px;
    scroll-snap-type:x mandatory;
    -webkit-overflow-scrolling: touch;
  }
  .booking-cards::-webkit-scrollbar{ height:6px; }
  .booking-cards::-webkit-scrollbar-thumb{ background: rgba(17,24,39,.12); border-radius:999px; }
  .booking-card{
    flex:0 0 86%;
    scroll-snap-align:start;
  }
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
  .booking-cards{
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
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

/* status pill on cover */
.booking-status{
  position:absolute;
  top:12px;
  right:12px;
  padding:8px 12px;
  border-radius:999px;
  font-weight:950;
  font-size:12px;
  text-transform:lowercase;
  letter-spacing:.2px;
  background: rgba(16,185,129,.14);
  color: rgba(16,185,129,1);
  border: 1px solid rgba(16,185,129,.18);
  backdrop-filter: blur(6px);
}

/* body */
.booking-body{
  padding:14px 14px 12px;
}
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

/* meta rows */
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

/* CTA */
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
.booking-cta i.fa-chevron-right{
  font-size:12px;
  opacity:.9;
}

/* pagination polish */
#dataTable_wrapper .pagination .page-link{
  border-radius:12px !important;
}
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
      $wrap.html('<div style="padding:12px 4px; font-weight:850; color:rgba(17,24,39,.55)">No bookings found.</div>');
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