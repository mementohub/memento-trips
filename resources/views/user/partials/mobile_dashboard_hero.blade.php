@php
    $auth_user = Auth::guard('web')->user();
@endphp

@once
<style>
  
  .md-hero, .md-hero *{ box-sizing:border-box !important; }

  .md-hero{
    background:#fff !important;
    border:1px solid rgba(17,24,39,.08) !important;
    border-radius:20px !important;
    box-shadow: 0 8px 30px rgba(17,24,39,.06) !important;
    padding:14px !important;
    margin-top:12px !important;
  }

  .md-hero__top{
    display:flex !important;
    align-items:center !important;
    justify-content:space-between !important;
    gap:12px !important;
  }

  .md-hero__kicker{
    font-weight:800 !important;
    color: rgba(17,24,39,.62) !important;
    font-size:13px !important;
    line-height:1.2 !important;
  }

  .md-hero__title{
    font-size:28px !important;
    font-weight:950 !important;
    line-height:1.05 !important;
    margin-top:4px !important;
    color: rgba(17,24,39,.92) !important;
  }

  .md-hero__right{
    display:flex !important;
    align-items:center !important;
    gap:10px !important;
    flex:0 0 auto !important;
  }

  .md-hero__iconbtn{
    width:44px !important;
    height:44px !important;
    border-radius:14px !important;
    border:1px solid rgba(17,24,39,.10) !important;
    background:#fff !important;
    display:grid !important;
    place-items:center !important;
    color: rgba(17,24,39,.85) !important;
    text-decoration:none !important;
  }

  .md-hero__avatar{
    width:44px !important;
    height:44px !important;
    border-radius:16px !important;
    overflow:hidden !important;
    border:2px solid rgba(255,66,0,.25) !important;
    display:block !important;
    flex:0 0 auto !important;
  }
  .md-hero__avatar img{
    width:100% !important;
    height:100% !important;
    object-fit:cover !important;
    display:block !important;
  }

  .md-hero__stats{
    display:grid !important;
    grid-template-columns: 1fr 1fr !important;
    gap:10px !important;
    margin-top:12px !important;
  }

  .md-stat{
    border:1px solid rgba(17,24,39,.08) !important;
    background: rgba(249,250,251, .95) !important;
    border-radius:16px !important;
    padding:12px !important;
    min-height:72px !important;
  }
  .md-stat__label{
    font-size:11px !important;
    font-weight:950 !important;
    letter-spacing:.5px !important;
    text-transform:uppercase !important;
    color: rgba(17,24,39,.55) !important;
  }
  .md-stat__value{
    margin-top:6px !important;
    font-size:18px !important;
    font-weight:950 !important;
    color: rgba(17,24,39,.92) !important;
    line-height:1.1 !important;
  }

  .md-hero__shortcuts{
    display:grid !important;
    grid-template-columns: 1fr 1fr !important;
    gap:10px !important;
    margin-top:12px !important;
  }

  .md-chip{
    border:1px solid rgba(17,24,39,.10) !important;
    background:#fff !important;
    border-radius:16px !important;
    padding:12px !important;
    display:flex !important;
    align-items:center !important;
    gap:10px !important;
    text-decoration:none !important;
    color: rgba(17,24,39,.92) !important;
    font-weight:950 !important;
  }

  .md-chip__ico{
    width:38px !important;
    height:38px !important;
    border-radius:14px !important;
    display:grid !important;
    place-items:center !important;
    background: rgba(255,66,0,.10) !important;
    color:#ff4200 !important;
    flex:0 0 auto !important;
    font-size:18px !important;
  }

  .md-chip__ico svg{
    width:20px !important;
    height:20px !important;
  }

  .md-chip--accent{
    border-color: rgba(255,66,0,.25) !important;
    box-shadow: 0 10px 24px rgba(255,66,0,.10) !important;
  }
</style>
@endonce

<div class="md-hero">
    <div class="md-hero__top">
        <div>
            <div class="md-hero__kicker">Hi, {{ $auth_user?->name ?? 'there' }} 
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
                <img
                    src="{{ $auth_user->image ? asset($auth_user->image) : asset($general_setting->default_avatar) }}"
                    alt="Profile"
                >
            </a>
        </div>
    </div>

    <div class="md-hero__stats">
        <div class="md-stat">
            <div class="md-stat__label">Total bookings</div>
            <div class="md-stat__value">{{ $total_booking ?? 0 }}</div>
        </div>
        <div class="md-stat">
            <div class="md-stat__label">Transactions</div>
            <div class="md-stat__value">{{ isset($total_transaction) ? currency($total_transaction) : '0.00 €' }}</div>
        </div>
        <div class="md-stat">
            <div class="md-stat__label">Support tickets</div>
            <div class="md-stat__value">{{ $support_tickets ?? 0 }}</div>
        </div>
        <div class="md-stat">
            <div class="md-stat__label">Wishlist</div>
            <div class="md-stat__value">{{ $wishlists ?? 0 }}</div>
        </div>
    </div>

    <div class="md-hero__shortcuts">
        <a class="md-chip" href="{{ route('user.bookings.index') }}">
            <span class="md-chip__ico">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="3" y="6" width="18" height="15" rx="2" stroke="currentColor" stroke-width="2"/>
                    <path d="M3 10h18M7 3v4M17 3v4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <circle cx="8.5" cy="14.5" r="0.5" fill="currentColor"/>
                    <circle cx="12" cy="14.5" r="0.5" fill="currentColor"/>
                    <circle cx="15.5" cy="14.5" r="0.5" fill="currentColor"/>
                    <circle cx="8.5" cy="17.5" r="0.5" fill="currentColor"/>
                    <circle cx="12" cy="17.5" r="0.5" fill="currentColor"/>
                    <circle cx="15.5" cy="17.5" r="0.5" fill="currentColor"/>
                </svg>
            </span>
            <span>Bookings</span>
        </a>

        <a class="md-chip" href="{{ route('user.wishlist.index') }}">
            <span class="md-chip__ico">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" fill="currentColor"/>
                </svg>
            </span>
            <span>Wishlist</span>
        </a>

        <a class="md-chip" href="{{ route('user.support-ticket.index') }}">
            <span class="md-chip__ico">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <circle cx="9" cy="10" r="1" fill="currentColor"/>
                    <circle cx="12" cy="10" r="1" fill="currentColor"/>
                    <circle cx="15" cy="10" r="1" fill="currentColor"/>
                </svg>
            </span>
            <span>Support</span>
        </a>

        @if(($auth_user?->instructor_joining_request ?? null) === 'approved')
            <a class="md-chip md-chip--accent" href="{{ route('agency.dashboard') }}">
                <span class="md-chip__ico">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z" fill="currentColor" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span>Agency</span>
            </a>
        @else
            <a class="md-chip md-chip--accent" href="{{ route('user.create-agency') }}">
                <span class="md-chip__ico">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z" fill="currentColor" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span>Agency</span>
            </a>
        @endif
    </div>
</div>