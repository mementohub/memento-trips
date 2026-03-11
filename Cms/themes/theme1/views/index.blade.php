{{-- Homepage — dynamically orders sections based on admin ordering --}}
@extends('theme::layouts.app')
@section('title')
    <title>{{ $seo_setting->seo_title }}</title>
    <meta name="title" content="{{ $seo_setting->seo_title }}">
    <meta name="description" content="{!! strip_tags(clean($seo_setting->seo_description)) !!}">
@endsection
@section('front-content')
    @php
        // Map section keys to blade component paths
        $sectionMap = [
            'theme1_slider'       => 'theme::components.slider',
            'theme1_about_us'     => 'theme::components.about',
            'theme1_tour_package' => 'theme::components.package',
            'theme1_why_choose_us'=> 'theme::components.why-choose',
            'theme1_banner'       => 'theme::components.banner',
            'theme1_destination'  => 'theme::components.destination',
            'theme1_banner_2'     => 'theme::components.banner-two',
            'theme1_testimonial'  => 'theme::components.testimonial',
            'theme1_blog'         => 'theme::components.blog',
            'theme1_cta'          => 'theme::components.cta',
        ];

        // Fetch ordering from DB for home sections
        $homeSections = \App\Models\Frontend::where('data_keys', 'like', 'theme1_%')
            ->pluck('ordering', 'data_keys')
            ->mapWithKeys(function ($ordering, $key) {
                $sectionKey = preg_replace('/\.(content|element)$/', '', $key);
                return [$sectionKey => $ordering ?: 0];
            })
            ->toArray();

        // Build ordered list (exclude slider — it's always first)
        $orderedSections = [];
        foreach ($sectionMap as $key => $view) {
            if ($key === 'theme1_slider') continue;
            $order = $homeSections[$key] ?? 999;
            $orderedSections[] = ['key' => $key, 'view' => $view, 'order' => $order];
        }

        usort($orderedSections, fn($a, $b) => $a['order'] <=> $b['order']);
    @endphp

    {{-- 1. Hero slider — always first --}}
    @include('theme::components.slider')

    {{-- 2. Booking/search form — always after slider --}}
    @include('theme::components.booking-form')

    {{-- 3. Remaining sections in admin-defined order --}}
    @foreach ($orderedSections as $section)
        @includeIf($section['view'])
    @endforeach

@endsection
