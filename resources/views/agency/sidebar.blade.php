@php
use Illuminate\Support\Facades\Route;

$clientsUrl = Route::has('agency.clients.index') ? route('agency.clients.index') : url('/agency/clients');
$reportsUrl = Route::has('agency.reports.index') ? route('agency.reports.index') : url('/agency/reports');
$logoutUrl = Route::has('agency.logout') ? route('agency.logout') : (Route::has('user.logout') ? route('user.logout') :
url('/logout'));

$clientsActive = Route::is('agency.clients.*') || request()->is('agency/clients*');
$reportsActive = Route::is('agency.reports.*') || request()->is('agency/reports*');
@endphp

<ul class="pt-nav">
    {{-- Dashboard --}}
    <li class="pt-item {{ Route::is('agency.dashboard') ? 'is-active' : '' }}">
        <a class="pt-link" href="{{ route('agency.dashboard') }}">
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

    {{-- Clients --}}
    <li class="pt-item {{ $clientsActive ? 'is-active' : '' }}">
        <a class="pt-link" href="{{ $clientsUrl }}">
            <span class="pt-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                    stroke-linecap="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                    <path d="M20 8v6" />
                    <path d="M23 11h-6" />
                </svg>
            </span>
            <span class="pt-text">{{ __('translate.Clients') }}</span>
        </a>
    </li>

    {{-- Reports --}}
    <li class="pt-item {{ $reportsActive ? 'is-active' : '' }}">
        <a class="pt-link" href="{{ $reportsUrl }}">
            <span class="pt-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                    stroke-linecap="round">
                    <path d="M4 19V5" />
                    <path d="M8 19V11" />
                    <path d="M12 19V8" />
                    <path d="M16 19V13" />
                    <path d="M20 19V6" />
                </svg>
            </span>
            <span class="pt-text">{{ __('translate.Reports') }}</span>
        </a>
    </li>
</ul>

{{-- TourBooking sidebar items --}}
@include('tourbooking::agency.sidebar')

{{-- Withdraw sidebar items --}}
@include('paymentwithdraw::seller.sidebar')

<div class="pt-sep-line"></div>
<div class="pt-section">Account</div>

<ul class="pt-nav">
    {{-- My Profile --}}
    <li class="pt-item {{ Route::is('agency.edit-profile') ? 'is-active' : '' }}">
        <a class="pt-link" href="{{ route('agency.edit-profile') }}">
            <span class="pt-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                    stroke-linecap="round" stroke-linejoin="round">
                    <ellipse cx="12" cy="17.5" rx="7" ry="3.5" />
                    <circle cx="12" cy="7" r="4" />
                </svg>
            </span>
            <span class="pt-text">{{ __('translate.My Profile') }}</span>
        </a>
    </li>

    {{-- Agency Profile --}}
    <li class="pt-item {{ Route::is('agency.agency-profile') ? 'is-active' : '' }}">
        <a class="pt-link" href="{{ route('agency.agency-profile') }}">
            <span class="pt-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 21V5a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v16" />
                    <path d="M8 7h8M8 11h8M8 15h6" />
                </svg>
            </span>
            <span class="pt-text">{{ __('translate.Agency Profile') }}</span>
        </a>
    </li>

    {{-- Change Password --}}
    <li class="pt-item {{ Route::is('agency.change-password') ? 'is-active' : '' }}">
        <a class="pt-link" href="{{ route('agency.change-password') }}">
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

    {{-- User Support --}}
    <li
        class="pt-item {{ Route::is('agency.agency-supports') || Route::is('agency.agency-support') ? 'is-active' : '' }}">
        <a class="pt-link" href="{{ route('agency.agency-supports') }}">
            <span class="pt-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15a4 4 0 0 1-4 4H7l-4 4V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z" />
                </svg>
            </span>
            <span class="pt-text">{{ __('translate.User Support') }}</span>
        </a>
    </li>

    {{-- Support Ticket --}}
    <li class="pt-item {{ Route::is('agency.support-ticket.*') ? 'is-active' : '' }}">
        <a class="pt-link" href="{{ route('agency.support-ticket.index') }}">
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
    <li class="pt-item {{ Route::is('agency.account-delete') ? 'is-active' : '' }}">
        <a class="pt-link" href="{{ route('agency.account-delete') }}">
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
        <a class="pt-link" href="{{ $logoutUrl }}">
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

{{-- Mobile panel switcher --}}
<div class="d-flex d-md-none justify-content-center pt-5">
    <a href="{{ route('user.dashboard') }}" class="panel-switcher-btn">{{ __('translate.User Dashboard') }}</a>
</div>