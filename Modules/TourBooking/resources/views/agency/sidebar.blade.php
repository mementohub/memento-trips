<div class="pt-sep-line"></div>
<div class="pt-section">{{ __('translate.Bookings') }}</div>

<ul class="pt-nav">
    <li class="pt-item {{ Route::is('agency.tourbooking.bookings.*') ? 'is-active' : '' }}">
        <a class="pt-link" data-bs-toggle="collapse" href="#pt-agency-bookings"
            aria-expanded="{{ Route::is('agency.tourbooking.bookings.*') ? 'true' : 'false' }}">
            <span class="pt-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                    stroke-linecap="round">
                    <path
                        d="M2 21C2.5 20.0909 4.4 18.2727 8 18.2727C11.6 18.2727 13.5 16.0909 14 15M8 8V5C8 3.89543 8.89543 3 10 3H20C21.1046 3 22 3.89543 22 5V13C22 14.1046 21.1046 15 20 15H16.7397M12 7H18M10 13C10 14.1046 9.10457 15 8 15C6.89543 15 6 14.1046 6 13C6 11.8954 6.89543 11 8 11C9.10457 11 10 11.8954 10 13Z" />
                    <path d="M15 11H18" />
                </svg>
            </span>
            <span class="pt-text">{{ __('translate.Bookings') }}</span>
            <span class="pt-caret"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2">
                    <path d="m6 9 6 6 6-6" />
                </svg></span>
        </a>
        <div class="collapse pt-collapse {{ Route::is('agency.tourbooking.bookings.*') ? 'show' : '' }}"
            id="pt-agency-bookings">
            <ul class="pt-nav">
                <li class="pt-item {{ Route::is('agency.tourbooking.bookings.index') ? 'is-active' : '' }}">
                    <a class="pt-link" href="{{ route('agency.tourbooking.bookings.index') }}">{{ __('translate.All
                        Bookings') }}</a>
                </li>
            </ul>
        </div>
    </li>
</ul>