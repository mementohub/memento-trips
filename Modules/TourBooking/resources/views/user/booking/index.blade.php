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
                                        <p class="dash-section-sub">Search and filter your bookings.</p>
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
                                            $title = $service?->title ?? 'Service';
                                            $code = $booking->booking_code ?? 'N/A';
                                            $total = currency($booking->total);
                                            $location = $service?->location ?? 'N/A';
                                            $status = (string) ($booking->booking_status ?? 'status');
                                            $href = route('user.bookings.details', ['id' => $booking->id]);
                                        @endphp

                                        <article class="booking-card">
                                            <a class="booking-cover" href="{{ $href }}">
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
                                                        <span>Total</span>
                                                        <strong>{{ $total }}</strong>
                                                    </div>
                                                    <div class="booking-meta-row">
                                                        <span>Location</span>
                                                        <strong>{{ $location }}</strong>
                                                    </div>
                                                </div>

                                                <a class="booking-cta" href="{{ $href }}">
                                                    <i class="fas fa-eye"></i>
                                                    <span>View details</span>
                                                    <i class="fas fa-chevron-right"></i>
                                                </a>
                                            </div>
                                        </article>
                                    @empty
                                        <div style="padding:24px 4px; font-weight:850; color:rgba(17,24,39,.55); text-align:center; grid-column:1/-1;">
                                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                            No bookings found.
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
@endsection

@push('style_section')
<style>
.bookings-mobile-head { margin-top: 12px; margin-bottom: 10px; }
.bookings-mobile-kicker { font-weight: 800; color: rgba(17,24,39,.55); font-size: 13px; }
.bookings-mobile-title { font-size: 28px; font-weight: 950; line-height: 1.05; margin-top: 4px; color: rgba(17,24,39,.92); }
.dash-section-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 16px; padding: 2px 4px 10px; }
.dash-section-title { margin: 0; font-size: 18px; font-weight: 900; }
.dash-section-sub { margin: 4px 0 0; color: rgba(18,25,38,.65); font-size: 13px; font-weight: 650; }

@media (max-width: 768px) {
  .bookings-v2 { padding-bottom: 110px !important; }
  .container__bscreen { padding-left: 16px; padding-right: 16px; }
}

.bookings-wrap { overflow: hidden; position: relative; }
.booking-cards { margin-top: 10px; margin-bottom: 12px; }

@media (max-width: 768px) {
  .booking-cards { display: flex; gap: 14px; overflow-x: auto; padding: 2px 2px 6px; scroll-snap-type: x mandatory; -webkit-overflow-scrolling: touch; }
  .booking-cards::-webkit-scrollbar { height: 6px; }
  .booking-cards::-webkit-scrollbar-thumb { background: rgba(17,24,39,.12); border-radius: 999px; }
  .booking-card { flex: 0 0 86%; scroll-snap-align: start; }
}

@media (min-width: 769px) { .booking-cards { display: grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap: 18px; } }
@media (min-width: 1200px) { .booking-cards { grid-template-columns: repeat(3, minmax(0,1fr)); } }

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
.booking-cta { margin-top: 12px; width: 100%; border-radius: 14px; padding: 12px 12px; display: flex; align-items: center; justify-content: center; gap: 10px; text-decoration: none !important; background: #0f1a23; color: #fff !important; font-weight: 950; }
.booking-cta i.fa-chevron-right { font-size: 12px; opacity: .9; }
</style>
@endpush