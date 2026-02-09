@php
$auth_user = Auth::guard('web')->user();
@endphp

<ul class="pt-nav">
    <li class="pt-item {{ Route::is('user.dashboard') ? 'is-active' : '' }}">
        <a class="pt-link" href="{{ route('user.dashboard') }}">
            <span class="pt-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path
                        d="M9.02 2.84L3.63 7.04C2.73 7.74 2 9.23 2 10.36V17.77C2 20.09 3.89 21.99 6.21 21.99H17.79C20.11 21.99 22 20.09 22 17.78V10.5C22 9.29 21.19 7.74 20.2 7.05L14.02 2.72C12.62 1.74 10.37 1.79 9.02 2.84Z" />
                    <path d="M12 17.99V14.99" />
                </svg>
            </span>
            <span class="pt-text">{{ __('translate.Dashboard') }}</span>
        </a>
    </li>

    {{-- Wishlist --}}
    <li
        class="pt-item {{ Route::is('user.wishlist.index') || Route::is('user.wishlist.services') ? 'is-active' : '' }}">
        <a class="pt-link" data-bs-toggle="collapse" href="#pt-wishlist"
            aria-expanded="{{ Route::is('user.wishlist.index') || Route::is('user.wishlist.services') ? 'true' : 'false' }}">
            <span class="pt-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path
                        d="M17 6.49999C18.1045 6.49999 19 7.39542 19 8.49999M12 5.70252L12.6851 4.99999C14.816 2.8147 18.2709 2.8147 20.4018 4.99999C22.4755 7.12659 22.5392 10.5538 20.5461 12.7599L14.8197 19.0981C13.2984 20.782 10.7015 20.782 9.18026 19.0981L3.45393 12.7599C1.46078 10.5538 1.5245 7.12661 3.5982 5C5.72912 2.81471 9.18404 2.81472 11.315 5.00001L12 5.70252Z" />
                </svg>
            </span>
            <span class="pt-text">{{ __('translate.Wishlist') }}</span>
            <span class="pt-caret"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2">
                    <path d="m6 9 6 6 6-6" />
                </svg></span>
        </a>
        <div class="collapse pt-collapse {{ Route::is('user.wishlist.index') || Route::is('user.wishlist.services') ? 'show' : '' }}"
            id="pt-wishlist">
            <ul class="pt-nav">
                <li class="pt-item"><a class="pt-link {{ Route::is('user.wishlist.index') ? 'is-active-sub' : '' }}"
                        href="{{ route('user.wishlist.index') }}">{{ __('translate.Product List') }}</a></li>
                <li class="pt-item"><a class="pt-link {{ Route::is('user.wishlist.services') ? 'is-active-sub' : '' }}"
                        href="{{ route('user.wishlist.services') }}">{{ __('translate.Service List') }}</a></li>
            </ul>
        </div>
    </li>

    {{-- Bookings --}}
    <li class="pt-item {{ Route::is('user.bookings.index') || Route::is('user.bookings.details') ? 'is-active' : '' }}">
        <a class="pt-link" href="{{ route('user.bookings.index') }}">
            <span class="pt-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path
                        d="M17 6.49999C18.1045 6.49999 19 7.39542 19 8.49999M12 5.70252L12.6851 4.99999C14.816 2.8147 18.2709 2.8147 20.4018 4.99999C22.4755 7.12659 22.5392 10.5538 20.5461 12.7599L14.8197 19.0981C13.2984 20.782 10.7015 20.782 9.18026 19.0981L3.45393 12.7599C1.46078 10.5538 1.5245 7.12661 3.5982 5C5.72912 2.81471 9.18404 2.81472 11.315 5.00001L12 5.70252Z" />
                </svg>
            </span>
            <span class="pt-text">{{ __('translate.Bookings') }}</span>
        </a>
    </li>

    <div class="pt-sep-line"></div>

    {{-- Edit Profile --}}
    <li class="pt-item {{ Route::is('user.edit-profile') ? 'is-active' : '' }}">
        <a class="pt-link" href="{{ route('user.edit-profile') }}">
            <span class="pt-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                    stroke-linecap="round" stroke-linejoin="round">
                    <ellipse cx="12" cy="17.5" rx="7" ry="3.5" />
                    <circle cx="12" cy="7" r="4" />
                </svg>
            </span>
            <span class="pt-text">{{ __('translate.Edit Profile') }}</span>
        </a>
    </li>

    {{-- Change Password --}}
    <li class="pt-item {{ Route::is('user.change-password') ? 'is-active' : '' }}">
        <a class="pt-link" href="{{ route('user.change-password') }}">
            <span class="pt-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                    stroke-linecap="round">
                    <path
                        d="M13 7H5M13 7C15.2091 7 17 8.79086 17 11V17C17 19.2091 15.2091 21 13 21H5C2.79086 21 1 19.2091 1 17V11C1 8.79086 2.79086 7 5 7M13 7V5C13 2.79086 11.2091 1 9 1C6.79086 1 5 2.79086 5 5V7M9 15V13" />
                </svg>
            </span>
            <span class="pt-text">{{ __('translate.Change Password') }}</span>
        </a>
    </li>

    <div class="pt-sep-line"></div>

    {{-- Agency Support --}}
    <li class="pt-item {{ Route::is('user.agency-support.*') ? 'is-active' : '' }}">
        <a class="pt-link" href="{{ route('user.agency-support.index') }}">
            <span class="pt-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15a4 4 0 0 1-4 4H7l-4 4V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z" />
                </svg>
            </span>
            <span class="pt-text">{{ __('translate.Agency Support') }}</span>
        </a>
    </li>

    {{-- Support Ticket --}}
    <li class="pt-item {{ Route::is('user.support-ticket.*') ? 'is-active' : '' }}">
        <a class="pt-link" href="{{ route('user.support-ticket.index') }}">
            <span class="pt-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                    <path d="M3 9a3 3 0 0 1 0-6h18a3 3 0 0 1 0 6" />
                    <path d="M3 15a3 3 0 0 0 0 6h18a3 3 0 0 0 0-6" />
                    <path d="M12 3v18" />
                </svg>
            </span>
            <span class="pt-text">{{ __('translate.Support Ticket') }}</span>
        </a>
    </li>

    <div class="pt-sep-line"></div>

    {{-- Account Delete --}}
    <li class="pt-item {{ Route::is('user.account-delete') ? 'is-active' : '' }}">
        <a class="pt-link" href="{{ route('user.account-delete') }}">
            <span class="pt-icon" style="background:rgba(220,38,38,.10);color:#dc2626;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path
                        d="M19.5 5.5L18.6139 20.121C18.5499 21.1766 17.6751 22 16.6175 22H7.38246C6.32488 22 5.4501 21.1766 5.38612 20.121L4.5 5.5" />
                    <path
                        d="M3 5.5H8M8 5.5L9.24025 2.60608C9.39783 2.2384 9.75937 2 10.1594 2H13.8406C14.2406 2 14.6022 2.2384 14.7597 2.60608L16 5.5M8 5.5H16M21 5.5H16" />
                </svg>
            </span>
            <span class="pt-text">{{ __('translate.Account Delete') }}</span>
        </a>
    </li>

    {{-- Logout --}}
    <li class="pt-item">
        <a class="pt-link" href="{{ route('user.logout') }}">
            <span class="pt-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                    stroke-linecap="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                    <path d="M16 17l5-5-5-5" />
                    <path d="M21 12H9" />
                </svg>
            </span>
            <span class="pt-text">{{ __('translate.Logout') }}</span>
        </a>
    </li>
</ul>

@if ($auth_user->instructor_joining_request == 'approved')
<div class="d-flex d-md-none justify-content-center pt-5">
    <a href="{{ route('agency.dashboard') }}" class="panel-switcher-btn">{{ __('translate.Agency Dashboard') }}</a>
</div>
@else
<div class="d-flex d-md-none justify-content-center pt-5">
    <a href="{{ route('user.create-agency') }}" class="panel-switcher-btn">{{ __('translate.Create a agency') }}</a>
</div>
@endif