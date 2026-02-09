{{-- resources/views/agency/partials/mobile_bottom_nav.blade.php --}}

@php
    use Illuminate\Support\Facades\Route;

    $auth_user = Auth::guard('web')->user();

    
    $userDashboardUrl = Route::has('user.dashboard') ? route('user.dashboard') : url('/user/dashboard');

    
    $r = function(string $name, string $fallback){
        return Route::has($name) ? route($name) : url($fallback);
    };

    // MAIN bottom tabs (agency)
    $agencyDashboardUrl = $r('agency.dashboard', '/agency/dashboard');

    
    $bookingsIndexUrl = Route::has('agency.tourbooking.bookings.index')
        ? route('agency.tourbooking.bookings.index')
        : (Route::has('agency.bookings.index') ? route('agency.bookings.index') : url('/agency/tourbooking/bookings'));

    $clientsIndexUrl = Route::has('agency.tourbooking.clients.index')
        ? route('agency.tourbooking.clients.index')
        : (Route::has('agency.clients.index') ? route('agency.clients.index') : url('/agency/tourbooking/clients'));

    $reportsIndexUrl = Route::has('agency.tourbooking.reports.index')
        ? route('agency.tourbooking.reports.index')
        : (Route::has('agency.reports.index') ? route('agency.reports.index') : url('/agency/tourbooking/reports'));

    $homeUrl = Route::has('home') ? route('home') : url('/');

    // Sheet links (agency)
    $editProfileUrl   = $r('agency.edit-profile', '/agency/profile/edit');
    $agencyProfileUrl = Route::has('agency.agency-profile') ? route('agency.agency-profile') : null;
    $changePasswordUrl = $r('agency.change-password', '/agency/change-password');

    // Optional: if you have these routes, keep them; otherwise they won't show
    $supportTicketUrl = Route::has('agency.support-ticket.index')
        ? route('agency.support-ticket.index')
        : null;

    $agencySupportUrl = Route::has('agency.agency-support.index')
        ? route('agency.agency-support.index')
        : null;

    $accountDeleteUrl = Route::has('agency.account-delete')
        ? route('agency.account-delete')
        : null;

    // Active states (match user style)
    $isActiveDashboard = request()->routeIs('agency.dashboard');
    $isActiveBookings  = request()->routeIs('agency.tourbooking.bookings.*') || request()->routeIs('agency.bookings.*');
    $isActiveClients   = request()->routeIs('agency.tourbooking.clients.*')  || request()->routeIs('agency.clients.*');
@endphp

<div class="app-nav-mobile d-lg-none">
    <div class="app-nav-backdrop" data-app-sheet-close></div>

    <div class="app-nav-sheet" id="appNavSheet" aria-hidden="true">
        <div class="app-nav-sheet__header">
            <div class="app-nav-user">
                <div class="app-nav-user__avatar">
                    <img
                        src="{{ $auth_user?->image ? asset($auth_user->image) : asset($general_setting->default_avatar) }}"
                        alt="User"
                    >
                </div>
                <div class="app-nav-user__meta">
                    <div class="app-nav-user__name">{{ $auth_user?->name ?? '—' }}</div>
                    <div class="app-nav-user__email">{{ $auth_user?->email ?? '' }}</div>
                </div>
            </div>

            <button type="button" class="app-nav-sheet__close" data-app-sheet-close aria-label="Close menu">
                <span>×</span>
            </button>
        </div>

        <div class="app-nav-sheet__content">
            <div class="app-nav-grid">
                <a class="app-nav-card" href="{{ $editProfileUrl }}">
                    <span class="app-nav-card__ico"><i class="fas fa-user"></i></span>
                    <span class="app-nav-card__txt">{{ __('translate.Edit Profile') }}</span>
                </a>

                <a class="app-nav-card" href="{{ $changePasswordUrl }}">
                    <span class="app-nav-card__ico"><i class="fas fa-lock"></i></span>
                    <span class="app-nav-card__txt">{{ __('translate.Change Password') }}</span>
                </a>

                @if($supportTicketUrl)
                    <a class="app-nav-card" href="{{ $supportTicketUrl }}">
                        <span class="app-nav-card__ico"><i class="fas fa-headset"></i></span>
                        <span class="app-nav-card__txt">{{ __('translate.Support Ticket') }}</span>
                    </a>
                @endif

                @if($agencySupportUrl)
                    <a class="app-nav-card" href="{{ $agencySupportUrl }}">
                        <span class="app-nav-card__ico"><i class="fas fa-life-ring"></i></span>
                        <span class="app-nav-card__txt">{{ __('translate.Agency Support') }}</span>
                    </a>
                @endif

                @if($agencyProfileUrl)
                    <a class="app-nav-card" href="{{ $agencyProfileUrl }}">
                        <span class="app-nav-card__ico"><i class="fas fa-building"></i></span>
                        <span class="app-nav-card__txt">{{ __('translate.Agency Profile') }}</span>
                    </a>
                @endif

                @if($accountDeleteUrl)
                    <a class="app-nav-card app-nav-card--danger" href="{{ $accountDeleteUrl }}">
                        <span class="app-nav-card__ico"><i class="fas fa-trash"></i></span>
                        <span class="app-nav-card__txt">{{ __('translate.Account Delete') }}</span>
                    </a>
                @endif

                <a class="app-nav-card app-nav-card--danger" href="{{ route('user.logout') }}">
                    <span class="app-nav-card__ico"><i class="fas fa-sign-out-alt"></i></span>
                    <span class="app-nav-card__txt">{{ __('translate.Logout') }}</span>
                </a>
            </div>

            <div class="app-nav-sheet__footer">
                <a class="app-nav-wide" href="{{ $userDashboardUrl }}">
                    <i class="fas fa-exchange-alt"></i> {{ __('translate.User Dashboard') }}
                </a>
            </div>
        </div>
    </div>

    <nav class="app-bottom-bar" aria-label="Bottom Navigation">
        <a class="app-bottom-item {{ $isActiveDashboard ? 'is-active' : '' }}" href="{{ $agencyDashboardUrl }}">
            <span class="app-bottom-ico"><i class="fas fa-home"></i></span>
            <span class="app-bottom-txt">{{ __('translate.Dashboard') }}</span>
        </a>

        <a class="app-bottom-item {{ $isActiveBookings ? 'is-active' : '' }}" href="{{ $bookingsIndexUrl }}">
            <span class="app-bottom-ico"><i class="fas fa-receipt"></i></span>
            <span class="app-bottom-txt">{{ __('translate.Bookings') }}</span>
        </a>

        <a class="app-bottom-item {{ $isActiveClients ? 'is-active' : '' }}" href="{{ $clientsIndexUrl }}">
            <span class="app-bottom-ico"><i class="fas fa-users"></i></span>
            <span class="app-bottom-txt">{{ __('translate.Clients') }}</span>
        </a>

        <a class="app-bottom-item" href="{{ $homeUrl }}">
            <span class="app-bottom-ico"><i class="fas fa-globe"></i></span>
            <span class="app-bottom-txt">{{ __('translate.Home') }}</span>
        </a>

        <button type="button" class="app-bottom-item app-bottom-item--btn" data-app-sheet-open aria-controls="appNavSheet" aria-expanded="false">
            <span class="app-bottom-ico"><i class="fas fa-user-circle"></i></span>
            <span class="app-bottom-txt">{{ __('translate.My Profile') }}</span>
        </button>
    </nav>
</div>