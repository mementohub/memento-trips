{{-- Mobile bottom navigation bar (front-end) --}}
@php
  $isLogged = Auth::guard('web')->check();

  $isHome = request()->routeIs('home');
  $isTrips = request()->is('tour-booking/services*') || request()->routeIs('front.tourbooking.services*');
  $isLogin = request()->is('user/login') || request()->routeIs('user.login');
@endphp

@if ($isLogged)
  {{-- exact ca la user --}}
  @include('user.partials.mobile_bottom_nav')
@else
  <nav class="app-bottom-bar app-bottom-bar--3" aria-label="Mobile bottom navigation">
    <a href="{{ route('home') }}" class="app-bottom-item {{ $isHome ? 'is-active' : '' }}">
      <span class="app-bottom-ico"><i class="fas fa-home"></i></span>
      <span class="app-bottom-txt">Home</span>
    </a>

    <a href="{{ route('front.tourbooking.services') }}" class="app-bottom-item {{ $isTrips ? 'is-active' : '' }}">
      <span class="app-bottom-ico"><i class="fas fa-suitcase-rolling"></i></span>
      <span class="app-bottom-txt">Trips</span>
    </a>

    <a href="{{ route('user.login') }}" class="app-bottom-item {{ $isLogin ? 'is-active' : '' }}">
      <span class="app-bottom-ico"><i class="fas fa-user"></i></span>
      <span class="app-bottom-txt">Log in</span>
    </a>
  </nav>
@endif