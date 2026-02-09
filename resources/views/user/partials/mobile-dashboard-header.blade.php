@php
    $u = Auth::guard('web')->user();
    $name = $u?->name ?: 'there';
    $firstName = trim(explode(' ', $name)[0] ?? $name);
    $avatar = $u?->image ? asset($u->image) : asset($general_setting->default_avatar);
@endphp
<div class="md-hero">
    <div class="md-hero__top">
        <div class="md-hero__left">
            <div class="md-hero__kicker">Hi, {{ strtolower($firstName) }} 
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="display:inline-block;vertical-align:middle;margin-left:2px;">
                    <path d="M7 3.516A9.004 9.004 0 0 1 21 12c0 4.97-4.03 9-9 9s-9-4.03-9-9c0-2.125.736-4.078 1.968-5.617" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <path d="M7.5 7.5c-.662 0-1.2.895-1.2 2s.538 2 1.2 2 1.2-.895 1.2-2-.538-2-1.2-2ZM16.5 7.5c-.662 0-1.2.895-1.2 2s.538 2 1.2 2 1.2-.895 1.2-2-.538-2-1.2-2Z" fill="currentColor"/>
                    <path d="M8.5 15.5s1.5 2 3.5 2 3.5-2 3.5-2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </div>
            <div class="md-hero__title">Dashboard</div>
        </div>
        <div class="md-hero__right">
            <a class="md-hero__iconbtn" href="{{ route('home') }}" target="_blank" aria-label="Open website">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <ellipse cx="12" cy="12" rx="4" ry="10" stroke="currentColor" stroke-width="1.6"/>
                    <path d="M21.9962 11.7205C20.1938 13.2016 16.3949 14.2222 12 14.2222C7.60511 14.2222 3.80619 13.2016 2.00383 11.7205M21.9962 11.7205C21.8482 6.32691 17.4294 2 12 2C6.57061 2 2.15183 6.32691 2.00383 11.7205M21.9962 11.7205C21.9987 11.8134 22 11.9065 22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 11.9065 2.00128 11.8134 2.00383 11.7205" stroke="currentColor" stroke-width="1.6"/>
                </svg>
            </a>
            <a class="md-hero__avatar" href="{{ route('user.edit-profile') }}" aria-label="My profile">
                <img src="{{ $avatar }}" alt="Profile">
            </a>
        </div>
    </div>
    <div class="md-hero__stats">
        <div class="md-stat">
            <div class="md-stat__label">Total bookings</div>
            <div class="md-stat__value">{{ $total_booking }}</div>
        </div>
        <div class="md-stat">
            <div class="md-stat__label">Transactions</div>
            <div class="md-stat__value">{{ currency($total_transaction) }}</div>
        </div>
        <div class="md-stat">
            <div class="md-stat__label">Support tickets</div>
            <div class="md-stat__value">{{ $support_tickets }}</div>
        </div>
        <div class="md-stat">
            <div class="md-stat__label">Wishlist</div>
            <div class="md-stat__value">{{ $wishlists }}</div>
        </div>
    </div>
    <div class="md-hero__shortcuts">
        <a class="md-chip" href="{{ route('user.bookings.index') }}">
            <span class="md-chip__ico">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M4 6h16M4 6c0-1.1.9-2 2-2h12c1.1 0 2 .9 2 2M4 6v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V6" stroke="currentColor" stroke-width="2"/>
                    <path d="M9 4v4M15 4v4M7 12h10M7 16h6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </span>
            <span class="md-chip__txt">Bookings</span>
        </a>
        <a class="md-chip" href="{{ route('user.wishlist.index') }}">
            <span class="md-chip__ico">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" fill="currentColor"/>
                </svg>
            </span>
            <span class="md-chip__txt">Wishlist</span>
        </a>
        <a class="md-chip" href="{{ route('user.support-ticket.index') }}">
            <span class="md-chip__ico">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z" stroke="currentColor" stroke-width="2"/>
                    <path d="M12 6v6M9 10.5c0-1.1.9-2 2-2h2c1.1 0 2 .9 2 2 0 .8-.5 1.5-1.2 1.8L12 13v1" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <path d="M8 15.5c0 1.1.9 2 2 2h4c1.1 0 2-.9 2-2v-1c0-.6-.4-1-1-1h-6c-.6 0-1 .4-1 1v1z" fill="currentColor"/>
                    <circle cx="12" cy="12" r="1.5" fill="currentColor" opacity="0.3"/>
                </svg>
            </span>
            <span class="md-chip__txt">Support</span>
        </a>
        <a class="md-chip md-chip--accent" href="{{ route('user.agency-support.index') }}">
            <span class="md-chip__ico">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z" fill="currentColor" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
            <span class="md-chip__txt">Agency</span>
        </a>
    </div>
</div>