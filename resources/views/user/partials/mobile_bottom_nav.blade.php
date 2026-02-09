@php
    $auth_user = Auth::guard('web')->user();
    $isAgencyApproved = $auth_user && $auth_user->instructor_joining_request === 'approved';

    $isActiveDashboard = request()->routeIs('user.dashboard');
    $isActiveBookings  = request()->routeIs('user.bookings.*');
    $isActiveWishlist  = request()->routeIs('user.wishlist.*');
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
                <a class="app-nav-card" href="{{ route('user.edit-profile') }}">
                    <span class="app-nav-card__ico"><i class="fas fa-user"></i></span>
                    <span class="app-nav-card__txt">{{ __('translate.Edit Profile') }}</span>
                </a>

                <a class="app-nav-card" href="{{ route('user.change-password') }}">
                    <span class="app-nav-card__ico"><i class="fas fa-lock') }}"></i></span>
                    <span class="app-nav-card__txt">{{ __('translate.Change Password') }}</span>
                </a>

                <a class="app-nav-card" href="{{ route('user.support-ticket.index') }}">
                    <span class="app-nav-card__ico"><i class="fas fa-headset"></i></span>
                    <span class="app-nav-card__txt">{{ __('translate.Support Ticket') }}</span>
                </a>

                <a class="app-nav-card" href="{{ route('user.agency-support.index') }}">
                    <span class="app-nav-card__ico"><i class="fas fa-life-ring"></i></span>
                    <span class="app-nav-card__txt">{{ __('translate.Agency Support') }}</span>
                </a>

                

                <a class="app-nav-card app-nav-card--danger" href="{{ route('user.account-delete') }}">
                    <span class="app-nav-card__ico"><i class="fas fa-trash"></i></span>
                    <span class="app-nav-card__txt">{{ __('translate.Account Delete') }}</span>
                </a>

                <a class="app-nav-card app-nav-card--danger" href="{{ route('user.logout') }}">
                    <span class="app-nav-card__ico"><i class="fas fa-sign-out-alt"></i></span>
                    <span class="app-nav-card__txt">{{ __('translate.Logout') }}</span>
                </a>
            </div>

            <div class="app-nav-sheet__footer">
                @if ($isAgencyApproved)
                    <a class="app-nav-wide" href="{{ route('agency.dashboard') }}">
                        <i class="fas fa-exchange-alt"></i> {{ __('translate.Agency Dashboard') }}
                    </a>
                @else
                    <a class="app-nav-wide" href="{{ route('user.create-agency') }}">
                        <i class="fas fa-plus-circle"></i> {{ __('translate.Create a agency') }}
                    </a>
                @endif
            </div>
        </div>
    </div>

    <nav class="app-bottom-bar" aria-label="Bottom Navigation">
        <a class="app-bottom-item {{ $isActiveDashboard ? 'is-active' : '' }}" href="{{ route('user.dashboard') }}">
            <span class="app-bottom-ico"><i class="fas fa-home"></i></span>
            <span class="app-bottom-txt">{{ __('translate.Dashboard') }}</span>
        </a>

        <a class="app-bottom-item {{ $isActiveBookings ? 'is-active' : '' }}" href="{{ route('user.bookings.index') }}">
            <span class="app-bottom-ico"><i class="fas fa-receipt"></i></span>
            <span class="app-bottom-txt">{{ __('translate.Bookings') }}</span>
        </a>

        <a class="app-bottom-item {{ $isActiveWishlist ? 'is-active' : '' }}" href="{{ route('user.wishlist.index') }}">
            <span class="app-bottom-ico"><i class="fas fa-heart"></i></span>
            <span class="app-bottom-txt">{{ __('translate.Wishlist') }}</span>
        </a>

        <a class="app-bottom-item" href="{{ route('home') }}">
            <span class="app-bottom-ico"><i class="fas fa-globe"></i></span>
            <span class="app-bottom-txt">{{ __('translate.Home') }}</span>
        </a>

        <button type="button" class="app-bottom-item app-bottom-item--btn" data-app-sheet-open aria-controls="appNavSheet" aria-expanded="false">
            <span class="app-bottom-ico"><i class="fas fa-user-circle"></i></span>
            <span class="app-bottom-txt">{{ __('translate.My Profile') }}</span>
        </button>
    </nav>
</div>