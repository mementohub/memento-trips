{{-- Site header — desktop nav, phone, cart, login, mobile hamburger --}}
<header class="tg-header-height">

    <div class="tg-header__area tg-header-lg-space z-index-999 tg-transparent d-none d-xl-block" id="header-sticky">
        <div class="container-fluid container-1860">
            <div class="row align-items-center">
                <div class="col-lg-7 col-5">
                    <div class="tgmenu__wrap d-flex align-items-center">
                        <div class="logo">
                            <a class="logo-1" href="{{ route('home') }}">
                                <img src="{{ asset($general_setting->logo) }}" alt="Logo">
                            </a>
                            <a class="logo-2 d-none" href="{{ route('home') }}">
                                <img src="{{ asset($general_setting->secondary_logo) }}" alt="Logo">
                            </a>
                        </div>

                        <nav class="tgmenu__nav tgmenu-1-space ml-180">
                            <div class="tgmenu__navbar-wrap tgmenu__main-menu d-none d-xl-flex">
                                @include('components.common_navitems')
                            </div>
                        </nav>
                    </div>
                </div>

                <div class="col-lg-5 col-7">
                    <div class="tg-menu-right-action d-flex align-items-center justify-content-end">
                        <div class="tg-header-contact-info d-flex align-items-center">
                            <span class="tg-header-contact-icon mr-5 d-none d-xl-block">

                                <svg width="21" height="21" viewBox="0 0 21 21" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M17.5747 15.8619L15.8138 17.6228C15.7656 17.6732 15.7236 17.7026 15.6627 17.7362C13.1757 19.0753 8.40326 16.5734 6.21009 14.2626C6.18698 14.2374 6.16809 14.2185 6.14502 14.1954C3.83427 12.0021 1.33257 7.22927 2.67157 4.7421C2.70515 4.68124 2.73453 4.64134 2.78491 4.5931L4.54573 2.83006C4.67586 2.69992 4.82067 2.64116 5.00114 2.64116H5.01583C5.20471 2.64327 5.35163 2.71044 5.47965 2.84895L7.75047 5.30044C7.98973 5.55651 7.98131 5.95109 7.73368 6.19877L6.26666 7.66589C5.85321 8.08148 5.67271 8.62926 5.75877 9.20856C5.94134 10.428 6.55419 11.574 7.63293 12.7095C7.65603 12.7326 7.67489 12.7536 7.69799 12.7746C8.83342 13.8534 9.97723 14.4663 11.1966 14.6488C11.7779 14.7349 12.3257 14.5544 12.7412 14.1388L14.2062 12.6738C14.4538 12.4261 14.8484 12.4177 15.1065 12.6549L17.5578 14.9259C17.6963 15.0539 17.7614 15.2008 17.7656 15.3897C17.7698 15.5785 17.709 15.7276 17.5747 15.8619Z"
                                        fill="currentColor" />
                                </svg>
                            </span>
                            <div class="tg-header-contact-number d-none d-xl-block">
                                <span>{{ __('translate.Call Us') }}:</span>
                                <a href="tel:{{ $footer->phone }}">{{ $footer->phone }}</a>
                            </div>
                        </div>




                        <div class="tg-header-btn ml-20 d-none d-sm-block">
                            @guest('web')
                            <a class="tg-btn-header" href="{{ route('user.login') }}">{{ __('translate.Login') }}</a>
                            @else
                            <a class="tg-btn-header"
                                href="{{ Auth::guard('web')->user()->is_seller == 1 ? route('agency.dashboard') : route('user.dashboard') }}">
                                {{ __('translate.Dashboard') }}
                            </a>
                            @endguest
                        </div>


                        <div class="tg-header-menu-bar lh-1 p-relative ml-20 pl-20">
                            <span class="tg-header-border d-none d-xl-block"></span>
                            <button class="tgmenu-offcanvas-open-btn menu-tigger d-none d-xl-block">
                                <span></span><span></span><span></span>
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</header>


<div class="d-block d-xl-none">

    @auth('web')

    @includeIf('user.partials.mobile_bottom_nav')

    @if (!View::exists('user.partials.mobile_bottom_nav'))
    <nav class="app-bottom-bar" role="navigation" aria-label="Bottom navigation">
        <a class="app-nav-item"
            href="{{ Auth::guard('web')->user()->is_seller == 1 ? route('agency.dashboard') : route('user.dashboard') }}">
            <span class="ico"><i class="fas fa-home"></i></span>
            <span class="lbl">Dashboard</span>
        </a>
        <a class="app-nav-item" href="{{ route('user.bookings') }}">
            <span class="ico"><i class="fas fa-clipboard-list"></i></span>
            <span class="lbl">Bookings</span>
        </a>
        <a class="app-nav-item" href="{{ route('home') }}">
            <span class="ico"><i class="fas fa-globe"></i></span>
            <span class="lbl">Home</span>
        </a>
        <a class="app-nav-item" href="{{ route('user.profile') }}">
            <span class="ico"><i class="fas fa-user"></i></span>
            <span class="lbl">Profile</span>
        </a>
    </nav>
    @endif
    @else
    <nav class="app-bottom-bar" role="navigation" aria-label="Bottom navigation">
        <a class="app-nav-item {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
            <span class="ico"><i class="fas fa-home"></i></span>
            <span class="lbl">Home</span>
        </a>

        <a class="app-nav-item {{ request()->routeIs('front.tourbooking.services') ? 'active' : '' }}"
            href="{{ route('front.tourbooking.services') }}">
            <span class="ico"><i class="fas fa-clipboard-list"></i></span>
            <span class="lbl">Trips</span>
        </a>

        <a class="app-nav-item {{ request()->routeIs('user.login') ? 'active' : '' }}" href="{{ route('user.login') }}">
            <span class="ico"><i class="fas fa-sign-in-alt"></i></span>
            <span class="lbl">Login</span>
        </a>
    </nav>
    @endauth

</div>