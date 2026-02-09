@php
    use Modules\TourBooking\App\Models\Availability;
    use Carbon\Carbon;

    $selectedDateRaw = request('checkIn');
    $selectedDateSQL = null;

    if ($selectedDateRaw) {
        try {
            // Convertim din MM/DD/YYYY → YYYY-MM-DD
            $selectedDateSQL = Carbon::createFromFormat('m/d/Y', $selectedDateRaw)->format('Y-m-d');
        } catch (\Exception $e) {
            $selectedDateSQL = null;
        }
    }
@endphp

@if ($allServices->count() > 0)
    <div class="tg-listing-grid-item">
        <div @class(['row list-card', 'list-card-open' => $isListView == 'true'])>
            @foreach ($allServices as $service)
                @php
                   
                    $query = request()->only(['checkIn', 'checkOut', 'check_in_check_out']);

                    $availability = null;
                    $available = true;

                    
                    $tripAdultPrice = null;

                    
                    $cats = [];

                    if (!empty($service->age_categories)) {
                        if (is_array($service->age_categories)) {
                            $cats = $service->age_categories;
                        } elseif (is_string($service->age_categories)) {
                            $decoded = json_decode($service->age_categories, true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                $cats = $decoded;
                            }
                        }
                    }

                    if (!empty($cats)) {
                        foreach ($cats as $catKey => $cat) {
                            // cheia 'adult', 'child', etc are prioritate
                            if (is_string($catKey) && $catKey !== '') {
                                $identifier = strtolower($catKey);
                            } else {
                                $identifier = strtolower($cat['slug'] ?? $cat['key'] ?? $cat['name'] ?? '');
                            }

                            if ($identifier === 'adult') {
                                // în form-ul de creare, câmpul e "price" (pe persoană)
                                if (isset($cat['price']) && $cat['price'] !== null && $cat['price'] !== '') {
                                    $tripAdultPrice = (float) $cat['price'];
                                    break;
                                }

                                
                                if (isset($cat['price_per_person']) && $cat['price_per_person'] !== null && $cat['price_per_person'] !== '') {
                                    $tripAdultPrice = (float) $cat['price_per_person'];
                                    break;
                                }
                            }
                        }
                    }

                    
                    if ($tripAdultPrice === null && $service->price_per_person !== null) {
                        $tripAdultPrice = (float) $service->price_per_person;
                    }

                    
                    $availabilityAdultPrice = null;

                    if ($selectedDateSQL) {
                        $availability = Availability::where('service_id', $service->id)
                            ->whereDate('date', $selectedDateSQL)
                            ->where('is_available', 1)
                            ->first();

                        if ($availability) {
                            
                            $availabilityAdultPrice = $availability->priceForCategory('adult');
                        } else {
                            
                            $available = false;
                        }
                    }

                    $disabledStyle = !$available ? 'pointer-events:none;opacity:0.5;filter:grayscale(0.6);' : '';
                @endphp

                <div class="col-xxl-4 col-xl-6 col-lg-6 col-md-6 tg-grid-full">
                    <div class="tg-listing-card-item tg-listing-4-card-item mb-25" style="{{ $disabledStyle }}">
                        <div class="tg-listing-card-thumb tg-listing-2-card-thumb mb-15 fix p-relative">
                           <a href="{{ $available ? route('front.tourbooking.services.show', ['slug' => $service->slug] + $query) : 'javascript:void(0);' }}">
                                <img class="tg-card-border w-100"
                                     src="{{ asset($service?->thumbnail?->file_path) }}"
                                     alt="{{ $service?->thumbnail?->caption ?? $service?->translation?->title }}">

                                @if ($service?->is_new == 1)
                                    <span class="tg-listing-item-price-discount shape"
                                          style="background-image: url('{{ asset('frontend/assets/img/shape/price-shape-2.png') }}')">New</span>
                                @endif

                                @if ($service?->is_featured == 1)
                                    <span class="tg-listing-item-price-discount shape-3"
                                          style="background-image: url('{{ asset('frontend/assets/img/shape/featured.png') }}')">
                                        <svg width="12" height="14" viewBox="0 0 12 14" fill="none"
                                             xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M6.60156 1L0.601562 8.2H6.00156L5.40156 13L11.4016 5.8H6.00156L6.60156 1Z"
                                                stroke="white" stroke-width="0.857143" stroke-linecap="round"
                                                stroke-linejoin="round"></path>
                                        </svg>
                                        Featured
                                    </span>
                                @endif

                                @if ($service?->discount_price)
                                    <span class="tg-listing-item-price-discount offer-btm shape-2"
                                          style="background-image: url('{{ asset('frontend/assets/img/shape/offter.png') }}')">Sale Offer</span>
                                @endif
                            </a>

                           
                            <div class="tg-listing-2-price d-flex align-items-center gap-1 px-2 py-1 rounded"
                                 style="background-color:#ff4200;color:#fff;font-weight:600;font-size:15px;">
                                @if(!$available)
                                    
                                    <small style="color:#fff;opacity:0.9;">No availability for selected date</small>
                                @else
                                    @php
                                        $basePrice = $tripAdultPrice !== null ? (float) $tripAdultPrice : 0.0; // TRIP
                                        $discountPrice = $availabilityAdultPrice !== null && $availabilityAdultPrice !== ''
                                            ? (float) $availabilityAdultPrice
                                            : null;
                                    @endphp

                                    @if($basePrice <= 0 && $discountPrice === null)
                                        
                                        <small style="color:#fff;opacity:0.9;">Contact us for price</small>
                                    @else
                                        <i class="fa-regular fa-user" style="color:#fff;"></i>

                                        @if($discountPrice !== null && $discountPrice > 0)
                                            @if($basePrice > 0 && $discountPrice < $basePrice)
                                                
                                                <span style="text-decoration:line-through;color:#fff;font-weight:400;margin-right:6px;font-size:14px;opacity:0.7;">
                                                    {{ number_format($basePrice, 2) }} €
                                                </span>
                                                <span style="font-size:15px;">
                                                    {{ number_format($discountPrice, 2) }} €
                                                </span>
                                                <small style="color:#fff;opacity:0.9;">/ adult</small>
                                            @elseif($basePrice > 0)
                                                
                                                <span style="font-size:15px;">
                                                    {{ number_format($discountPrice, 2) }} €
                                                </span>
                                                <small style="color:#fff;opacity:0.9;">/ adult</small>
                                            @else
                                                
                                                <span style="font-size:15px;">
                                                    {{ number_format($discountPrice, 2) }} €
                                                </span>
                                                <small style="color:#fff;opacity:0.9;">/ adult</small>
                                            @endif
                                        @elseif($basePrice > 0)
                                            
                                                 
                                            <span style="font-size:15px;">
                                                {{ number_format($basePrice, 2) }} €
                                            </span>
                                            <small style="color:#fff;opacity:0.9;">/ adult</small>
                                        @else
                                            
                                            <small style="color:#fff;opacity:0.9;">Contact us for price</small>
                                        @endif
                                    @endif
                                @endif
                            </div>

                        </div>

                        <div class="tg-listing-card-content p-relative">
                            <h4 class="tg-listing-card-title mb-5">
                                <a href="{{ route('front.tourbooking.services.show', ['slug' => $service?->slug] + $query) }}">
                                    {{ Str::limit($service?->translation?->title, 45) }}
                                </a>
                            </h4>
                            <span class="tg-listing-card-duration-map d-inline-block">
                                <svg width="13" height="16" viewBox="0 0 13 16" fill="none"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M12.3329 6.7071C12.3329 11.2324 6.55512 15.1111 6.55512 15.1111C6.55512 15.1111 0.777344 11.2324 0.777344 6.7071C0.777344 5.16402 1.38607 3.68414 2.46962 2.59302C3.55316 1.5019 5.02276 0.888916 6.55512 0.888916C8.08748 0.888916 9.55708 1.5019 10.6406 2.59302C11.7242 3.68414 12.3329 5.16402 12.3329 6.7071Z"
                                        stroke="currentColor" stroke-width="1.15556" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                    <path
                                        d="M6.55512 8.64649C7.61878 8.64649 8.48105 7.7782 8.48105 6.7071C8.48105 5.636 7.61878 4.7677 6.55512 4.7677C5.49146 4.7677 4.6292 5.636 4.6292 6.7071C4.6292 7.7782 5.49146 8.64649 6.55512 8.64649Z"
                                        stroke="currentColor" stroke-width="1.15556" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                                {{ $service?->location }}
                            </span>
                            @include('tourbooking::front.services.ratting', [
                                'avgRating' => $service?->active_reviews_avg_rating ?? 0,
                                'ratingCount' => $service?->active_reviews_count ?? 0
                            ])
                            <div class="tg-listing-avai d-flex align-items-center justify-content-between">
                                @if($available)
                                    <a class="tg-listing-avai-btn"
                                       href="{{ route('front.tourbooking.services.show', ['slug' => $service->slug] + $query) }}">
                                       Check Availability
                                    </a>
                                @else
                                    <a class="tg-listing-avai-btn disabled"
                                       style="background:#ccc;color:#666;cursor:not-allowed;">
                                       No Availability
                                    </a>
                                @endif

                                <div @class([
                                    'tg-listing-item-wishlist',
                                    'active' => $service?->my_wishlist_exists == 1,
                                ]) data-url="{{ route('user.wishlist.store') }}"
                                     onclick="addToWishlist({{ $service->id }}, this, 'service')">
                                    <a href="javascript:void(0);">
                                        <svg width="20" height="18" viewBox="0 0 20 18" fill="none"
                                             xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M10.5167 16.3416C10.2334 16.4416 9.76675 16.4416 9.48341 16.3416C7.06675 15.5166 1.66675 12.075 1.66675 6.24165C1.66675 3.66665 3.74175 1.58331 6.30008 1.58331C7.81675 1.58331 9.15841 2.31665 10.0001 3.44998C10.8417 2.31665 12.1917 1.58331 13.7001 1.58331C16.2584 1.58331 18.3334 3.66665 18.3334 6.24165C18.3334 12.075 12.9334 15.5166 10.5167 16.3416Z"
                                                stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                        </svg>
                                    </a>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="text-center mt-50 mb-30">
            @include('components.front.custom-pagination', ['items' => $allServices])
        </div>
    </div>
@else
    <div class="col-12">Data Not found.</div>
@endif

<style>
/* === Equal Height Cards for All Services Grid === */
.row.list-card > [class*="col-"] {
    display: flex;
    padding-bottom: 25px;
}
.tg-listing-card-item {
    display: flex;
    flex-direction: column;
    height: 100%;
    width: 100%;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    overflow: hidden;
}
.tg-listing-card-thumb {
    flex-shrink: 0;
}
.tg-listing-card-content {
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding-bottom: 10px;
}
.tg-listing-2-price {
    min-height: 36px;
    display: flex;
    align-items: center;
}
.tg-listing-avai {
    min-height: 48px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: auto;
}
.tg-listing-avai-btn.disabled {
    background-color: #e0e0e0 !important;
    color: #777 !important;
    cursor: not-allowed;
    pointer-events: none;
    border-radius: 4px;
    width: 100%;
    text-align: center;
}
.row.list-card {
    margin-left: -12px;
    margin-right: -12px;
}
.row.list-card > [class*="col-"] {
    padding-left: 12px;
    padding-right: 12px;
}
.tg-listing-item-price-discount {
    font-weight: 600;
}
</style>