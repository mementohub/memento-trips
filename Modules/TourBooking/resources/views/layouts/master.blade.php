<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">

  <title>@yield('title', config('app.name').' — Tours')</title>
  <meta name="description" content="{{ $description ?? '' }}">
  <meta name="keywords" content="{{ $keywords ?? '' }}">
  <meta name="author" content="{{ $author ?? '' }}">

 
  <link rel="stylesheet" href="{{ asset('frontend/assets/css/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('frontend/assets/css/animate.min.css') }}">
  <link rel="stylesheet" href="{{ asset('frontend/assets/css/magnific-popup.css') }}">
  <link rel="stylesheet" href="{{ asset('frontend/assets/css/fontawesome-all.min.css') }}">
  <link rel="stylesheet" href="{{ asset('frontend/assets/css/swiper-bundle.min.css') }}">
  <link rel="stylesheet" href="{{ asset('frontend/assets/css/flatpicker.css') }}">
  <link rel="stylesheet" href="{{ asset('frontend/assets/css/odometer.css') }}">
  <link rel="stylesheet" href="{{ asset('frontend/assets/css/default.css') }}">
  <link rel="stylesheet" href="{{ asset('frontend/assets/css/main.css') }}">
  <link rel="stylesheet" href="{{ asset('frontend/assets/css/hero-search.css') }}">
  <link rel="stylesheet" href="{{ asset('frontend/assets/css/dev.css') }}">
  <link rel="stylesheet" href="{{ asset('frontend/assets/css/cookie_consent.css') }}">
  <link rel="stylesheet" href="{{ asset('global/toastr/toastr.min.css') }}">

  {{-- Bottom bar assets --}}
  @include('theme::components.front_mobile_bottom_bar_assets')

  @stack('style_section')
</head>

<body class="td_theme_2">
  {{-- Header FRONT (desktop) --}}
  @include('theme::components.header')

  {{-- Content module --}}
  @yield('content')

  {{-- Footer FRONT --}}
  @include('theme::components.footer')

  {{-- Bottom bar (mobile) --}}
  @include('theme::components.front_mobile_bottom_bar')

  {{-- Scripts --}}
  <script src="{{ asset('global/js/jquery-3.7.1.min.js') }}"></script>
  <script src="{{ asset('frontend/assets/js/bootstrap.min.js') }}"></script>
  <script src="{{ asset('frontend/assets/js/isotope.pkgd.min.js') }}"></script>
  <script src="{{ asset('frontend/assets/js/imagesloaded.pkgd.min.js') }}"></script>
  <script src="{{ asset('frontend/assets/js/jquery.magnific-popup.min.js') }}"></script>
  <script src="{{ asset('frontend/assets/js/jquery.odometer.min.js') }}"></script>
  <script src="{{ asset('frontend/assets/js/jquery.appear.js') }}"></script>
  <script src="{{ asset('frontend/assets/js/swiper-bundle.min.js') }}"></script>
  <script src="{{ asset('frontend/assets/js/flatpickr.js') }}"></script>
  <script src="{{ asset('frontend/assets/js/nice-select.js') }}"></script>
  <script src="{{ asset('frontend/assets/js/ajax-form.js') }}"></script>
  <script src="{{ asset('frontend/assets/js/wow.min.js') }}"></script>
  <script src="{{ asset('frontend/assets/js/main.js') }}"></script>
  <script src="{{ asset('global/toastr/toastr.min.js') }}"></script>

  @stack('js_section')
</body>
</html>