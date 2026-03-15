{{-- resources/views/agency/bookings/index.blade.php --}}

@extends('agency.master_layout')

@section('title')
<title>{{ __('translate.Bookings list') }}</title>
@endsection

@section('body-header')
<div class="d-none d-md-block">
  <h3 class="crancy-header__title m-0">{{ __('translate.Bookings list') }}</h3>
  <p class="crancy-header__text">{{ __('translate.Bookings list') }} >> {{ __('translate.Bookings list') }}</p>
</div>
@endsection

@section('body-content')
<section class="crancy-adashboard crancy-show bookings-v2">
  <div class="container container__bscreen">

    <div class="d-md-none bookings-mobile-head">
      <div class="bookings-mobile-kicker">{{ __('translate.Dashboard') }}</div>
      <div class="bookings-mobile-title">{{ __('translate.Bookings') }}</div>
    </div>

    <div class="row">
      <div class="col-12">
        <div class="crancy-body">
          <div class="crancy-dsinner">
            <div class="crancy-table crancy-table--v3 mg-top-30 bookings-wrap">

              <div class="dash-section-head">
                <div>
                  <h4 class="dash-section-title">{{ __('translate.My bookings') }}</h4>
                  <p class="dash-section-sub">{{ __('translate.Search and filter your bookings.') }}</p>
                </div>
              </div>

              @include('tourbooking::components._booking_filters')

              {{-- Cards --}}
              <div class="booking-cards">
                @forelse ($bookings as $booking)
                  @php
                    $service = $booking?->service;
                    $thumb = $service?->thumbnail?->file_path ?? null;
                    $fallback = $service?->image ?? $service?->thumb_image ?? $service?->thumbnail_image ?? $service?->cover_image ?? $service?->banner_image ?? $service?->main_image ?? null;
                    $rawCover = $thumb ?: $fallback;
                    $coverUrl = null;
                    if (!empty($rawCover)) {
                        $rawCover = trim((string) $rawCover);
                        $coverUrl = \Illuminate\Support\Str::startsWith($rawCover, ['http://','https://']) ? $rawCover : asset($rawCover);
                    }
                    $title = $service?->translation?->title ?? $service?->title ?? 'Service';
                    $code = $booking->booking_code ?? 'N/A';
                    $total = currency((float)($booking->total ?? 0));
                    $location = $service?->location ?? 'N/A';
                    $status = (string)($booking->booking_status ?? 'status');
                    $bookingDate = $booking->created_at ? $booking->created_at->format('d M Y') : '—';
                    $detailsUrl = \Illuminate\Support\Facades\Route::has('agency.tourbooking.bookings.show')
                        ? route('agency.tourbooking.bookings.show', $booking->id)
                        : url('/agency/tourbooking/bookings/'.$booking->id);
                    $deleteAction = url('agency/tourbooking/bookings').'/'.$booking->id;
                  @endphp

                  <article class="booking-card">
                    <a class="booking-cover" href="{{ $detailsUrl }}">
                      @if($coverUrl)
                        <img src="{{ $coverUrl }}" alt="" loading="lazy" onerror="this.remove();">
                      @endif
                      <span class="booking-status">{{ $status }}</span>
                    </a>

                    <div class="booking-body">
                      <div class="booking-title">{{ $title }}</div>
                      <div class="booking-code">#{{ $code }}</div>

                      <div class="booking-meta">
                        <div class="booking-meta-row">
                          <span>{{ __('translate.Total Amount') }}</span>
                          <strong>{{ $total }}</strong>
                        </div>
                        <div class="booking-meta-row">
                          <span>{{ __('translate.Location') }}</span>
                          <strong>{{ $location }}</strong>
                        </div>
                        <div class="booking-meta-row">
                          <span>{{ __('translate.Booking Date') }}</span>
                          <strong>{{ $bookingDate }}</strong>
                        </div>
                      </div>

                      <div class="booking-cta-row">
                        <a class="booking-cta" href="{{ $detailsUrl }}">
                          <i class="fas fa-eye"></i>
                          <span>{{ __('translate.Details') }}</span>
                          <i class="fas fa-chevron-right"></i>
                        </a>
                        <button type="button" class="booking-cta booking-cta--danger"
                            onclick="itemDeleteConfrimation('{{ $deleteAction }}')"
                            data-bs-toggle="modal" data-bs-target="#exampleModal">
                          <i class="fas fa-trash"></i>
                          <span>{{ __('translate.Delete') }}</span>
                        </button>
                      </div>
                    </div>
                  </article>
                @empty
                  <div style="padding:24px 4px; font-weight:850; color:rgba(17,24,39,.55); text-align:center; grid-column:1/-1;">
                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                    {{ __('translate.No bookings found') }}
                  </div>
                @endforelse
              </div>

              @if($bookings->hasPages())
                  {{ $bookings->appends(request()->query())->links('vendor.pagination.custom') }}
              @endif

            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- Delete Confirmation Modal --}}
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">{{ __('translate.Delete Confirmation') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>{{ __('translate.Are you realy want to delete this item?') }}</p>
      </div>
      <div class="modal-footer">
        <form id="item_delect_confirmation" class="delet_modal_form" method="POST">
          @csrf
          @method('DELETE')
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('translate.Close') }}</button>
          <button type="submit" class="btn btn-primary">{{ __('translate.Yes, Delete') }}</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('style_section')
<style>
  .bookings-mobile-head { margin-top: 12px; margin-bottom: 10px; }
  .bookings-mobile-kicker { font-weight: 800; color: rgba(17,24,39,.55); font-size: 13px; }
  .bookings-mobile-title { font-size: 28px; font-weight: 950; line-height: 1.05; margin-top: 4px; color: rgba(17,24,39,.92); }
  .dash-section-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 16px; padding: 2px 4px 10px; }
  .dash-section-title { margin: 0; font-size: 18px; font-weight: 900; }
  .dash-section-sub { margin: 4px 0 0; color: rgba(18,25,38,.65); font-size: 13px; font-weight: 650; }

  @media (max-width:768px) {
    .bookings-v2 { padding-bottom: 110px !important; }
    .container__bscreen { padding-left: 16px; padding-right: 16px; }
  }

  .bookings-wrap { overflow: hidden; position: relative; }
  .booking-cards { margin-top: 10px; margin-bottom: 12px; }

  @media (max-width:768px) {
    .booking-cards { display: flex; gap: 14px; overflow-x: auto; padding: 2px 2px 6px; scroll-snap-type: x mandatory; -webkit-overflow-scrolling: touch; }
    .booking-cards::-webkit-scrollbar { height: 6px; }
    .booking-cards::-webkit-scrollbar-thumb { background: rgba(17,24,39,.12); border-radius: 999px; }
    .booking-card { flex: 0 0 86%; scroll-snap-align: start; }
  }

  @media (min-width:769px) { .booking-cards { display: grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap: 18px; } }
  @media (min-width:1200px) { .booking-cards { grid-template-columns: repeat(3, minmax(0,1fr)); } }

  .booking-card { background: #fff; border: 1px solid rgba(17,24,39,.08); border-radius: 18px; box-shadow: 0 10px 30px rgba(17,24,39,.06); overflow: hidden; }
  .booking-cover { display: block; position: relative; aspect-ratio: 16/9; background: linear-gradient(135deg, rgba(255,66,0,.10), rgba(17,24,39,.06)); }
  .booking-cover img { width: 100%; height: 100%; object-fit: cover; display: block; }
  .booking-status { position: absolute; top: 12px; right: 12px; padding: 8px 12px; border-radius: 999px; font-weight: 950; font-size: 12px; text-transform: lowercase; letter-spacing: .2px; background: rgba(16,185,129,.14); color: rgba(16,185,129,1); border: 1px solid rgba(16,185,129,.18); backdrop-filter: blur(6px); }
  .booking-body { padding: 14px 14px 12px; }
  .booking-title { font-size: 16px; font-weight: 950; line-height: 1.2; margin: 0; color: rgba(17,24,39,.92); }
  .booking-code { margin-top: 6px; font-weight: 850; color: rgba(17,24,39,.55); }
  .booking-meta { margin-top: 12px; border-top: 1px solid rgba(17,24,39,.08); padding-top: 10px; display: grid; gap: 8px; }
  .booking-meta-row { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; }
  .booking-meta-row span { font-size: 11px; font-weight: 950; letter-spacing: .5px; text-transform: uppercase; color: rgba(17,24,39,.55); }
  .booking-meta-row strong { font-size: 14px; font-weight: 950; color: rgba(17,24,39,.92); text-align: right; line-height: 1.2; }
  .booking-cta-row { margin-top: 12px; display: grid; grid-template-columns: 1fr; gap: 10px; }
  @media(min-width:420px) { .booking-cta-row { grid-template-columns: 1fr 1fr; } }
  .booking-cta { width: 100%; border-radius: 14px; padding: 12px 12px; display: flex; align-items: center; justify-content: center; gap: 10px; text-decoration: none !important; background: #0f1a23; color: #fff !important; font-weight: 950; cursor: pointer; border: none; }
  .booking-cta i.fa-chevron-right { font-size: 12px; opacity: .9; }
  .booking-cta--danger { border: 1px solid rgba(220,38,38,.18); background: rgba(220,38,38,.10); color: #b91c1c !important; }
</style>
@endpush

@push('js_section')
<script>
  "use strict";
  function itemDeleteConfrimation(action) {
    $("#item_delect_confirmation").attr("action", action);
  }
</script>
@endpush