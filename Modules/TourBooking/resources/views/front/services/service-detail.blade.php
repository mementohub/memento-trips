@extends('layout_inner_page')

@section('title')
    <title>Services</title>
    <meta name="title" content="Services">
    <meta name="description" content="Services">
@endsection

@section('front-content')
    <!-- main-area -->
    <main>

        <!-- tg-breadcrumb-area-start -->
        <div class="tg-breadcrumb-spacing-3 include-bg p-relative fix"
             data-background="{{ asset($general_setting->secondary_breadcrumb_image ?? $general_setting->breadcrumb_image) }}">
            <div class="tg-hero-top-shadow"></div>
        </div>
        <div class="tg-breadcrumb-list-2-wrap">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="tg-breadcrumb-list-2">
                            <ul>
                                <li><a href="{{ url('home') }}">{{ __('translate.Home') }}</a></li>
                                <li><i class="fa-sharp fa-solid fa-angle-right"></i></li>
                                <li><a href="{{ route('front.tourbooking.services') }}">{{ __('translate.Services') }}</a></li>
                                <li><i class="fa-sharp fa-solid fa-angle-right"></i></li>
                                <li><span>{{ $service?->translation?->title }}</span></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- tg-breadcrumb-area-end -->


        <!-- tg-tour-details-area-start -->
        <div class="tg-tour-details-area pt-35 pb-25">
            <div class="container">
                <div class="row align-items-end mb-35">
                    <div class="col-xl-9 col-lg-8">
                        <div class="tg-tour-details-video-title-wrap">
                            <h2 class="tg-tour-details-video-title mb-15">
                                {{ $service?->translation?->title }}
                            </h2>
                            <div class="tg-tour-details-video-location d-flex flex-wrap">

                                @if ($service?->country || $service?->location)
    <span class="mr-25">
        <i class="fa-regular fa-location-dot"></i>
        @php
            $parts = [];
            if (!empty($service?->country)) { $parts[] = $service->country; }
            if (!empty($service?->location))    { $parts[] = $service->location; }
        @endphp
        {{ implode(', ', $parts) }}
    </span>
@endif


                                <div class="tg-tour-details-video-ratings">
                                    @foreach (range(1, 5) as $star)
                                        <i class="fa-sharp fa-solid fa-star @if ($avgRating >= $star) active @endif"></i>
                                    @endforeach
                                    <span class="review">
                                        ({{ __($reviews->count()) }}
                                        {{ __($reviews->count() > 1 ? __('translate.Reviews') : __('translate.Review')) }})
                                    </span>
                                </div>

                            </div>
                        </div>
                    </div>

                    @php
    
    $ageCatsRaw = is_array($service->age_categories)
        ? $service->age_categories
        : (json_decode($service->age_categories ?? '[]', true) ?: []);
    $enabledAgeCats = collect($ageCatsRaw)->filter(fn($c) => !empty($c['enabled']));
    $hasAgePricing = $enabledAgeCats->count() > 0;
    $minAgePrice = $hasAgePricing
        ? collect($enabledAgeCats)
            ->pluck('price')
            ->filter(fn($p) => $p !== null && $p !== '' && is_numeric($p))
            ->min()
        : null;

    
    $order = ['adult', 'child', 'baby', 'infant'];
    $enabledAgeCats = collect($order)
        ->mapWithKeys(fn($key) => isset($ageCatsRaw[$key]) ? [$key => $ageCatsRaw[$key]] : [])
        ->filter(fn($c) => !empty($c['enabled']));
@endphp


                    <div class="col-xl-3 col-lg-4">
                        <div class="tg-tour-details-video-share text-end">
                            <a class="d-none" href="#">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path d="M5.87746 9.03227L10.7343 11.8625M10.7272 4.05449L5.87746 6.88471M14.7023 2.98071C14.7023 4.15892 13.7472 5.11405 12.569 5.11405C11.3908 5.11405 10.4357 4.15892 10.4357 2.98071C10.4357 1.80251 11.3908 0.847382 12.569 0.847382C13.7472 0.847382 14.7023 1.80251 14.7023 2.98071ZM6.16901 7.95849C6.16901 9.1367 5.21388 10.0918 4.03568 10.0918C2.85747 10.0918 1.90234 9.1367 1.90234 7.95849C1.90234 6.78029 2.85747 5.82516 4.03568 5.82516C5.21388 5.82516 6.16901 6.78029 6.16901 7.95849ZM14.7023 12.9363C14.7023 14.1145 13.7472 15.0696 12.569 15.0696C11.3908 15.0696 10.4357 14.1145 10.4357 12.9363C10.4357 11.7581 11.3908 10.8029 12.569 10.8029C13.7472 10.8029 14.7023 11.7581 14.7023 12.9363Z"
                                          stroke="currentColor" stroke-width="0.977778" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                Share
                            </a>
                            <a
                                @class(['tg-listing-item-wishlist ml-25', 'active' => $service?->my_wishlist_exists == 1])
                                data-url="{{ route('user.wishlist.store') }}"
                                onclick="addToWishlist({{ $service->id }}, this, 'service')"
                                href="javascript:void(0);"
                            >
                                <svg width="16" height="14" viewBox="0 0 16 14" fill="none"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path d="M10.2606 10.7831L10.2878 10.8183L10.2606 10.7831L10.2482 10.7928C10.0554 10.9422 9.86349 11.0909 9.67488 11.2404C9.32643 11.5165 9.01846 11.7565 8.72239 11.9304C8.42614 12.1044 8.19324 12.1804 7.99978 12.1804C7.80633 12.1804 7.57342 12.1044 7.27718 11.9304C6.9811 11.7565 6.67312 11.5165 6.32472 11.2404C6.13618 11.091 5.94436 10.9423 5.75159 10.7929L5.73897 10.7831C4.90868 10.1397 4.06133 9.48294 3.36178 8.6911C2.51401 7.73157 1.92536 6.61544 1.92536 5.16811C1.92536 3.75448 2.71997 2.57143 3.80086 2.07481C4.84765 1.59384 6.26028 1.71692 7.61021 3.12673L7.64151 3.09675L7.61021 3.12673C7.7121 3.23312 7.85274 3.2933 7.99978 3.2933C8.14682 3.2933 8.28746 3.23312 8.38936 3.12673L8.35868 3.09736L8.38936 3.12673C9.73926 1.71692 11.1519 1.59384 12.1987 2.07481C13.2796 2.57143 14.0742 3.75448 14.0742 5.16811C14.0742 6.61544 13.4856 7.73157 12.6378 8.69109L12.668 8.71776L12.6378 8.6911C11.9382 9.48294 11.0909 10.1397 10.2606 10.7831ZM5.10884 11.6673L5.13604 11.6321L5.10884 11.6673L5.10901 11.6674C5.29802 11.8137 5.48112 11.9554 5.65523 12.0933C5.99368 12.3616 6.35981 12.6498 6.73154 12.8682L6.75405 12.8298L6.73154 12.8682C7.10315 13.0864 7.53174 13.2667 7.99978 13.2667C8.46782 13.2667 8.89641 13.0864 9.26802 12.8682L9.24552 12.8298L9.26803 12.8682C9.63979 12.6498 10.0059 12.3615 10.3443 12.0933C10.5185 11.9553 10.7016 11.8136 10.8907 11.6673L10.8907 11.6673L10.8926 11.6659C11.7255 11.0212 12.6722 10.2884 13.4463 9.41228L13.413 9.38285L13.4463 9.41227C14.4145 8.31636 15.1553 6.95427 15.1553 5.16811C15.1553 3.34832 14.1308 1.76808 12.6483 1.08693C11.2517 0.445248 9.53362 0.635775 7.99979 1.99784C6.46598 0.635775 4.74782 0.445248 3.35124 1.08693C1.86877 1.76808 0.844227 3.34832 0.844227 5.16811C0.844227 6.95427 1.58502 8.31636 2.55325 9.41227C3.32727 10.2883 4.27395 11.0211 5.10682 11.6657L5.10884 11.6673Z"
                                          fill="currentColor" stroke="currentColor" stroke-width="0.0888889"/>
                                </svg>
                                <span class="wishlist_change_text">
                                    @if ($service?->my_wishlist_exists == 1)
                                        Remove
                                    @else
                                        Add
                                    @endif
                                    to Wishlist
                                </span>
                            </a>
                        </div>
                    </div>
                </div>

                @php
                    $thumbnails = $service->media->where('is_thumbnail', 1)->sortBy('display_order')->values();
                    $nonThumbnails = $service->media->where('is_thumbnail', 0)->sortBy('display_order')->values();
                @endphp

                <div class="row gx-15 mb-25">
                    {{-- Left side: Big image (first thumbnail) --}}
                    <div class="col-lg-7">
                        <div class="tg-tour-details-video-thumb mb-15">
                            @if (isset($thumbnails[0]))
                                <img class="w-100" src="{{ asset($thumbnails[0]->file_path) }}" alt="{{ $thumbnails[0]->caption }}">
                            @else
                                <img class="w-100" src="{{ asset('frontend/assets/img/shape/placeholder.png') }}" alt="default">
                            @endif
                        </div>
                    </div>

                    {{-- Right side: Small images --}}
                    <div class="col-lg-5">
                        <div class="row gx-15">
                            {{-- Top-right: play button image --}}
                            <div class="col-12">
                                <div class="tg-tour-details-video-thumb p-relative mb-15">
                                    @if (isset($nonThumbnails[0]))
                                        <img class="w-100" src="{{ asset($nonThumbnails[0]->file_path) }}" alt="{{ $nonThumbnails[0]->caption }}">
                                        <div class="tg-tour-details-video-inner text-center">
                                            <a class="tg-video-play popup-video tg-pulse-border" href="{{ $service->video_url }}">
                                                <span class="p-relative z-index-11">
                                                    <svg width="19" height="21" viewBox="0 0 19 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M17.3616 8.34455C19.0412 9.31425 19.0412 11.7385 17.3616 12.7082L4.13504 20.3445C2.45548 21.3142 0.356021 20.1021 0.356021 18.1627L0.356022 2.89C0.356022 0.950609 2.45548 -0.261512 4.13504 0.708185L17.3616 8.34455Z"
                                                              fill="currentColor"/>
                                                    </svg>
                                                </span>
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Bottom-right: two smaller images --}}
                            @for ($i = 1; $i <= 2; $i++)
                                @if (isset($nonThumbnails[$i]))
                                    <div class="col-lg-6 col-md-6">
                                        <div class="tg-tour-details-video-thumb mb-15">
                                            <img class="w-100" src="{{ asset($nonThumbnails[$i]->file_path) }}" alt="{{ $nonThumbnails[$i]->caption }}">
                                        </div>
                                    </div>
                                @endif
                            @endfor
                        </div>
                    </div>
                </div>

                <div class="tg-tour-details-feature-list-wrap">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <div class="tg-tour-details-video-feature-list">
                                <ul>
                                    @if ($service?->duration)
                                        <li>
                                            <span class="icon">
                                                <svg width="18" height="18" viewBox="0 0 18 18" fill="none"
                                                     xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M9.00001 4.19992V8.99992L12.2 10.5999M17 9C17 13.4183 13.4183 17 9 17C4.58172 17 1 13.4183 1 9C1 4.58172 4.58172 1 9 1C13.4183 1 17 4.58172 17 9Z"
                                                          stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </span>
                                            <div>
                                                <span class="title">{{ __('translate.Duration') }}</span>
                                                <span class="duration">{{ $service?->duration }}</span>
                                            </div>
                                        </li>
                                    @endif

                                    @if ($service?->serviceType?->name)
                                        <li>
                                            <span class="icon">
                                                <svg width="16" height="17" viewBox="0 0 16 17" fill="none"
                                                     xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M11.5 6.52684L4.5 2.64944M1.21001 4.70401L8.00001 8.47683L14.79 4.70401M8 16V8.46931M15 11.4578V5.48102C14.9997 5.21899 14.9277 4.96165 14.7912 4.7348C14.6547 4.50794 14.4585 4.31956 14.2222 4.18855L8.77778 1.20018C8.5413 1.06904 8.27306 1 8 1C7.72694 1 7.4587 1.06904 7.22222 1.20018L1.77778 4.18855C1.54154 4.31956 1.34532 4.50794 1.2088 4.7348C1.07229 4.96165 1.00028 5.21899 1 5.48102V11.4578C1.00028 11.7198 1.07229 11.9771 1.2088 12.204C1.34532 12.4308 1.54154 12.6192 1.77778 12.7502L7.22222 15.7386C7.4587 15.8697 7.72694 15.9388 8 15.9388C8.27306 15.9388 8.5413 15.8697 8.77778 15.7386L14.2222 12.7502C14.4585 12.6192 14.6547 12.4308 14.7912 12.204C14.9277 11.9771 14.9997 11.7198 15 11.4578Z"
                                                          stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </span>
                                            <div>
                                                <span class="title">{{ __('translate.Type') }}</span>
                                                <span class="duration">{{ $service?->serviceType?->name }}</span>
                                            </div>
                                        </li>
                                    @endif

                                    @if ($service?->group_size)
                                        <li>
                                            <span class="icon">
                                                <svg width="18" height="18" viewBox="0 0 18 18" fill="none"
                                                                 xmlns="http://www.w3.org/2000/svg">
                                                                <path d="M1.7 17.2C1.5 17.2 1.3 17.1 1.2 17C1.1 16.8 1 16.7 1 16.5C1 15.1 1.4 13.7 2.1 12.4C2.8 11.2 3.9 10.1 5.1 9.4C4.6 8.8 4.2 8 4 7.2C3.9 6.4 3.9 5.5 4.1 4.8C4.3 4 4.8 3.2 5.3 2.6C5.9 2 6.6 1.5 7.3 1.3C7.9 1.1 8.5 1 9.1 1C9.3 1 9.6 1 9.8 1C10.6 1.1 11.4 1.4 12.1 1.9C12.8 2.4 13.3 3 13.7 3.7C14.1 4.4 14.3 5.2 14.3 6.1C14.3 7.3 13.9 8.5 13.1 9.4C13.7 9.8 14.3 10.2 14.9 10.7C15.7 11.5 16.2 12.3 16.7 13.3C17.1 14.3 17.3 15.3 17.3 16.4C17.3 16.6 17.2 16.8 17.1 16.9C17 17 16.8 17.1 16.6 17.1C16.5 17.1 16.4 17.1 16.3 17C16.2 17 16.1 16.9 16.1 16.8C16 16.7 16 16.7 15.9 16.6C15.9 16.5 15.8 16.4 15.8 16.3C15.8 15.4 15.6 14.6 15.3 13.8C15 13 14.5 12.3 13.8 11.7C13.2 11.2 12.6 10.7 11.9 10.4C11.1 10.9 10.2 11.2 9.1 11.2C8.1 11.2 7.1 10.9 6.3 10.4C5.2 10.9 4.2 11.7 3.5 12.8C2.8 13.9 2.4 15.1 2.4 16.4C2.4 16.6 2.3 16.8 2.2 16.9C2.1 17.1 1.9 17.2 1.7 17.2ZM9.1 2.5C8.4 2.5 7.7 2.7 7.1 3.1C6.4 3.5 6 4.1 5.7 4.7C5.4 5.4 5.3 6.1 5.5 6.9C5.6 7.6 6 8.3 6.5 8.8C7 9.3 7.7 9.7 8.4 9.8C8.6 9.8 8.9 9.9 9.1 9.9C9.6 9.9 10.1 9.8 10.5 9.6C11.2 9.3 11.7 8.9 12.2 8.2C12.6 7.6 12.8 6.9 12.8 6.2C12.8 5.2 12.4 4.3 11.7 3.6C11 2.8 10.1 2.5 9.1 2.5Z"
                                                                      fill="currentColor"/>
                                                            </svg>
                                            </span>
                                            <div>
                                                <span class="title">{{ __('translate.Group Size') }}</span>
                                                <span class="duration">{{ $service?->group_size }}</span>
                                            </div>
                                        </li>
                                    @endif

                                    @if ($service?->languages && is_array($service?->languages) && count($service?->languages) > 0)
                                        <li>
                                            <span class="icon">
                                                <svg width="17" height="17" viewBox="0 0 17 17" fill="none"
                                                     xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M16 8.5C16 12.6421 12.6421 16 8.5 16M16 8.5C16 4.35786 12.6421 1 8.5 1M16 8.5H1M8.5 16C4.35786 16 1 12.6421 1 8.5M8.5 16C10.376 13.9462 11.4421 11.281 11.5 8.5C11.4421 5.71903 10.376 3.05376 8.5 1M8.5 16C6.62404 13.9462 5.55794 11.281 5.5 8.5C5.55794 5.71903 6.62404 3.05376 8.5 1M1 8.5C1 4.35786 4.35786 1 8.5 1"
                                                          stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </span>
                                            <div>
                                                <span class="title">{{ __('translate.Languages') }}</span>
                                                <span class="duration">
                                                    @foreach ($service?->languages as $language)
                                                        {{ $language }}@if (!$loop->last), @endif
                                                    @endforeach
                                                </span>
                                            </div>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </div>

                       <div class="col-lg-4">
    @php
    $raw = is_array($service->age_categories)
        ? $service->age_categories
        : (json_decode($service->age_categories ?? '[]', true) ?: []);

   
    $ageCats = [];
    foreach ($raw as $k => $cfg) {
        if (!is_array($cfg)) continue;
        $key = $cfg['key'] ?? $cfg['code'] ?? $cfg['type'] ?? $cfg['name'] ?? (is_string($k) ? $k : null);
        if (!$key) continue;
        $ageCats[strtolower($key)] = $cfg;
    }
    if (!$ageCats && is_array($raw)) {
        // dacă deja e assoc {adult:{...}}
        $ageCats = $raw;
    }

    $adultBasePrice = $ageCats['adult']['price'] ?? null;
@endphp

    <div id="service-price" class="tg-tour-details-video-feature-price mb-15 text-right">
        <p>
            {{ __('translate.From') }}
            <span id="adultBasePrice" style="text-decoration: line-through; color: rgba(0,0,0,0.5); margin-right:6px;">
                {{ number_format($adultBasePrice ?? 0, 2) }} €
            </span>
            <span id="adultAvailPrice" style="color:#ff4200; font-weight:700;">
                {{ number_format($adultBasePrice ?? 0, 2) }} €
            </span>
            / {{ __('translate.Person') }}
        </p>
    </div>
</div>


                    </div>
                </div>
            </div>
        </div>
        <!-- tg-tour-details-area-end -->

        <!-- tg-tour-about-start -->
        <div class="tg-tour-about-area tg-tour-about-border pt-40 pb-70">
            <div class="container">
                <div class="row">
                    <div class="col-xl-9 col-lg-8">
                        <div class="tg-tour-about-wrap mr-55">
                            <div class="tg-tour-about-content">
                                <div class="tg-tour-about-inner mb-25">
                                    <h4 class="tg-tour-about-title mb-15">{{ __('translate.About This Tour') }}</h4>
                                    <div class="text-capitalize lh-28">
                                        {!! $service?->translation?->short_description !!}
                                    </div>
                                </div>

                                @if ($service?->translation?->description)
                                    <div class="tg-tour-about-inner mb-40">
                                        {!! $service?->translation?->description !!}
                                    </div>
                                    <div class="tg-tour-about-border mb-40"></div>
                                @endif

                                @if ($service?->included || $service?->excluded)
                                    <div class="tg-tour-about-inner mb-40">
                                        <h4 class="tg-tour-about-title mb-20">Included/Exclude</h4>
                                        <div class="row">
                                            @if ($service?->included)
                                                <div class="col-lg-5">
                                                    <div class="tg-tour-about-list tg-tour-about-list-2">
                                                        <ul>
                                                            @foreach (json_decode($service?->included) as $key => $item)
                                                                <li>
                                                                    <span class="icon mr-10">
                                                                        <i class="fa-sharp fa-solid fa-check fa-fw"></i>
                                                                    </span>
                                                                    <span class="text">{{ $item }}</span>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                </div>
                                            @endif

                                            @if ($service?->excluded)
                                                <div class="col-lg-7">
                                                    <div class="tg-tour-about-list tg-tour-about-list-2 disable">
                                                        <ul>
                                                            @foreach (json_decode($service?->excluded) as $key => $item)
                                                                <li>
                                                                    <span class="icon mr-10">
                                                                        <i class="fa-sharp fa-solid fa-xmark"></i>
                                                                    </span>
                                                                    <span class="text">{{ $item }}</span>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="tg-tour-about-border mb-40"></div>
                                @endif

                                <div class="tg-tour-faq-wrap mb-70">
                                    <h4 class="tg-tour-about-title mb-15">{{ __('translate.Tour Plan') }}</h4>

                                    @if ($service?->tour_plan_sub_title)
                                        <p class="text-capitalize lh-28 mb-20">{{ $service?->tour_plan_sub_title }}</p>
                                    @endif

                                    <div class="tg-tour-about-faq-inner">
                                        <div class="tg-tour-about-faq" id="accordionExample">
                                            @foreach ($service?->itineraries as $itinerary)
                                                <div class="accordion-item">
                                                    <h2 class="accordion-header">
                                                        <button
                                                            @class(['accordion-button', 'collapsed' => !$loop->first])
                                                            type="button"
                                                            data-bs-toggle="collapse"
                                                            data-bs-target="#collapse_{{ $itinerary->id }}"
                                                            aria-expanded="{{ $loop->first ? 'true' : 'false' }}"
                                                            aria-controls="collapse_{{ $itinerary->id }}"
                                                        >
                                                            <span>Day-{{ $itinerary?->day_number }}</span>
                                                            {{ $itinerary?->title }}
                                                        </button>
                                                    </h2>
                                                    <div
                                                        id="collapse_{{ $itinerary->id }}"
                                                        @class(['accordion-collapse collapse', 'show' => $loop->first])
                                                        data-bs-parent="#accordionExample"
                                                    >
                                                        <div class="accordion-body">
                                                            <div class="row pb-5">
                                                                @if ($itinerary?->image)
                                                                    <div class="col-md-4 mb-5">
                                                                        <img src="{{ asset($itinerary->image) }}"
                                                                             alt="{{ $itinerary->title }}"
                                                                             class="itinerary-image">
                                                                    </div>
                                                                @endif
                                                                <div @class(['col-12 mb-5' => !$itinerary?->image, 'col-md-8 mb-5' => $itinerary?->image])>
                                                                    @if ($itinerary?->description)
                                                                        <div>{!! $itinerary?->description !!}</div>
                                                                    @endif
                                                                    @if ($itinerary?->location)
                                                                        <div class="mt-3">
                                                                            <strong><i class="fa fa-map-marker"></i> Location:</strong>
                                                                            {{ $itinerary?->location }}
                                                                        </div>
                                                                    @endif
                                                                    @if ($itinerary?->duration)
                                                                        <div class="mt-3">
                                                                            <strong><i class="fa-solid fa-business-time"></i> Duration:</strong>
                                                                            {{ $itinerary?->duration }}
                                                                        </div>
                                                                    @endif
                                                                    @if ($itinerary?->meal_included)
                                                                        <div class="mt-2">
                                                                            <strong><i class="fa fa-utensils"></i> Meal Included:</strong>
                                                                            <span class="badge bg-success">{{ $itinerary?->meal_included }}</span>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                <div class="tg-tour-about-border mb-45"></div>

                                <div class="tg-tour-about-map mb-40">
                                    <h4 class="tg-tour-about-title mb-15">{{ __('translate.Location') }}</h4>

                                    @if ($service?->google_map_sub_title)
                                        <p class="text-capitalize lh-28">{{ $service?->google_map_sub_title }}</p>
                                    @endif

                                    @if ($service?->google_map_url)
                                        <div class="tg-tour-about-map h-100">{!! $service?->google_map_url !!}</div>
                                    @endif
                                </div>

                                <div class="tg-tour-about-border mb-45"></div>

                                <div class="tg-tour-about-review-wrap mb-45">
                                    <h4 class="tg-tour-about-title mb-15">{{ __('translate.Customer Reviews') }}</h4>

                                    @if ($reviews->count() > 0)
                                        <div class="tg-tour-about-review">
                                            <div class="head-reviews">
                                                <div class="review-left">
                                                    <div class="review-info-inner">
                                                        <h2>{{ number_format($avgRating, 1) }}</h2>
                                                        <p>Based On {{ __($reviews->count()) }}
                                                            {{ __($reviews->count() > 1 ? __('translate.Reviews') : __('translate.Review')) }}
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="review-right">
                                                    <div class="review-progress">
                                                        @foreach ($averageRatings as $item)
                                                            <div class="item-review-progress">
                                                                <div class="text-rv-progress"><p>{{ $item['category'] }}</p></div>
                                                                <div class="bar-rv-progress">
                                                                    <div class="progress">
                                                                        <div class="progress-bar" style="width: {{ $item['percent'] }}%"></div>
                                                                    </div>
                                                                </div>
                                                                <div class="text-avarage"><p>{{ $item['average'] }}/5</p></div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                </div>

                                <div class="tg-tour-about-border mb-35"></div>

                                <div class="tg-tour-about-cus-review-wrap mb-25">
                                    <h4 class="tg-tour-about-title mb-40">
                                        {{ __($reviews->count()) }}
                                        {{ __($reviews->count() > 1 ? __('translate.Reviews') : __('translate.Review')) }}
                                    </h4>
                                    <ul>
                                        @forelse ($paginatedReviews as $review)
                                            <li>
                                                <div class="tg-tour-about-cus-review d-flex mb-40">
                                                    <div class="tg-tour-about-cus-review-thumb">
                                                        <img
                                                            src="{{ asset($review->user->image ?? 'frontend/assets/img/shape/placeholder.png') }}"
                                                            alt="{{ $review->user->name }}">
                                                    </div>
                                                    <div>
                                                        <div class="tg-tour-about-cus-name mb-5 d-flex align-items-center justify-content-between flex-wrap">
                                                            <h6 class="mr-10 mb-10 d-inline-block">
                                                                {{ $review->user->name }}
                                                                <span>- {{ \Carbon\Carbon::parse($review->created_at)->format('d M, Y . h:i A') }}</span>
                                                            </h6>
                                                            <span class="tg-tour-about-cus-review-star mb-10 d-inline-block">
                                                                @foreach (range(1, 5) as $star)
                                                                    <i class="fa-sharp fa-solid fa-star @if ($review->rating >= $star) active @endif"></i>
                                                                @endforeach
                                                            </span>
                                                        </div>
                                                        <p class="text-capitalize lh-28 mb-10">{{ $review->review }}</p>
                                                    </div>
                                                </div>
                                                <div class="tg-tour-about-border mb-40"></div>
                                            </li>
                                        @empty
                                            <h5 class="text-center">{{ __('translate.No Review Found') }}</h5>
                                        @endforelse
                                    </ul>
                                    @include('components.front.custom-pagination', ['items' => $paginatedReviews])
                                </div>

                                <div id="reviewForm" x-data="reviewForm()" class="tg-tour-about-review-form-wrap mb-45">
                                    <h4 class="tg-tour-about-title mb-5">{{ __('translate.Leave a Reply') }}</h4>
                                    <div class="tg-tour-about-rating-category mb-20">
                                        <ul>
                                            <template x-for="(category, index) in categories" :key="category.name">
                                                <li>
                                                    <label x-text="category.name + ' :'" class="mr-2"></label>
                                                    <div class="rating-icon flex space-x-1">
                                                        <template x-for="star in 5" :key="star">
                                                            <i
                                                                class="fa-sharp fa-solid fa-star cursor-pointer"
                                                                :class="star <= category.rating ? 'active' : ''"
                                                                @click="setRating(index, star)"
                                                                @mouseover="hoverRating = star; hoverIndex = index"
                                                                @mouseleave="hoverRating = 0; hoverIndex = null"
                                                            ></i>
                                                        </template>
                                                    </div>
                                                </li>
                                            </template>
                                        </ul>
                                    </div>
                                    <div class="tg-tour-about-review-form">
                                        <form @submit.prevent="submitForm" method="POST">
                                            @csrf
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <textarea x-model="message" class="textarea mb-5" placeholder="Write Message"></textarea>
                                                    <button type="submit" class="tg-btn tg-btn-switch-animation">
                                                        {{ __('translate.Submit Review') }}
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- ========================= BOOK NOW SIDEBAR ========================= --}}
<div class="col-xl-3 col-lg-4">
    <style>
       
        .rf-booknow { border-radius: 14px; overflow: hidden; }
        .rf-booknow__head { display:flex; align-items:center; justify-content:space-between; gap:10px; margin-bottom:12px; }
        .rf-booknow__title { margin:0; }
        .rf-booknow__sub { font-size:12px; opacity:.75; margin-top:2px; }

        .rf-field { position:relative; }
        .rf-field .input { width:100%; border-radius: 10px; }
        .rf-field__icon { position:absolute; left:12px; top:50%; transform:translateY(-50%); opacity:.65; }
        .rf-field__chev { position:absolute; right:12px; top:50%; transform:translateY(-50%); opacity:.65; }
        .rf-field .input { padding-left:40px; padding-right:38px; }

        .rf-section-title { display:flex; align-items:center; justify-content:space-between; gap:10px; margin: 4px 0 10px; }
        .rf-section-title span { font-weight:600; }

        /* Extras */
        .rf-extra-list { display:flex; flex-direction:column; gap:10px; }
        .rf-extra-card {
            border: 1px solid rgba(15,23,42,.10);
            border-radius: 12px;
            background:#fff;
            overflow:hidden;
            transition: transform .12s ease, box-shadow .12s ease, border-color .12s ease;
        }
        .rf-extra-card.is-active { border-color: rgba(255,66,0,.35); box-shadow: 0 10px 22px rgba(15,23,42,.08); }
        .rf-extra-main { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:12px; }
        .rf-extra-left { display:flex; align-items:flex-start; gap:10px; min-width:0; }
        .rf-extra-toggle { padding-top:2px; }
        .rf-extra-checkbox { width:18px; height:18px; cursor:pointer; }
        .rf-extra-toggle-mandatory { width:18px; height:18px; display:flex; align-items:center; justify-content:center; }
        .rf-extra-toggle-mandatory .dot { width:10px; height:10px; border-radius:999px; background:#22c55e; display:block; box-shadow:0 0 0 3px rgba(34,197,94,.18); }

        .rf-extra-info { min-width:0; }
        .rf-extra-title { display:block; font-weight:700; line-height:1.2; margin:0 0 6px; cursor:pointer; }
        .rf-extra-desc { font-size:12px; opacity:.75; line-height:1.35; margin:0 0 8px; }
        .rf-extra-pill-row { display:flex; flex-wrap:wrap; gap:6px; }
        .rf-extra-pill {
            font-size:11px; font-weight:700; letter-spacing:.02em;
            padding:4px 8px; border-radius:8px;
            background: rgba(15,23,42,.06);
            color: rgba(15,23,42,.8);
        }
        .rf-extra-pill-soft { background: rgba(255,66,0,.10); color: rgba(255,66,0,.95); }
        .rf-extra-pill-mandatory { background: rgba(34,197,94,.10); color: rgba(22,163,74,.95); }

        .rf-extra-right { text-align:right; white-space:nowrap; }
        .rf-extra-price-main { font-weight:800; font-size:15px; display:block; }
        .rf-extra-price-unit { font-size:11px; opacity:.7; display:block; margin-top:2px; }

        .rf-extra-ages { border-top:1px dashed rgba(15,23,42,.10); padding:10px 12px; background: rgba(15,23,42,.02); }
        .rf-extra-age-row { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:8px 0; }
        .rf-extra-age-row + .rf-extra-age-row { border-top:1px dashed rgba(15,23,42,.08); }
        .rf-extra-age-label { font-weight:600; font-size:13px; }
        .rf-extra-age-price { display:flex; align-items:center; gap:10px; white-space:nowrap; }
        .rf-extra-age-select { border-radius:10px; padding:6px 10px; }

        .rf-extra-summary { display:flex; align-items:center; justify-content:space-between; padding:10px 12px; border-radius:12px; background: rgba(15,23,42,.03); font-weight:700; }

        /* Pickup modal */
        .rf-pickup-overlay{
            position:fixed; inset:0;
            background:rgba(0,0,0,.55);
            z-index:9999;
            display:flex;
            align-items:center;
            justify-content:center;
            padding:18px;
            overflow:auto;
        }
        .rf-pickup-modal{
            width:100%;
            max-width:900px;
            border-radius:14px;
            background:#fff;
            overflow:hidden;
            box-shadow:0 24px 60px rgba(0,0,0,.28);
        }
        .rf-pickup-head{ display:flex; align-items:flex-start; justify-content:space-between; gap:12px; padding:16px 18px; border-bottom:1px solid #e5e7eb; background:#f8fafc; }
        .rf-pickup-body{ height: 56vh; min-height: 420px; display:flex; flex-direction:column; }
        .rf-pickup-bar{ display:flex; align-items:center; justify-content:space-between; gap:12px; padding:10px 12px; background:#f1f5f9; border-bottom:1px solid #e5e7eb; }
        .rf-pickup-content{ flex:1; display:flex; min-height:0; }
        .rf-pickup-map{ flex:1; min-height:100%; }
        .rf-pickup-list{ width:360px; max-width: 100%; border-left:1px solid #e5e7eb; background:#fafafa; overflow:auto; }
        .rf-pickup-listhead{ position:sticky; top:0; z-index:10; background:#fff; border-bottom:1px solid #e5e7eb; padding:12px; }
        .rf-pickup-item .rf-pickup-card{
            background:#fff;
            border-radius:12px;
            border:1px solid rgba(15,23,42,.10);
            padding:12px;
            transition: transform .12s ease, box-shadow .12s ease, border-color .12s ease;
        }
        .rf-pickup-item.selected .rf-pickup-card{ border-color: rgba(37,99,235,.45); box-shadow:0 10px 22px rgba(15,23,42,.08); transform: translateY(-1px); }
        .rf-pickup-foot{ display:flex; align-items:center; justify-content:space-between; gap:12px; padding:14px 18px; border-top:1px solid #e5e7eb; background:#f8fafc; }

        @media (max-width: 991.98px){
            .rf-pickup-content{ flex-direction:column; }
            .rf-pickup-list{ width:100%; border-left:none; border-top:1px solid #e5e7eb; max-height: 40vh; }
            .rf-pickup-body{ height:auto; min-height:unset; }
            .rf-pickup-map{ height: 320px; }
        }
    </style>

    <div x-data="bookingForm()" class="tg-tour-about-sidebar top-sticky mb-50 rf-booknow">
        <form
            action="{{ route('front.tourbooking.book.checkout.view') }}"
            method="GET"
            @submit="validateAndSubmit($event)"
            x-ref="bookingForm"
        >
            <div class="rf-booknow__head">
                <div>
                    <h4 class="tg-tour-about-title title-2 mb-0 rf-booknow__title">Book Now</h4>
                    <div class="rf-booknow__sub">{{ __('Secure your spot in seconds') }}</div>
                </div>
            </div>

            <input type="hidden" name="service_id" value="{{ $service->id }}">
            <input type="hidden" name="intended_from" value="booking">
            <input type="hidden" name="pickup_point_id" x-bind:value="selectedPickupPoint?.id || ''">
            <input type="hidden" name="pickup_extra_charge" x-bind:value="pickupExtraCharge || 0">
            <input type="hidden" name="availability_id" x-bind:value="currentAvailability?.id || ''">

            {{-- Date --}}
            <div class="tg-booking-form-parent-inner mb-10">
                <div class="tg-tour-about-date p-relative rf-field">
                    @php
                        $urlCheckIn = request('checkIn');
                        if ($urlCheckIn && preg_match('/\d{2}\/\d{2}\/\d{4}/', $urlCheckIn)) {
                            [$month, $day, $year] = explode('/', $urlCheckIn);
                            $defaultDate = "{$year}-{$month}-{$day}";
                        } else {
                            $defaultDate = now()->format('Y-m-d');
                        }
                    @endphp

                    <span class="rf-field__icon calender"></span>
                    <input
                        id="check_in_date"
                        required
                        class="input"
                        name="check_in_date"
                        type="text"
                        placeholder="When (Date)"
                        value="{{ $defaultDate }}"
                    >
                    <span class="rf-field__chev angle"><i class="fa-sharp fa-solid fa-angle-down"></i></span>
                </div>

                <div id="availability-info" class="mt-2" style="display:none;"></div>
            </div>

            {{-- Tickets / Age Categories --}}
            @if ($hasAgePricing)
                <div class="tg-tour-about-border-doted mb-15"></div>
                <div class="tg-tour-about-tickets-wrap mb-15">
                    <div class="rf-section-title">
                        <span class="tg-tour-about-sidebar-title">Tickets:</span>
                        <small class="text-muted">{{ __('Select quantities') }}</small>
                    </div>

                    <template x-for="(cfg, key) in ageConfig" :key="key">
                        <div class="tg-tour-about-tickets mb-10">
                            <div class="tg-tour-about-tickets-adult">
                                <span x-text="cfg.label"></span>
                                <p class="mb-0">
                                    <span x-text="ageRangeText(cfg)"></span>
                                    <span x-text="calculatePrice(prices[key])"></span>
                                </p>
                            </div>
                            <div class="tg-tour-about-tickets-quantity">
                                <select
                                    class="item-first custom-select"
                                    :name="'age_quantities[' + key + ']'"
                                    x-model.number="tickets[key]"
                                >
                                    <template x-for="i in 11" :key="i">
                                        <option :value="i - 1" x-text="i - 1"></option>
                                    </template>
                                </select>
                            </div>
                        </div>
                    </template>
                </div>
                <div class="tg-tour-about-border-doted mb-15"></div>

            @elseif ($service->is_per_person)
                <div class="tg-tour-about-border-doted mb-15"></div>

                <div class="tg-tour-about-tickets-wrap mb-15">
                    <div class="rf-section-title">
                        <span class="tg-tour-about-sidebar-title">Tickets:</span>
                        <small class="text-muted">{{ __('Select quantities') }}</small>
                    </div>

                    <div class="tg-tour-about-tickets mb-10">
                        <div class="tg-tour-about-tickets-adult">
                            <span>Person</span>
                            <p class="mb-0">(18+ years) <span x-text="calculatePrice(pricesLegacy.person)"></span></p>
                        </div>
                        <div class="tg-tour-about-tickets-quantity">
                            <select name="person" class="item-first custom-select" x-model.number="ticketsLegacy.person">
                                <template x-for="i in 8" :key="i">
                                    <option :value="i" x-text="i"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <div class="tg-tour-about-tickets mb-10">
                        <div class="tg-tour-about-tickets-adult">
                            <span>Children</span>
                            <p class="mb-0">(13-17 years) <span x-text="calculatePrice(pricesLegacy.children)"></span></p>
                        </div>
                        <div class="tg-tour-about-tickets-quantity">
                            <select name="children" class="item-first custom-select" x-model.number="ticketsLegacy.children">
                                <template x-for="i in 8" :key="i">
                                    <option :value="i - 1" x-text="i - 1"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="tg-tour-about-border-doted mb-15"></div>
            @endif

            {{-- Extras --}}
            @if ($service->extraCharges->count() > 0)
                <div class="tg-tour-about-extra mb-10">
                    <div class="rf-section-title">
                        <span class="tg-tour-about-sidebar-title d-inline-block">Add Extra:</span>
                        <small class="text-muted">{{ __('Optional add-ons') }}</small>
                    </div>

                    <div class="rf-extra-list">
                        @foreach ($service->extraCharges as $extra)
                            @php
                                $priceType = $extra->price_type ?? 'flat'; // flat | per_person
                                $isPerPerson = $priceType === 'per_person';

                                $general = $extra->general_price ?? null;
                                $adult   = $extra->adult_price ?? null;
                                $child   = $extra->child_price ?? null;
                                $infant  = $extra->infant_price ?? null;

                                $isMandatory = (bool)($extra->is_mandatory ?? false);
                                $applyAll    = (bool)($extra->apply_to_all_persons ?? false);

                                $headlinePrice = $isPerPerson
                                    ? ($adult ?? $child ?? $infant ?? 0)
                                    : (($general !== null) ? $general : ($extra->price ?? 0));

                                $badgeLabel = $isPerPerson ? 'PER PERSON' : 'PER BOOKING';
                                $headlineUnit = $isPerPerson ? __('Per person') : __('Per booking');

                                $hasAgePrices = $isPerPerson && ( ($adult ?? 0) > 0 || ($child ?? 0) > 0 || ($infant ?? 0) > 0 );
                                $currency = config('settings.currency_icon', '€');
                            @endphp

                            <div
                                class="rf-extra-card"
                                :class="{'is-active': extrasState[{{ $extra->id }}]?.active}"
                                data-extra-id="{{ $extra->id }}"
                                data-price-type="{{ $priceType }}"
                                data-general-price="{{ (float)($general ?? 0) }}"
                                data-adult-price="{{ (float)($adult ?? 0) }}"
                                data-child-price="{{ (float)($child ?? 0) }}"
                                data-infant-price="{{ (float)($infant ?? 0) }}"
                                data-is-mandatory="{{ $isMandatory ? 1 : 0 }}"
                                data-apply-all="{{ $applyAll ? 1 : 0 }}"
                                data-is-tax="{{ !empty($extra->is_tax) ? 1 : 0 }}"
                                data-tax-pct="{{ (float)($extra->tax_percentage ?? 0) }}"
                                data-max-qty="{{ (int)($extra->max_quantity ?? 0) }}"
                                x-init="
                                    extrasState[{{ $extra->id }}] = extrasState[{{ $extra->id }}] || {
                                        active: {{ $isMandatory ? 'true' : 'false' }},
                                        quantities: { adult:0, child:0, baby:0, infant:0 }
                                    };
                                    @if($isMandatory)
                                        extrasState[{{ $extra->id }}].active = true;
                                    @endif
                                "
                            >
                                <div class="rf-extra-main">
                                    <div class="rf-extra-left">
                                        <div class="rf-extra-toggle">
                                            @if ($isMandatory)
                                                <div class="rf-extra-toggle-mandatory" title="{{ __('Included') }}">
                                                    <span class="dot"></span>
                                                </div>
                                            @else
                                                <input
                                                    type="checkbox"
                                                    class="rf-extra-checkbox"
                                                    id="extra_{{ $extra->id }}"
                                                    x-model="extrasState[{{ $extra->id }}].active"
                                                    @change="onExtraToggle({{ $extra->id }})"
                                                >
                                            @endif
                                        </div>

                                        <div class="rf-extra-info">
                                            <label for="extra_{{ $extra->id }}" class="rf-extra-title">
                                                {{ $extra->name }}
                                                @if ($isMandatory)
                                                    <span class="rf-extra-pill rf-extra-pill-mandatory">{{ __('Included') }}</span>
                                                @endif
                                                @if (!empty($extra->is_tax))
                                                    <span class="rf-extra-pill rf-extra-pill-soft">{{ __('TAX') }} {{ (float)($extra->tax_percentage ?? 0) }}%</span>
                                                @endif
                                                @if ($applyAll && $isPerPerson)
                                                    <span class="rf-extra-pill">{{ __('Applied to all travellers') }}</span>
                                                @endif
                                            </label>

                                            @if (!empty($extra->description))
                                                <p class="rf-extra-desc">{{ Str::limit(strip_tags($extra->description), 110) }}</p>
                                            @endif

                                            <div class="rf-extra-pill-row">
                                                <span class="rf-extra-pill">{{ $badgeLabel }}</span>
                                                @if ($isPerPerson && $hasAgePrices)
                                                    <span class="rf-extra-pill rf-extra-pill-soft">{{ __('Age pricing') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="rf-extra-right">
                                        <span class="rf-extra-price-main">{{ number_format((float)$headlinePrice, 2) }} {{ $currency }}</span>
                                        <span class="rf-extra-price-unit">{{ $headlineUnit }}</span>
                                    </div>
                                </div>

                                {{-- Age breakdown (only if per_person and has any age price) --}}
                                @if ($hasAgePrices)
                                    <div class="rf-extra-ages">
                                        {{-- Adult --}}
                                        @if (($adult ?? 0) > 0)
                                            <div class="rf-extra-age-row">
                                                <div class="rf-extra-age-label">{{ __('Adult') }}</div>
                                                <div class="rf-extra-age-price">
                                                    <span>{{ number_format((float)$adult, 2) }} {{ $currency }}</span>

                                                    @unless ($isMandatory || $applyAll)
                                                        <select class="rf-extra-age-select" x-model.number="extrasState[{{ $extra->id }}].quantities.adult">
                                                            <template x-for="i in ((tickets['adult'] || 0) + 1)" :key="i">
                                                                <option :value="i - 1" x-text="i - 1"></option>
                                                            </template>
                                                        </select>
                                                    @endunless
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Child --}}
                                        @if (($child ?? 0) > 0)
                                            <div class="rf-extra-age-row">
                                                <div class="rf-extra-age-label">{{ __('Child') }}</div>
                                                <div class="rf-extra-age-price">
                                                    <span>{{ number_format((float)$child, 2) }} {{ $currency }}</span>

                                                    @unless ($isMandatory || $applyAll)
                                                        <select class="rf-extra-age-select" x-model.number="extrasState[{{ $extra->id }}].quantities.child">
                                                            <template x-for="i in ((tickets['child'] || 0) + 1)" :key="i">
                                                                <option :value="i - 1" x-text="i - 1"></option>
                                                            </template>
                                                        </select>
                                                    @endunless
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Infant (also used for baby if you want to keep UX consistent) --}}
                                        @if (($infant ?? 0) > 0)
                                            <div class="rf-extra-age-row">
                                                <div class="rf-extra-age-label">{{ __('Infant') }}</div>
                                                <div class="rf-extra-age-price">
                                                    <span>{{ number_format((float)$infant, 2) }} {{ $currency }}</span>

                                                    @unless ($isMandatory || $applyAll)
                                                        <select class="rf-extra-age-select" x-model.number="extrasState[{{ $extra->id }}].quantities.infant">
                                                            <template x-for="i in ((tickets['infant'] || 0) + 1)" :key="i">
                                                                <option :value="i - 1" x-text="i - 1"></option>
                                                            </template>
                                                        </select>
                                                    @endunless
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                
                                <input type="hidden" :name="'extras[{{ $extra->id }}][active]'" :value="extrasState[{{ $extra->id }}]?.active ? 1 : 0">
                                <input type="hidden" name="extras[{{ $extra->id }}][price_type]" value="{{ $priceType }}">
                                <input type="hidden" name="extras[{{ $extra->id }}][is_mandatory]" value="{{ $isMandatory ? 1 : 0 }}">
                                <input type="hidden" name="extras[{{ $extra->id }}][apply_to_all_persons]" value="{{ $applyAll ? 1 : 0 }}">
                                <input type="hidden" name="extras[{{ $extra->id }}][general_price]" value="{{ (float)($general ?? 0) }}">
                                <input type="hidden" name="extras[{{ $extra->id }}][adult_price]" value="{{ (float)($adult ?? 0) }}">
                                <input type="hidden" name="extras[{{ $extra->id }}][child_price]" value="{{ (float)($child ?? 0) }}">
                                <input type="hidden" name="extras[{{ $extra->id }}][infant_price]" value="{{ (float)($infant ?? 0) }}">
                                <input type="hidden" name="extras[{{ $extra->id }}][is_tax]" value="{{ !empty($extra->is_tax) ? 1 : 0 }}">
                                <input type="hidden" name="extras[{{ $extra->id }}][tax_percentage]" value="{{ (float)($extra->tax_percentage ?? 0) }}">
                                <input type="hidden" name="extras[{{ $extra->id }}][max_quantity]" value="{{ (int)($extra->max_quantity ?? 0) }}">

                                {{-- Quantities --}}
                                <input type="hidden" :name="'extras[{{ $extra->id }}][quantities][adult]'"  :value="extrasState[{{ $extra->id }}]?.quantities?.adult  || 0">
                                <input type="hidden" :name="'extras[{{ $extra->id }}][quantities][child]'"  :value="extrasState[{{ $extra->id }}]?.quantities?.child  || 0">
                                <input type="hidden" :name="'extras[{{ $extra->id }}][quantities][baby]'"   :value="extrasState[{{ $extra->id }}]?.quantities?.baby   || 0">
                                <input type="hidden" :name="'extras[{{ $extra->id }}][quantities][infant]'" :value="extrasState[{{ $extra->id }}]?.quantities?.infant || 0">
                            </div>
                        @endforeach
                    </div>

                    <div class="rf-extra-summary mt-2" x-show="extrasTotal > 0">
                        <span>{{ __('Extras total') }}</span>
                        <span x-text="calculatePrice(extrasTotal)"></span>
                    </div>
                </div>

                <div class="tg-tour-about-border-doted mb-15"></div>
            @endif

            {{-- Pickup Points --}}
            @if ($service->activePickupPoints->count() > 0)
                <div class="tg-tour-about-pickup mb-10">
                    <div class="rf-section-title">
                        <span class="tg-tour-about-sidebar-title d-inline-block">{{ __('Pickup Point') }}</span>
                        <small class="text-muted">{{ __('Optional') }}</small>
                    </div>

                    {{-- Selected Pickup Display --}}
                    <div class="selected-pickup-display mb-3">
                        <div x-show="!selectedPickupPoint" class="no-pickup-selected">
                            <div class="pickup-placeholder d-flex align-items-center justify-content-between p-3"
                                 style="background:#f8fafc;border:1px dashed #e5e7eb;border-radius:12px;cursor:pointer;">
                                <div>
                                    <i class="fa fa-map-marker text-muted me-2"></i>
                                    <span class="text-muted">{{ __('No pickup point selected') }}</span>
                                </div>
                                <small class="text-primary">{{ __('Click to choose') }} <i class="fa fa-chevron-right"></i></small>
                            </div>
                        </div>

                        <div x-show="selectedPickupPoint" class="selected-pickup-card">
                            <div class="pickup-selected-card d-flex align-items-center justify-content-between p-3"
                                 style="background:#eef2ff;border:1px solid rgba(37,99,235,.35);border-radius:12px;cursor:pointer;">
                                <div class="selected-info">
                                    <div class="d-flex align-items-center mb-1">
                                        <i class="fa fa-map-marker text-primary me-2"></i>
                                        <span class="fw-bold text-primary" x-text="selectedPickupPoint?.name || ''"></span>
                                    </div>
                                    <small class="text-muted d-block" x-text="selectedPickupPoint?.address || ''"></small>
                                    <div class="charge-display mt-2 d-flex flex-wrap gap-2">
                                        <span class="badge" :class="selectedPickupPoint?.has_charge ? 'bg-danger' : 'bg-success'"
                                              x-text="selectedPickupPoint?.formatted_charge || 'Free'"></span>
                                        <span x-show="selectedPickupPoint?.distance" class="badge bg-secondary"
                                              x-text="(selectedPickupPoint?.distance || 0) + ' km away'"></span>
                                    </div>
                                </div>
                                <div class="change-btn">
                                    <small class="text-primary">{{ __('Change') }} <i class="fa fa-edit"></i></small>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="pickup-actions d-flex gap-2">
                        <button type="button"
                                @click="showPickupModal = true"
                                class="btn btn-outline-primary btn-sm flex-fill"
                                style="border-radius:12px;">
                            <i class="fa fa-map-marker me-1"></i>
                            <span x-text="selectedPickupPoint ? '{{ __('Change Pickup') }}' : '{{ __('Choose Pickup Point') }}'"></span>
                        </button>
                        <button x-show="selectedPickupPoint"
                                type="button"
                                @click="clearPickupPoint()"
                                class="btn btn-outline-secondary btn-sm"
                                style="border-radius:12px;">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                </div>

                <div class="tg-tour-about-border-doted mb-15"></div>
            @endif

            {{-- Total --}}
            @if ($hasAgePricing)
                <div class="tg-tour-about-coast d-flex align-items-center flex-wrap justify-content-between mb-20">
                    <span class="tg-tour-about-sidebar-title d-inline-block">Total Cost:</span>
                    <h5 class="total-price" x-text="calculatePrice(totalCostWithPickup)"></h5>
                </div>
            @elseif ($service->is_per_person)
                <div class="tg-tour-about-coast d-flex align-items-center flex-wrap justify-content-between mb-20">
                    <span class="tg-tour-about-sidebar-title d-inline-block">Total Cost:</span>
                    <h5 class="total-price" x-text="calculatePrice(totalCostLegacyWithPickup)"></h5>
                </div>
            @else
                <div class="mt-4 tg-tour-about-coast d-flex align-items-center flex-wrap justify-content-between mb-20">
                    <span class="tg-tour-about-sidebar-title d-inline-block">Total Cost:</span>
                    <h5 class="total-price">{{ currency($service->discount_price ?? $service->full_price) }}</h5>
                </div>
            @endif

            <button type="submit" class="tg-btn tg-btn-switch-animation w-100" style="border-radius:12px;">
                {{ __('Book now') }}
            </button>
            <p x-show="formError" x-text="formError" class="text-danger mt-2" style="font-size:14px;"></p>
        </form>

        {{-- ===== PICKUP POINT SELECTION MODAL ===== --}}
        <div
            x-show="showPickupModal"
            class="rf-pickup-overlay"
            @click.self="showPickupModal = false"
            x-cloak
        >
            <div class="rf-pickup-modal">
                {{-- Header --}}
                <div class="rf-pickup-head">
                    <div>
                        <h5 class="mb-0">
                            <i class="fa fa-map-marker text-primary me-2"></i>
                            {{ __('Choose Pickup Point') }}
                        </h5>
                        <small class="text-muted">{{ __('Select a pickup location or continue without one') }}</small>
                    </div>
                    <button type="button" @click="showPickupModal = false" class="btn-close"></button>
                </div>

                {{-- Body --}}
                <div class="rf-pickup-body">
                    <div class="rf-pickup-bar">
                        <div class="location-status">
                            <span x-show="!userLocation" class="text-muted">
                                <i class="fa fa-location-slash me-1"></i>
                                {{ __('Location not detected') }}
                            </span>
                            <span x-show="userLocation" class="text-success">
                                <i class="fa fa-location-arrow me-1"></i>
                                {{ __('Location detected - sorted by distance') }}
                            </span>
                        </div>

                        <button type="button"
                                @click="getCurrentLocation()"
                                class="btn btn-sm btn-outline-primary"
                                :disabled="locationLoading"
                                style="border-radius:12px;">
                            <i :class="locationLoading ? 'fa fa-spinner fa-spin' : 'fa fa-location-arrow'" class="me-1"></i>
                            <span x-text="locationLoading ? '{{ __('Getting location...') }}' : '{{ __('Find Nearest') }}'"></span>
                        </button>
                    </div>

                    <div class="rf-pickup-content">
                        {{-- Map --}}
                        <div class="rf-pickup-map">
                            <div id="pickup-map-container-modal" style="width:100%;height:100%;border:none;"></div>
                        </div>

                        {{-- List --}}
                        <div class="rf-pickup-list">
                            <div class="rf-pickup-listhead">
                                <h6 class="mb-1">{{ __('Available Pickup Points') }}</h6>
                                <small class="text-muted">{{ __('Click on a point to select it') }}</small>
                            </div>

                            <div x-show="pickupLoading" class="p-3">
                                <div class="d-flex align-items-center justify-content-center py-3">
                                    <div class="spinner-border spinner-border-sm me-2" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <span>{{ __('Calculating charges...') }}</span>
                                </div>
                            </div>

                            <div class="p-3">
                                <template x-for="(pickup, index) in (pickupPoints || [])" :key="'modal-pickup-' + (pickup?.id || index)">
                                    <div class="rf-pickup-item mb-3" :class="{'selected': selectedPickupPoint?.id === pickup?.id}" @click="selectPickupPointModal(pickup)">
                                        <div class="rf-pickup-card">
                                            <div class="d-flex align-items-start justify-content-between mb-2">
                                                <div class="flex-fill">
                                                    <h6 class="mb-1" x-text="pickup?.name || 'Unknown'"></h6>
                                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                                        <span x-show="pickup?.is_default" class="badge bg-warning text-dark">
                                                            <i class="fa fa-star me-1"></i>{{ __('Default') }}
                                                        </span>
                                                        <span x-show="pickup?.distance" class="badge bg-info">
                                                            <i class="fa fa-road me-1"></i><span x-text="pickup?.distance + ' km'"></span>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="selection-indicator">
                                                    <i x-show="selectedPickupPoint?.id === pickup?.id" class="fa fa-check-circle text-primary fa-lg"></i>
                                                    <i x-show="selectedPickupPoint?.id !== pickup?.id" class="far fa-circle text-muted"></i>
                                                </div>
                                            </div>

                                            <p class="mb-2 text-muted" style="font-size:13px;line-height:1.35;" x-text="pickup?.address || ''"></p>
                                            <p x-show="pickup?.description" class="mb-2 text-muted" style="font-size:12px;font-style:italic;" x-text="pickup?.description || ''"></p>

                                            <div>
                                                <span class="badge fs-6" :class="pickup?.has_charge ? 'bg-danger' : 'bg-success'" x-text="pickup?.formatted_charge || 'Free'"></span>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <div x-show="!pickupLoading && (!pickupPoints || pickupPoints.length === 0)" class="text-center py-4">
                                    <i class="fa fa-map-marker-alt fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">{{ __('No pickup points available for this service') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="rf-pickup-foot">
                    <div class="selected-pickup-summary">
                        <div x-show="!selectedPickupPoint" class="text-muted">
                            <i class="fa fa-info-circle me-1"></i>
                            {{ __('No pickup point selected - you can proceed without one') }}
                        </div>
                        <div x-show="selectedPickupPoint">
                            <strong class="text-primary">
                                <i class="fa fa-map-marker me-1"></i>
                                <span x-text="selectedPickupPoint?.name"></span>
                            </strong>
                            <small class="d-block text-muted" x-text="selectedPickupPoint?.formatted_charge"></small>
                        </div>
                    </div>

                    <div class="modal-actions">
                        <button type="button" @click="clearPickupPointModal()" class="btn btn-outline-secondary me-2" style="border-radius:12px;">
                            {{ __('Clear Selection') }}
                        </button>
                        <button type="button" @click="confirmPickupSelection()" class="btn btn-primary" style="border-radius:12px;">
                            <i class="fa fa-check me-1"></i>
                            {{ __('Confirm Selection') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        {{-- ===== /PICKUP MODAL ===== --}}
    </div>
</div>
{{-- ======================= /BOOK NOW SIDEBAR ======================= --}}
                </div>
            </div>
        </div>
        <!-- tg-tour-about-end -->

        @include('tourbooking::front.services.popular-services')

    </main>
    <!-- main-area-end -->
@endsection


@push('js_section')
    <script src="https://cdn.jsdelivr.net/npm/gasparesganga-jquery-loading-overlay@2.1.7/dist/loadingoverlay.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<!--
<script>
(function ($) {
  'use strict';
  $(function () {

    function extractMoney(text) {
      const t = String(text || '');
      const m = t.match(/(\d{1,3}(?:[.,]\d{3})*(?:[.,]\d{2})|\d+(?:[.,]\d{2})?)\s*(?:€|$|£|lei|ron|usd|eur)?\s*$/i);
      if (!m) return 0;
      let n = m[1].replace(/\s+/g, '');
      n = n.replace(/\.(?=\d{3}\b)/g, '').replace(',', '.');
      const v = parseFloat(n);
      return isNaN(v) ? 0 : v;
    }

    function ticketRows($root) {
      const $rows = $root.find('[data-price]')
        .add($root.find('div,li,.d-flex,.tg-tour-about-tickets').filter(function () {
          return $(this).find('input[type="number"], input.tg-quantity-input').length > 0;
        }));
      return $rows;
    }

    function qtyFromRow($row) {
      const $inp = $row.find('input[type="number"], input.tg-quantity-input').first();
      const v = parseInt($inp.val(), 10);
      return isNaN(v) ? 0 : Math.max(0, v);
    }

    function priceFromRow($row) {
      const dp = $row.data('price');
      if (dp !== undefined) return parseFloat(String(dp).replace(',', '.')) || 0;
      const $p = $row.find('.price,.amount,.tg-price,.ticket-price').last();
      if ($p.length) return extractMoney($p.text());
      return extractMoney($row.text());
    }

    function extrasTotal($root) {
      let sum = 0;
      $root.find('input[type="checkbox"]').each(function () {
        const $cb = $(this);
        if (!$cb.is(':checked')) return;
        if ($cb.data('price') !== undefined) {
          const p = parseFloat(String($cb.data('price')).replace(',', '.')) || 0;
          sum += p;
          return;
        }
        const labelText = $cb.closest('label,li,div').text();
        sum += extractMoney(labelText);
      });
      return sum;
    }

    function currencySymbol($root) {
      const txt = $root.text();
      const m = txt.match(/(€|\$|£|lei|RON)/i);
      if (!m) return '$';
      const sym = m[1];
      return /ron|lei/i.test(sym) ? 'Lei ' : sym;
    }

    function fmt(n) {
      try {
        return new Intl.NumberFormat(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(n);
      } catch (e) {
        return Number(n || 0).toFixed(2);
      }
    }

    function recalc($root) {
      let total = 0;

      ticketRows($root).each(function () {
        const $row = $(this);
        const q = qtyFromRow($row);
        if (!q) return;
        const p = priceFromRow($row);
        if (p > 0) total += q * p;
      });

      total += extrasTotal($root);

      const sym = currencySymbol($root);
      $root.find('.total-price').text(sym + fmt(total));
      $root.find('input[name="total"]').val(total);
    }

    const $bookNow = $('.tg-blog-sidebar-box, .booking-sidebar, [data-book-now]').first();
    if (!$bookNow.length) return;

    recalc($bookNow);

    $bookNow.on('input change', 'input', function () {
      setTimeout(() => recalc($bookNow), 0);
    });

    let debounce;
    const mo = new MutationObserver(() => {
      clearTimeout(debounce);
      debounce = setTimeout(() => recalc($bookNow), 50);
    });
    mo.observe($bookNow[0], { childList: true, subtree: true, characterData: true });
  });
})(jQuery);
</script> -->




    <script>
        (function ($) {
            "use strict";

            $(document).ready(function () {
                
                $(".timepicker").flatpickr({
                    enableTime: true,
                    noCalendar: true,
                    dateFormat: "H:i",
                    time_24hr: true
                });

                
                const availabilityMap = @json($availabilityMap ?? []);
                const availableDates = Object.keys(availabilityMap).filter(date => availabilityMap[date].is_available);

                console.log('availabilityMap', availabilityMap);

                const datePicker = flatpickr("input[name='check_in_date']", {
    dateFormat: "Y-m-d",
    disableMobile: "true",
    minDate: "today",
    enable: availableDates,

    
    locale: {
        firstDayOfWeek: 1, 
        weekdays: {
            shorthand: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
            longhand:  ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']
        }
    },

    onChange: function (selectedDates, dateStr) {
        updateAvailabilityInfo(dateStr);
        document.dispatchEvent(new CustomEvent('booking-date-changed', { detail: { date: dateStr } }));
    }
});

                console.log('datePicker', datePicker);

                function updateAvailabilityInfo(dateStr) {
                    const availInfo = $('#availability-info');
                    const bookBtn = $('button[type="submit"]');
                    const availabilityInput = $('#selected-availability-id');

                    if (dateStr && availabilityMap[dateStr]) {
                        const info = availabilityMap[dateStr];

                        availabilityInput.val(info.id || '');

                        let html = '<div class="alert alert-info mt-2 mb-0">';

                        if (info.spots !== null && info.spots !== undefined) {
                            html += `<p class="mb-1"><strong>Available spots:</strong> ${info.spots}</p>`;
                            if (+info.spots <= 0) {
                                html += '<p class="text-danger mb-0">No spots available for this date!</p>';
                                bookBtn.prop('disabled', true);
                            } else {
                                bookBtn.prop('disabled', false);
                            }
                        } else {
                            html += '<p class="mb-1">Spots available for booking</p>';
                            bookBtn.prop('disabled', false);
                        }

                        if (info.start_time && info.end_time) {
                            html += `<p class="mb-1"><strong>Time:</strong> ${info.start_time.substring(0,5)} - ${info.end_time.substring(0,5)}</p>`;
                        }

                        // Display age-specific pricing if available
                        if (info.age_categories) {
                            const currencyIcon = '{{ default_currency()['currency_icon'] }}';
                            const currencyRate = {{ default_currency()['currency_rate'] }};
                            
                            Object.keys(info.age_categories).forEach(key => {
                                const category = info.age_categories[key];
                                if (category.enabled && category.price !== null && category.price !== undefined) {
                                    const displayPrice = (+category.price * currencyRate).toFixed(2);
                                    html += `<p class="mb-1"><strong>${key.charAt(0).toUpperCase() + key.slice(1)} price:</strong> ${currencyIcon}${displayPrice}</p>`;
                                }
                            });
                        } else {
                            // Legacy pricing display
                        if (info.special_price) {
                            html += `<p class="mb-1"><strong>Special price (adult):</strong> {{ default_currency()['currency_icon'] }}${(+info.special_price * {{ default_currency()['currency_rate'] }}).toFixed(2)}</p>`;
                        }
                        if (info.per_children_price) {
                            html += `<p class="mb-1"><strong>Child price:</strong> {{ default_currency()['currency_icon'] }}${(+info.per_children_price * {{ default_currency()['currency_rate'] }}).toFixed(2)}</p>`;
                            }
                        }

                        if (info.notes) {
                            html += `<p class="mb-0"><strong>Notes:</strong> ${info.notes}</p>`;
                        }

                        html += '</div>';
                        availInfo.html(html).show();
                    } else {
                        availInfo.hide().html('');
                        availabilityInput.val('');
                        bookBtn.prop('disabled', false);
                    }
                }

                const initialDate = $('input[name="check_in_date"]').val();
                if (initialDate) {
                    updateAvailabilityInfo(initialDate);
                }
            });
        })(jQuery);
    </script>

    {{-- AlpineJS --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    
    @php
    $raw = is_array($service->age_categories)
        ? $service->age_categories
        : (json_decode($service->age_categories ?? '[]', true) ?: []);

    $ageCats = [];
    foreach ($raw as $k => $cfg) {
        if (!is_array($cfg)) continue;
        $key = $cfg['key'] ?? $cfg['code'] ?? $cfg['type'] ?? $cfg['name'] ?? (is_string($k) ? $k : null);
        if (!$key) continue;
        $ageCats[strtolower($key)] = $cfg;
    }
    if (!$ageCats && is_array($raw)) $ageCats = $raw;

    $order = ['adult','child','baby','infant'];

    $ageConfigForJs = [];
    foreach ($order as $key) {
        if (empty($ageCats[$key])) continue;
        $cfg = $ageCats[$key];

        $enabled = filter_var($cfg['enabled'] ?? false, FILTER_VALIDATE_BOOLEAN);
        if (!$enabled) continue;

        $ageConfigForJs[$key] = [
            'label' => ucfirst($key),
            'price' => (float)($cfg['price'] ?? 0),
            'min_age' => $cfg['min_age'] ?? null,
            'max_age' => $cfg['max_age'] ?? null,
            'default_count' => 0,
        ];
    }
@endphp

<script>
  const AGE_CONFIG = @json($ageConfigForJs);
</script>
@php
    $extrasConfig = $service->extraCharges
        ->map(function ($extra) {

            // =========================
            // 1) AGE CATEGORIES (JSON) normalize -> assoc by key (adult/child/baby/infant)
            // =========================
            $ageCatsRaw = $extra->age_categories;

            if (is_string($ageCatsRaw)) {
                $ageCatsRaw = json_decode($ageCatsRaw, true) ?: [];
            } elseif ($ageCatsRaw instanceof \Illuminate\Support\Collection) {
                $ageCatsRaw = $ageCatsRaw->toArray();
            } elseif (is_object($ageCatsRaw)) {
                $ageCatsRaw = (array) $ageCatsRaw;
            }
            if (!is_array($ageCatsRaw)) $ageCatsRaw = [];

            // normalize (acceptă și listă, și assoc)
            $ageCats = [];
            foreach ($ageCatsRaw as $k => $cfg) {
                if (!is_array($cfg)) continue;

                $key = $cfg['key']
                    ?? $cfg['code']
                    ?? $cfg['type']
                    ?? $cfg['name']
                    ?? (is_string($k) ? $k : null);

                if (!$key) continue;

                $ageCats[strtolower($key)] = $cfg;
            }

            // detect enabled categories
            $hasEnabledAgeCats = false;
            foreach ($ageCats as $k => $cfg) {
                if (!empty($cfg['enabled'])) { $hasEnabledAgeCats = true; break; }
            }

            // =========================
            // 2) LEGACY PRICES (DB columns)
            // =========================
            $priceType = $extra->price_type ?? 'flat'; // flat | per_person

            $general = (float)($extra->general_price ?? ($extra->price ?? 0));

            // age columns (dacă există)
            $adult  = (float)($extra->adult_price ?? 0);
            $child  = (float)($extra->child_price ?? 0);
            $baby   = (float)($extra->baby_price ?? 0);   // dacă nu există în DB, rămâne 0
            $infant = (float)($extra->infant_price ?? 0);

            // IMPORTANT:
            // dacă ai prețuri pe vârste în coloane, trebuie tratat ca per_age (nu per_person)
            $hasColumnAgePrices = ($adult > 0 || $child > 0 || $baby > 0 || $infant > 0);

            // =========================
            // 3) Decide charge_type for Alpine
            // =========================
            // - dacă există age_categories enabled => per_age
            // - altfel dacă price_type=per_person și ai prețuri pe vârste în coloane => per_age
            // - altfel dacă price_type=per_person => per_person (un singur preț * nr persoane)
            // - altfel => per_booking
            if ($hasEnabledAgeCats) {
                $chargeType = 'per_age';
            } elseif ($priceType === 'per_person' && $hasColumnAgePrices) {
                $chargeType = 'per_age';
            } elseif ($priceType === 'per_person') {
                $chargeType = 'per_person';
            } else {
                $chargeType = 'per_booking';
            }

            // =========================
            // 4) prices_per_age 
            // =========================
            $pricesPerAge = [
                'adult'  => $adult,
                'child'  => $child,
                'baby'   => $baby,
                'infant' => $infant,
            ];

            
            if ($hasEnabledAgeCats) {
                foreach ($ageCats as $k => $cfg) {
                    if (!empty($cfg['enabled'])) {
                        $pricesPerAge[$k] = (float)($cfg['price'] ?? 0);
                    }
                }
            }

            return [
                'id'                   => (int) $extra->id,
                'charge_type'          => $chargeType,
                'price_type'           => $priceType,

                'is_mandatory'         => (bool) ($extra->is_mandatory ?? false),
                'apply_to_all_persons' => (bool) ($extra->apply_to_all_persons ?? false),

                'is_tax'               => (bool) ($extra->is_tax ?? false),
                'tax_percentage'       => (float) ($extra->tax_percentage ?? 0),

                // folosit la per_booking / per_person
                'price'                => $general,

                // folosit la per_age
                'prices_per_age'       => $pricesPerAge,
            ];
        })
        ->keyBy('id');
@endphp

<script>
    const EXTRAS_CONFIG = @json($extrasConfig);
</script>
    <script>
        function reviewForm() {
            return {
                categories: [
                    { name: 'Location',  rating: 0 },
                    { name: 'Price',     rating: 0 },
                    { name: 'Amenities', rating: 0 },
                    { name: 'Rooms',     rating: 0 },
                    { name: 'Services',  rating: 0 }
                ],
                hoverRating: 0,
                hoverIndex: null,
                message: '',

                setRating(index, rating) {
                    this.categories[index].rating = rating;
                },

                submitForm() {
                    const data = {
                        service_id: `{{ $service->id }}`,
                        message: this.message,
                        ratings: this.categories.map(c => ({ category: c.name, rating: c.rating }))
                    };

                    if (!data.message.trim()) {
                        toastr.error('{{ __('Please write your review before submitting.') }}');
                        return;
                    }
                    if (data.ratings.some(c => c.rating === 0)) {
                        toastr.error('{{ __('Please select a rating before submitting.') }}');
                        return;
                    }

                    fetch(`{{ route('front.tourbooking.reviews.store') }}`, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(data)
                    })
                        .then(r => r.json())
                        .then(d => {
                            if (d.success) {
                                toastr.success(d.message);
                                this.message = '';
                                this.categories.forEach(c => c.rating = 0);
                            } else {
                                toastr.error(d.message);
                            }
                        })
                        .catch(() => toastr.error('{{ __('An error occurred. Please try again later.') }}'));
                }
            };
        }

function bookingForm() {
    return {
        // === Currency ===
        currencyIcon: "{{ default_currency()['currency_icon'] }}",
        currencyRate: parseFloat("{{ default_currency()['currency_rate'] }}"),

        // === Extras (config + state) ===
        extrasConfig: EXTRAS_CONFIG || {},
        extrasState: (function () {
            const config = EXTRAS_CONFIG || {};
            const state  = {};
            Object.keys(config).forEach((id) => {
                state[id] = {
                    
                    active: !!config[id].is_mandatory,
                    quantities: {
                        adult: 0,
                        child: 0,
                        baby: 0,
                        infant: 0,
                    },
                };
            });
            return state;
        })(),

        onExtraToggle(id) {
            const extraCfg = this.extrasConfig[id];
            const extraSt  = this.extrasState[id];
            if (!extraCfg || !extraSt) return;

            
            if (extraCfg.charge_type !== 'per_age' || extraCfg.is_mandatory === true) {
                return;
            }

            
            if (extraSt.active) {
                const qtySource = this.getCurrentQuantities(); // adult, child, baby, infant

                ['adult', 'child', 'baby', 'infant'].forEach((key) => {
                    extraSt.quantities[key] = Number(qtySource[key] || 0);
                });
            } else {
                
                ['adult', 'child', 'baby', 'infant'].forEach((key) => {
                    extraSt.quantities[key] = 0;
                });
            }
        },

        
        ageConfig: AGE_CONFIG,
        tickets: Object.fromEntries(
            Object.keys(AGE_CONFIG || {}).map(k => [k, AGE_CONFIG[k].default_count || 0])
        ),
        prices: Object.fromEntries(
            Object.keys(AGE_CONFIG || {}).map(k => [k, parseFloat(AGE_CONFIG[k].price || 0)])
        ),

        // === Legacy per-person pricing fallback ===
        ticketsLegacy: {
            person: 1,
            children: 0
        },
        pricesLegacy: {
            person: {{ $service?->availabilitieByDate?->special_price ?? ($service->price_per_person ?? 0) }},
            children: {{ $service?->availabilitieByDate?->per_children_price ?? ($service->child_price ?? 0) }}
        },

        // === Date selection state ===
        selectedDate: "{{ now()->format('Y-m-d') }}",
        currentAvailability: null,
        loading: false,

        // === Pickup Points ===
        pickupPoints: [],
        selectedPickupPoint: null,
        pickupExtraCharge: 0,
        pickupLoading: false,
        pickupMap: null,          
        pickupModalMap: null,
        pickupMarkers: [],        
        pickupModalMarkers: [],
        userLocation: null,
        locationLoading: false,
        showPickupModal: false,

        // ===================== INIT =====================
        init() {
            const input = document.querySelector("input[name='check_in_date']");
            if (input && input.value) this.selectedDate = input.value;

            document.addEventListener('booking-date-changed', (e) => {
                const date = e.detail?.date || '';
                if (!date) return;
                this.selectedDate = date;
                this.fetchAvailabilityPricing(date);
            });

            this.fetchAvailabilityPricing(this.selectedDate);
            this.initPickupPoints();

            
            this.$watch('tickets', () => {
                this.syncAgeExtrasFromTickets();
                if (this.selectedPickupPoint?.id) {
                    this.calculatePickupCharge();
                }
            }, { deep: true });

            
            this.$watch('ticketsLegacy', () => {
                this.syncAgeExtrasFromTickets();
                if (this.selectedPickupPoint?.id) {
                    this.calculatePickupCharge();
                }
            }, { deep: true });

            
            this.$watch('showPickupModal', (isOpen) => {
                document.body.style.overflow = isOpen ? 'hidden' : '';
            });
        },

        
        syncAgeExtrasFromTickets() {
            const cfg = this.extrasConfig || {};
            const st  = this.extrasState || {};
            const qtySource = this.getCurrentQuantities(); // adult, child, baby, infant

            Object.keys(cfg).forEach((id) => {
                const extraCfg = cfg[id];
                const extraSt  = st[id];
                if (!extraCfg || !extraSt) return;

                
                if (extraCfg.charge_type !== 'per_age') return;

                
                if (extraCfg.is_mandatory) return;

                
                if (!extraSt.active) return;

                ['adult', 'child', 'baby', 'infant'].forEach((key) => {
                    
                    if (!extraSt.quantities[key]) {
                        extraSt.quantities[key] = Number(qtySource[key] || 0);
                    }
                });
            });
        },

        // ===================== PICKUP POINTS =====================
        initPickupPoints() {
            // Ensure pickup points array is initialized
            if (!Array.isArray(this.pickupPoints)) {
                this.pickupPoints = [];
            }

            // Only fetch if service has pickup points
            @if ($service->activePickupPoints->count() > 0)
                this.fetchPickupPoints();

                // Initialize modal map when modal is shown
                this.$watch('showPickupModal', (isShown) => {
                    if (isShown) {
                        this.$nextTick(() => {
                            this.initModalMap();
                        });
                    }
                });
            @endif
        },

        fetchPickupPoints() {
            // Ensure array is initialized
            if (!Array.isArray(this.pickupPoints)) {
                this.pickupPoints = [];
            }

            $.ajax({
                url: "{{ route('front.tourbooking.pickup-points.get') }}",
                method: 'GET',
                data: {
                    service_id: {{ $service->id }},
                    user_lat: this.userLocation?.lat,
                    user_lng: this.userLocation?.lng,
                    _token: "{{ csrf_token() }}"
                },
                success: (response) => {
                    console.log('Pickup points response:', response);

                    if (response.success && Array.isArray(response.data)) {
                        // Ensure each pickup point has required properties
                        this.pickupPoints = response.data.map(pickup => ({
                            id: pickup.id || null,
                            name: pickup.name || 'Unknown',
                            description: pickup.description || '',
                            address: pickup.address || '',
                            coordinates: pickup.coordinates || { lat: 0, lng: 0 },
                            extra_charge: pickup.extra_charge || 0,
                            charge_type: pickup.charge_type || 'flat',
                            formatted_charge: pickup.formatted_charge || 'Free',
                            is_default: pickup.is_default || false,
                            distance: pickup.distance || null,
                            has_charge: pickup.has_charge || false
                        }));

                        this.updateMapMarkers();

                        
                        if (!this.selectedPickupPoint) {
                            const defaultPickup = this.pickupPoints.find(p => p.is_default);
                            if (defaultPickup) {
                                
                            }
                        }
                    } else {
                        console.error('Invalid pickup points response:', response);
                        this.pickupPoints = [];
                    }
                },
                error: (xhr, status, error) => {
                    console.error('Error fetching pickup points:', {xhr, status, error});
                    this.pickupPoints = [];
                }
            });
        },

        initModalMap() {
            const mapContainer = document.getElementById('pickup-map-container-modal');
            if (!mapContainer || this.pickupModalMap) return;

            console.log('Initializing modal map...');

            // Default to service location or a general location
            const defaultLat = {{ $service->latitude ?? '40.7128' }};
            const defaultLng = {{ $service->longitude ?? '-74.0060' }};

            try {
                this.pickupModalMap = L.map('pickup-map-container-modal').setView([defaultLat, defaultLng], 12);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                }).addTo(this.pickupModalMap);

                // Wait for map to load then update markers
                this.pickupModalMap.whenReady(() => {
                    this.updateModalMapMarkers();
                });

                console.log('Modal map initialized successfully');
            } catch (error) {
                console.error('Error initializing modal map:', error);
            }
        },

        updateModalMapMarkers() {
            if (!this.pickupModalMap || !Array.isArray(this.pickupPoints)) return;

            console.log('Updating modal map markers...');

            // Clear existing markers
            this.pickupModalMarkers.forEach(marker => {
                try {
                    this.pickupModalMap.removeLayer(marker);
                } catch (e) {
                    console.warn('Error removing modal marker:', e);
                }
            });
            this.pickupModalMarkers = [];

            const bounds = [];

            // Add pickup point markers
            this.pickupPoints.forEach((pickup) => {
                if (!pickup || !pickup.coordinates || !pickup.coordinates.lat || !pickup.coordinates.lng) {
                    console.warn('Invalid pickup point data:', pickup);
                    return;
                }

                const isSelected = this.selectedPickupPoint?.id === pickup.id;
                const isDefault = pickup.is_default;

                // Enhanced icon styling
                let color = '#28a745'; // free - green
                if (pickup.has_charge) color = '#dc3545'; // paid - red
                if (isSelected) color = '#007bff'; // selected - blue
                if (isDefault && !isSelected) color = '#ffc107'; // default - yellow

                const icon = L.divIcon({
                    className: 'custom-pickup-marker-modal',
                    html: `
                        <div class="marker-wrapper">
                            <i class="fa fa-map-marker" style="color: ${color}; font-size: 32px; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);"></i>
                            ${isSelected ? '<div class="selected-pulse"></div>' : ''}
                            ${isDefault ? '<div class="default-badge">★</div>' : ''}
                        </div>
                    `,
                    iconSize: [40, 40],
                    iconAnchor: [20, 35]
                });

                try {
                    const marker = L.marker([pickup.coordinates.lat, pickup.coordinates.lng], {icon: icon})
                        .addTo(this.pickupModalMap);

                    const distanceText = pickup.distance
                        ? `<p class="mb-1"><i class="fa fa-road"></i> <strong>${pickup.distance} km away</strong></p>`
                        : '';
                    const defaultText = pickup.is_default ? '<span class="badge badge-warning">Default</span>' : '';

                    const popupContent = `
                        <div class="pickup-popup-enhanced">
                            <h6 class="mb-2" style="color: ${color};">
                                <i class="fa fa-map-marker"></i> ${pickup.name || 'Unknown'} ${defaultText}
                            </h6>
                            <p class="mb-1"><i class="fa fa-map-pin"></i> ${pickup.address || 'No address'}</p>
                            <p class="mb-1">
                                <i class="fa ${pickup.has_charge ? 'fa-money text-danger' : 'fa-check-circle text-success'}"></i>
                                <strong>${pickup.formatted_charge || 'Free'}</strong>
                            </p>
                            ${distanceText}
                            ${pickup.description ? `<p class="mb-0"><i class="fa fa-info-circle"></i> ${pickup.description}</p>` : ''}
                            <div class="text-center mt-2">
                                <button class="btn btn-sm ${isSelected ? 'btn-success' : 'btn-primary'}"
                                        onclick="selectPickupFromModalMap(${pickup.id})">
                                    ${isSelected ? '✓ Selected' : 'Select Point'}
                                </button>
                            </div>
                        </div>
                    `;

                    marker.bindPopup(popupContent, {
                        maxWidth: 280,
                        className: 'enhanced-popup'
                    });

                    marker.on('click', () => {
                        this.selectPickupPointModal(pickup);
                    });

                    bounds.push([pickup.coordinates.lat, pickup.coordinates.lng]);
                    this.pickupModalMarkers.push(marker);
                } catch (e) {
                    console.error('Error creating modal marker for pickup:', pickup.name, e);
                }
            });

            // Add user location marker if available
            if (this.userLocation?.lat && this.userLocation?.lng) {
                try {
                    const userIcon = L.divIcon({
                        className: 'user-location-marker-modal',
                        html: `
                            <div class="user-marker">
                                <i class="fa fa-location-arrow" style="color: #007bff; font-size: 26px;"></i>
                                <div class="user-pulse"></div>
                            </div>
                        `,
                        iconSize: [30, 30],
                        iconAnchor: [15, 15]
                    });

                    const userMarker = L.marker([this.userLocation.lat, this.userLocation.lng], {icon: userIcon})
                        .addTo(this.pickupModalMap)
                        .bindPopup('<div class="text-center"><h6><i class="fa fa-user"></i> Your Location</h6></div>');

                    bounds.push([this.userLocation.lat, this.userLocation.lng]);
                    this.pickupModalMarkers.push(userMarker);
                } catch (e) {
                    console.error('Error creating user location marker:', e);
                }
            }

            // Auto-fit map bounds
            try {
                if (bounds.length > 1) {
                    this.pickupModalMap.fitBounds(bounds, { padding: [20, 20] });
                } else if (bounds.length === 1) {
                    this.pickupModalMap.setView(bounds[0], 14);
                } else {
                    const defaultPickup = this.pickupPoints.find(p => p.is_default);
                    if (defaultPickup && defaultPickup.coordinates) {
                        this.pickupModalMap.setView(
                            [defaultPickup.coordinates.lat, defaultPickup.coordinates.lng],
                            14
                        );
                    }
                }

                // Force map resize
                setTimeout(() => {
                    if (this.pickupModalMap) {
                        this.pickupModalMap.invalidateSize();
                    }
                }, 300);
            } catch (e) {
                console.error('Error setting modal map bounds:', e);
            }

            // Store reference for popup button clicks
            window.selectPickupFromModalMap = (pickupId) => {
                const pickup = this.pickupPoints.find(p => p.id === pickupId);
                if (pickup) {
                    this.selectPickupPointModal(pickup);
                }
            };
        },

        // Modal-specific selection methods
        selectPickupPointModal(pickup) {
            if (!pickup || !pickup.id) {
                console.warn('Invalid pickup point:', pickup);
                return;
            }

            this.selectedPickupPoint = { ...pickup };
            this.calculatePickupCharge();
            this.updateModalMapMarkers();
        },

        clearPickupPointModal() {
            this.selectedPickupPoint = null;
            this.pickupExtraCharge = 0;
            this.updateModalMapMarkers();
        },

        confirmPickupSelection() {
            this.showPickupModal = false;
        },

        clearPickupPoint() {
            this.selectedPickupPoint = null;
            this.pickupExtraCharge = 0;
        },

        // Compatibility function for any remaining calls
        updateMapMarkers() {
            if (this.pickupModalMap) {
                this.updateModalMapMarkers();
            }
        },

        calculatePickupCharge() {
            if (!this.selectedPickupPoint?.id) {
                this.pickupExtraCharge = 0;
                return;
            }

            const quantities = this.getCurrentQuantities();
            this.pickupLoading = true;

            $.ajax({
                url: "{{ route('front.tourbooking.pickup-points.calculate-charge') }}",
                method: 'POST',
                data: {
                    pickup_point_id: this.selectedPickupPoint.id,
                    age_quantities: quantities,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: (response) => {
                    console.log('Pickup charge response:', response);
                    if (response.success && typeof response.extra_charge === 'number') {
                        this.pickupExtraCharge = response.extra_charge;
                        console.log('Updated pickup charge:', this.pickupExtraCharge);
                    } else {
                        console.error('Invalid pickup charge response:', response);
                        this.pickupExtraCharge = 0;
                    }
                },
                error: (xhr, status, error) => {
                    console.error('Error calculating pickup charge:', {xhr, status, error});
                    this.pickupExtraCharge = 0;
                },
                complete: () => {
                    this.pickupLoading = false;
                }
            });
        },

        getCurrentQuantities() {
            if (Object.keys(this.tickets || {}).length > 0) {
                return this.tickets;
            } else {
                return {
                    adult: this.ticketsLegacy.person || 0,
                    child: this.ticketsLegacy.children || 0,
                    baby: 0,
                    infant: 0
                };
            }
        },

        getCurrentLocation() {
            if (!navigator.geolocation) {
                alert('{{ __('Geolocation is not supported by this browser') }}');
                return;
            }

            this.locationLoading = true;

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    this.userLocation = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };

                    this.locationLoading = false;
                    this.fetchPickupPoints(); // Refetch with location data

                    if (this.pickupMap) {
                        this.pickupMap.setView([this.userLocation.lat, this.userLocation.lng], 13);
                    }
                },
                (error) => {
                    this.locationLoading = false;
                    console.error('Error getting location:', error);
                    alert('{{ __('Unable to get your location') }}');
                }
            );
        },

        // ===================== AVAILABILITY & PRICING =====================
        fetchAvailabilityPricing(dateStr) {
            const that = this;
            this.loadingOverlay('show');

            $.ajax({
                url: "{{ route('front.tourbooking.availability.by-date') }}",
                method: 'GET',
                data: {
                    service_id: `{{ $service->id }}`,
                    date: dateStr
                },
                success(response) {
                    console.log('AJAX Response:', response);

                    // === UPDATE MAIN PRICE DISPLAY (adult) ===
                    const baseEl  = document.getElementById('adultBasePrice');
                    const availEl = document.getElementById('adultAvailPrice');

                    if (response.success && response.data && response.data.age_categories?.adult) {
                        const cat       = response.data.age_categories.adult;
                        const basePrice = parseFloat({{ $adultBasePrice ?? 0 }});
                        const availPrice = parseFloat(cat.price || 0);

                        if (!isNaN(availPrice) && availPrice > 0 && availPrice !== basePrice) {
                            baseEl.style.display = 'inline';
                            baseEl.textContent   = basePrice.toFixed(2) + ' €';
                            availEl.textContent  = availPrice.toFixed(2) + ' €';
                        } else {
                            baseEl.style.display = 'none';
                            availEl.textContent  = basePrice.toFixed(2) + ' €';
                        }
                    } else {
                        
                        const basePrice = parseFloat({{ $adultBasePrice ?? 0 }});
                        baseEl.style.display = 'none';
                        availEl.textContent  = basePrice.toFixed(2) + ' €';
                    }

                    if (response.success && response.data) {
                        const data = response.data;
                        that.currentAvailability = data;

                        
                        if (data.age_categories) {
                            Object.keys(data.age_categories).forEach(function (key) {
                                if (that.prices.hasOwnProperty(key) && data.age_categories[key].enabled) {
                                    const price = parseFloat(data.age_categories[key].price);
                                    if (!isNaN(price)) {
                                        that.prices[key] = price;
                                    }
                                }
                            });
                        }

                        // Handle legacy pricing (fallback / unified system)
                        if (data.prices) {
                            Object.keys(data.prices).forEach(function (key) {
                                if (that.prices.hasOwnProperty(key)) {
                                    const price = parseFloat(data.prices[key]);
                                    if (!isNaN(price)) {
                                        that.prices[key] = price;
                                    }
                                }
                            });

                            if (data.prices.adult) {
                                that.pricesLegacy.person = parseFloat(data.prices.adult);
                            }
                            if (data.prices.child) {
                                that.pricesLegacy.children = parseFloat(data.prices.child);
                            }
                        }

                        // Legacy special_price handling
                        if (data.special_price !== undefined && data.special_price !== null) {
                            that.pricesLegacy.person = parseFloat(data.special_price);
                        }
                        if (data.per_children_price !== undefined && data.per_children_price !== null) {
                            that.pricesLegacy.children = parseFloat(data.per_children_price);
                        }
                    }
                },
                error(err) {
                    console.error('Error fetching availability:', err);
                },
                complete() {
                    that.loadingOverlay('hide');
                }
            });
        },

        // ===================== TOTALS (Extras + Tickets + Pickup) =====================

       get extrasTotal() {
    let total = 0;

    const cfg = this.extrasConfig || {};
    const st  = this.extrasState || {};

    const hasAgePricing = Object.keys(this.ageConfig || {}).length > 0;

    // cantități “curente” (adult/child/baby/infant)
    const qtySource = hasAgePricing
        ? (this.tickets || {})
        : {
            adult: this.ticketsLegacy.person || 0,
            child: this.ticketsLegacy.children || 0,
            baby: 0,
            infant: 0,
        };

    
    const ticketsSubtotal = hasAgePricing
        ? Object.keys(this.tickets || {}).reduce((sum, k) => sum + (Number(this.tickets[k] || 0) * Number(this.prices[k] || 0)), 0)
        : (Number(this.ticketsLegacy.person || 0) * Number(this.pricesLegacy.person || 0)) +
          (Number(this.ticketsLegacy.children || 0) * Number(this.pricesLegacy.children || 0));

    Object.keys(cfg).forEach((id) => {
        const extraCfg = cfg[id];
        const extraSt  = st[id] || {};

        if (!extraCfg) return;

        const isActive = extraCfg.is_mandatory ? true : !!extraSt.active;
        if (!isActive) return;

        // TAX %
        if (extraCfg.is_tax) {
            const pct = Number(extraCfg.tax_percentage || 0);
            if (pct > 0) total += (ticketsSubtotal * pct / 100);
            return;
        }

        const type = extraCfg.charge_type || 'per_booking';

        // PER BOOKING
        if (type === 'per_booking') {
            total += Number(extraCfg.price || 0);
            return;
        }

        // PER PERSON 
        if (type === 'per_person') {
            const peopleCount = Object.values(qtySource).reduce((s, v) => s + (Number(v) || 0), 0);
            total += Number(extraCfg.price || 0) * peopleCount;
            return;
        }

        // PER AGE
        if (type === 'per_age') {
            const pricesPerAge = extraCfg.prices_per_age || {};

            Object.keys(pricesPerAge).forEach((ageKey) => {
                const pricePer = Number(pricesPerAge[ageKey] || 0);
                if (!pricePer) return;

                
                let qty = 0;

                if (extraCfg.is_mandatory || extraCfg.apply_to_all_persons) {
                    qty = Number(qtySource[ageKey] || 0);
                } else {
                    qty = Number(extraSt.quantities?.[ageKey] || 0);
                }

                
                const maxQty = Number(qtySource[ageKey] || 0);
                qty = Math.min(qty, maxQty);

                total += pricePer * qty;
            });

            return;
        }
    });

    return Number(total.toFixed(2));
},

        get totalCostAge() {
            let total = 0;

           
            for (const key in this.tickets) {
                const qty   = Number(this.tickets[key] || 0);
                const price = Number(this.prices[key]  || 0);
                total += qty * price;
            }

            
            total += this.extrasTotal;

            return Number(total.toFixed(2));
        },

        get totalCostLegacy() {
            let total = 0;

            
            total += (this.ticketsLegacy.person   || 0) * (this.pricesLegacy.person   || 0);
            total += (this.ticketsLegacy.children || 0) * (this.pricesLegacy.children || 0);

            
            total += this.extrasTotal;

            return Number(total.toFixed(2));
        },

        get totalCostWithPickup() {
            return Number((this.totalCostAge + this.pickupExtraCharge).toFixed(2));
        },

        get totalCostLegacyWithPickup() {
            return Number((this.totalCostLegacy + this.pickupExtraCharge).toFixed(2));
        },

        // ===================== HELPERS =====================
        ageRangeText(cfg) {
            const min = cfg.min_age, max = cfg.max_age;
            if (min != null && max != null) {
                if (Number(max) >= 120) return `(${min}+ years) `;
                return `(${min}-${max} years) `;
            }
            if (min != null && (max == null || Number(max) === 0)) return `(${min}+ years) `;
            if (max != null) return `(0-${max} years) `;
            return '';
        },

        calculatePrice(amount) {
            return this.currencyIcon + (this.currencyRate * Number(amount || 0)).toFixed(2);
        },

        loadingOverlay(action = 'show', target = false) {
            const options = { size: 50, maxSize: 50, minSize: 50 };
            if (target && typeof target === 'string') $(target).LoadingOverlay(action, options);
            else $.LoadingOverlay(action, options);
        },

        // ===================== FORM VALIDATION =====================
        validateAndSubmit(event) {
            const hasAgePricing = Object.keys(this.ageConfig || {}).length > 0;

            if (hasAgePricing) {
                const adultCount = Number(this.tickets.adult || 0);
                if (adultCount === 0) {
                    event.preventDefault();
                    alert("At least one adult must be included in the booking.");
                    return false;
                }

                const totalTickets = Object.values(this.tickets || {}).reduce(
                    (sum, qty) => sum + (Number(qty) || 0),
                    0
                );

                if (totalTickets === 0) {
                    event.preventDefault();
                    alert('{{ __('Please select at least one ticket before proceeding to checkout.') }}');
                    return false;
                }
            } else {
                const totalPersons =
                    (Number(this.ticketsLegacy.person) || 0) +
                    (Number(this.ticketsLegacy.children) || 0);

                if (totalPersons === 0) {
                    event.preventDefault();
                    alert('{{ __('Please select at least one person before proceeding to checkout.') }}');
                    return false;
                }
            }

            return true;
        }
    };
}
    </script>
@endpush

@push('style_section')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        a.tg-listing-item-wishlist.active { color: var(--tg-theme-primary); }
        .tg-tour-about-cus-review-thumb img { height: 128px; }

        .tg-tour-details-video-ratings i { color: #a6a6a6; }
        .tg-tour-details-video-ratings i.active { color: var(--tg-common-yellow); }

        .custom-select {
            min-width: 60px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 1px solid #d6d6d6;
            border-radius: 24px;
            padding: 1px 14px;
            font-weight: 400;
            font-size: 16px;
            color: var(--tg-grey-1);
        }
        .custom-select:focus { outline: none; border-color: #560CE3; }

        .calender-active.open .flatpickr-innerContainer .flatpickr-days .flatpickr-day.today,
        .flatpickr-calendar.open .flatpickr-innerContainer .flatpickr-days .flatpickr-day.selected {
            color: var(--tg-common-white) !important;
            background-color: var(--tg-theme-primary) !important;
        }

        /* Pickup Points Styles */
        .pickup-point-item {
            border: 2px solid transparent;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 8px;
            background: #f8f9fa;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .pickup-point-item:hover {
            background: #e9ecef;
            border-color: #dee2e6;
        }

        .pickup-point-item.selected {
            background: #e3f2fd;
            border-color: #2196f3;
        }

        .pickup-point-label {
            cursor: pointer;
            width: 100%;
            margin: 0;
            display: block;
        }

        .pickup-info {
            margin-left: 8px;
        }

        .pickup-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 4px;
        }

        .pickup-address {
            font-size: 13px;
            color: #666;
            margin-bottom: 6px;
        }

        .pickup-details {
            font-size: 12px;
        }

        .pickup-charge {
            font-weight: 600;
        }

        /* New Modal UI Styles */
        .pickup-placeholder:hover {
            background: #e9ecef !important;
            border-color: #adb5bd !important;
        }

        .pickup-selected-card:hover {
            background: #d1ecf1 !important;
            border-color: #0c5460 !important;
        }

        .pickup-modal-overlay {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            z-index: 999999 !important; /* Higher z-index to prevent overlap */
            background: rgba(0, 0, 0, 0.6) !important;
            backdrop-filter: blur(3px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px !important;
        }

       

        .pickup-modal-overlay[x-cloak] {
            display: none !important;
        }

        .pickup-modal-content {
            animation: modalSlideIn 0.3s ease-out;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-20px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .pickup-modal-item:hover .pickup-modal-card {
            background: #f8f9fa !important;
            border-color: #6c757d !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1) !important;
        }

        .pickup-modal-item.selected .pickup-modal-card {
            background: #e3f2fd !important;
            border-color: #2196f3 !important;
        }

        .pickup-modal-name {
            font-weight: 600;
            transition: color 0.2s ease;
        }

        .pickup-modal-address {
            line-height: 1.3;
        }

        .pickup-modal-description {
            line-height: 1.3;
        }

        .pickup-modal-charge .badge {
            font-weight: 500;
        }

        .pickup-list-section::-webkit-scrollbar {
            width: 8px;
        }

        .pickup-list-section::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .pickup-list-section::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }

        .pickup-list-section::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        /* Pickup Loading Overlay */
        .pickup-loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 8px;
            z-index: 10;
        }

        .pickup-location-bar{
            display: none !important;
        }

        .pickup-points-list {
            position: relative;
        }

        .pickup-point-item.loading {
            opacity: 0.6;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }

        .pickup-point-item.loading input[type="radio"] {
            opacity: 0.5;
        }

        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
        }

        .pickup-distance {
            font-style: italic;
        }

        /* Enhanced Marker Styles */
        .custom-pickup-marker,
        .custom-pickup-marker-modal,
        .user-location-marker,
        .user-location-marker-modal {
            background: none;
            border: none;
        }

        .marker-wrapper {
            position: relative;
            display: inline-block;
        }

        .selected-pulse {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 50px;
            height: 50px;
            border: 3px solid #007bff;
            border-radius: 50%;
            transform: translate(-50%, -50%);
            animation: pulse 2s infinite;
            opacity: 0.6;
        }

        .default-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #ffc107;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: bold;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
        }

        .user-marker {
            position: relative;
            display: inline-block;
        }

        .user-pulse {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 40px;
            height: 40px;
            border: 2px solid #007bff;
            border-radius: 50%;
            transform: translate(-50%, -50%);
            animation: pulse 1.5s infinite;
            opacity: 0.4;
        }

        @keyframes pulse {
            0% {
                transform: translate(-50%, -50%) scale(0.8);
                opacity: 0.8;
            }
            50% {
                transform: translate(-50%, -50%) scale(1.2);
                opacity: 0.4;
            }
            100% {
                transform: translate(-50%, -50%) scale(1.5);
                opacity: 0;
            }
        }

        /* Enhanced Popup Styles */
        .pickup-popup-enhanced {
            min-width: 220px;
        }

        .pickup-popup-enhanced h6 {
            border-bottom: 1px solid #eee;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }

        .pickup-popup-enhanced .badge {
            font-size: 10px;
            padding: 2px 6px;
            margin-left: 5px;
        }

        .badge-warning {
            background-color: #ffc107;
            color: #212529;
        }

        .enhanced-popup .leaflet-popup-content {
            margin: 12px;
        }

        .enhanced-popup .leaflet-popup-content-wrapper {
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }            

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .pickup-modal-content {
                max-height: 95vh;
                margin: 10px;
            }
            .pickup-content-container {
                flex-direction: column;
            }
            
            .pickup-map-section {
                height: 300px;
                flex: none;
            }
            
            .pickup-list-section {
                width: 100% !important;
                border-left: none;
                border-top: 1px solid #dee2e6;
                max-height: 50vh;
            }
            
            .pickup-modal-footer {
                flex-direction: column;
                gap: 15px;
            }
            
            .pickup-modal-footer .modal-actions {
                width: 100%;
                display: flex;
                gap: 10px;
            }
            
            .pickup-modal-footer .modal-actions button {
                flex: 1;
            }
            
            .selected-pickup-summary {
                width: 100%;
                text-align: center;
            }
        }
        
        
        
        
        /* === Flatpickr – weekday header brand bar === */


.flatpickr-calendar .flatpickr-weekdays {
    display: block !important;
    background: #ff4200;                 
    border-radius: 12px 12px 0 0;        
    margin: 0;                           
    padding: 6px 0 4px;
}


.flatpickr-calendar .flatpickr-weekdaycontainer {
    display: grid !important;
    grid-template-columns: repeat(7, 1fr);
    width: 100%;
    padding: 0 12px;                     
    box-sizing: border-box;
}


.flatpickr-calendar .flatpickr-weekday {
    text-align: center;
    color: #ffffff !important;           
    font-weight: 700;
    font-size: 13px;
    line-height: 1.4;
    padding: 0 2px;
}


/* === Extras cards (Add Extra) === */
.rf-extra-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.rf-extra-card {
    border-radius: 22px;
    border: 1px solid #e5e7eb;
    background: #ffffff;
    padding: 14px 18px;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.03);
    transition: all 0.2s ease;
}

.rf-extra-card:hover {
    box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
    border-color: #d1d5db;
}

.rf-extra-card.is-active {
    border-color: var(--tg-theme-primary);
    box-shadow: 0 14px 32px rgba(255, 66, 0, 0.16);
}

.rf-extra-main {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.rf-extra-left {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
}

.rf-extra-toggle {
    flex-shrink: 0;
}


.rf-extra-checkbox {
    appearance: none;
    -webkit-appearance: none;
    width: 26px;
    height: 26px;
    border-radius: 999px;
    border: 2px solid #d1d5db;
    background: #ffffff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
}

.rf-extra-checkbox::after {
    content: "";
    width: 0;
    height: 0;
    border-radius: 999px;
    background: #ffffff;
    transition: all 0.2s ease;
}

.rf-extra-checkbox:checked {
    border-color: var(--tg-theme-primary);
    background: var(--tg-theme-primary);
    box-shadow: 0 0 0 4px rgba(255, 66, 0, 0.18);
}

.rf-extra-checkbox:checked::after {
    width: 10px;
    height: 10px;
}

/* indicator mandatory */
.rf-extra-toggle-mandatory {
    width: 26px;
    height: 26px;
    border-radius: 999px;
    background: var(--tg-theme-primary);
    box-shadow: 0 0 0 4px rgba(255, 66, 0, 0.18);
    display: flex;
    align-items: center;
    justify-content: center;
}

.rf-extra-toggle-mandatory .dot {
    width: 10px;
    height: 10px;
    border-radius: 999px;
    background: #ffffff;
}

.rf-extra-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.rf-extra-title {
    font-weight: 600;
    font-size: 16px;
    color: #111827;
    margin: 0;
    cursor: pointer;
}

.rf-extra-pill-row {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-top: 2px;
}

.rf-extra-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 3px 12px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    background: #eef2ff;
    color: #111827;
}

.rf-extra-pill-soft {
    background: #f3f4f6;
    color: #6b7280;
}

.rf-extra-pill-mandatory {
    background: #ecfdf3;
    color: #166534;
    margin-left: 8px;
}

.rf-extra-right {
    text-align: right;
    flex-shrink: 0;
}

.rf-extra-price-main {
    font-weight: 700;
    font-size: 16px;
    color: #111827;
    display: block;
}

.rf-extra-price-unit {
    font-size: 12px;
    color: #9ca3af;
}

/* age breakdown */
.rf-extra-ages {
    margin-top: 10px;
    padding-top: 8px;
    border-top: 1px dashed #e5e7eb;
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.rf-extra-age-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 13px;
}

.rf-extra-age-label {
    color: #4b5563;
}

.rf-extra-age-price {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #111827;
}

.rf-extra-age-select {
    min-width: 58px;
    border-radius: 999px;
    border: 1px solid #d1d5db;
    padding: 2px 10px;
    font-size: 13px;
    background: #ffffff;
    cursor: pointer;
}

.rf-extra-age-select:focus {
    outline: none;
    border-color: var(--tg-theme-primary);
}

/* extras summary line */
.rf-extra-summary {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 14px;
    font-weight: 500;
    color: #111827;
}

        
    </style>
@endpush

