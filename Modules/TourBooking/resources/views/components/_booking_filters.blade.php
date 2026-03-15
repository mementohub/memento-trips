{{-- Booking Filters Partial --}}
{{-- Variables: $locations, $statuses, $showPaymentFilter, $paymentStatuses --}}
@php
    $currentSearch = request('search', '');
    $currentStatus = request('status', '');
    $currentPayment = request('payment_status', '');
    $currentDateFrom = request('date_from', '');
    $currentDateTo = request('date_to', '');
    $currentLocation = request('location', '');
    $hasActiveFilters = $currentStatus || $currentPayment || $currentDateFrom || $currentDateTo || $currentLocation;
@endphp

<div class="booking-filters-bar" id="bookingFiltersBar">
    <form method="GET" action="" class="booking-filters-form">
        {{-- Search + Filter toggle --}}
        <div class="bf-search-row">
            <div class="bf-search-wrap">
                <i class="fas fa-search bf-search-icon"></i>
                <input type="text" name="search" value="{{ $currentSearch }}"
                    class="bf-search-input"
                    placeholder="{{ __('translate.Search by name, code, location...') }}">
                @if($currentSearch)
                    <a href="{{ url()->current() }}{{ $hasActiveFilters ? '?' . http_build_query(array_filter(request()->except(['search','page']))) : '' }}"
                       class="bf-search-clear" title="Clear search">
                        <i class="fas fa-times"></i>
                    </a>
                @endif
            </div>
            <button type="submit" class="bf-search-btn">
                <i class="fas fa-search"></i>
                <span>{{ __('translate.Search') }}</span>
            </button>
            <button type="button" class="bf-filter-toggle {{ $hasActiveFilters ? 'bf-filter-toggle--active' : '' }}" id="bfFilterToggle">
                <i class="fas fa-sliders-h"></i>
                <span>{{ __('translate.Filters') }}</span>
                @if($hasActiveFilters)
                    <span class="bf-filter-badge">✓</span>
                @endif
            </button>
        </div>

        {{-- Collapsible filter dropdowns --}}
        <div class="bf-filters-panel" id="bfFiltersPanel" style="{{ $hasActiveFilters ? '' : 'display:none;' }}">
            <div class="bf-filters-grid">
                {{-- Status --}}
                <div class="bf-filter-group">
                    <label class="bf-filter-label">{{ __('translate.Status') }}</label>
                    <select name="status" class="bf-filter-select">
                        <option value="">{{ __('translate.All statuses') }}</option>
                        @foreach($statuses as $s)
                            <option value="{{ $s }}" {{ $currentStatus === $s ? 'selected' : '' }}>
                                {{ ucfirst($s) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Payment Status (admin only) --}}
                @if($showPaymentFilter ?? false)
                <div class="bf-filter-group">
                    <label class="bf-filter-label">{{ __('translate.Payment') }}</label>
                    <select name="payment_status" class="bf-filter-select">
                        <option value="">{{ __('translate.All payments') }}</option>
                        @foreach($paymentStatuses as $ps)
                            <option value="{{ $ps }}" {{ $currentPayment === $ps ? 'selected' : '' }}>
                                {{ ucfirst($ps) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                {{-- Location --}}
                <div class="bf-filter-group">
                    <label class="bf-filter-label">{{ __('translate.Location') }}</label>
                    <select name="location" class="bf-filter-select">
                        <option value="">{{ __('translate.All locations') }}</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc }}" {{ $currentLocation === $loc ? 'selected' : '' }}>
                                {{ $loc }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Date from --}}
                <div class="bf-filter-group">
                    <label class="bf-filter-label">{{ __('translate.From') }}</label>
                    <input type="date" name="date_from" value="{{ $currentDateFrom }}" class="bf-filter-select">
                </div>

                {{-- Date to --}}
                <div class="bf-filter-group">
                    <label class="bf-filter-label">{{ __('translate.To') }}</label>
                    <input type="date" name="date_to" value="{{ $currentDateTo }}" class="bf-filter-select">
                </div>
            </div>

            <div class="bf-filters-actions">
                <button type="submit" class="bf-apply-btn">
                    <i class="fas fa-check me-1"></i> {{ __('translate.Apply') }}
                </button>
                @if($hasActiveFilters)
                    <a href="{{ url()->current() }}{{ $currentSearch ? '?search=' . urlencode($currentSearch) : '' }}"
                       class="bf-clear-btn">
                        {{ __('translate.Clear filters') }}
                    </a>
                @endif
            </div>
        </div>
    </form>

    {{-- Active filter pills --}}
    @if($hasActiveFilters)
    <div class="bf-active-pills">
        @if($currentStatus)
            <a href="{{ request()->fullUrlWithQuery(['status' => null, 'page' => null]) }}" class="bf-pill">
                {{ ucfirst($currentStatus) }} <i class="fas fa-times"></i>
            </a>
        @endif
        @if($currentPayment)
            <a href="{{ request()->fullUrlWithQuery(['payment_status' => null, 'page' => null]) }}" class="bf-pill">
                {{ __('translate.Payment') }}: {{ ucfirst($currentPayment) }} <i class="fas fa-times"></i>
            </a>
        @endif
        @if($currentLocation)
            <a href="{{ request()->fullUrlWithQuery(['location' => null, 'page' => null]) }}" class="bf-pill">
                {{ $currentLocation }} <i class="fas fa-times"></i>
            </a>
        @endif
        @if($currentDateFrom)
            <a href="{{ request()->fullUrlWithQuery(['date_from' => null, 'page' => null]) }}" class="bf-pill">
                {{ __('translate.From') }}: {{ $currentDateFrom }} <i class="fas fa-times"></i>
            </a>
        @endif
        @if($currentDateTo)
            <a href="{{ request()->fullUrlWithQuery(['date_to' => null, 'page' => null]) }}" class="bf-pill">
                {{ __('translate.To') }}: {{ $currentDateTo }} <i class="fas fa-times"></i>
            </a>
        @endif
    </div>
    @endif
</div>

<style>
.booking-filters-bar { margin-bottom: 20px; }

.bf-search-row {
    display: flex !important;
    gap: 10px !important;
    align-items: center !important;
    height: 48px !important;
}
.bf-search-wrap {
    flex: 7 !important;
    min-width: 0;
    position: relative;
    display: flex;
    align-items: center;
    height: 48px !important;
}
.bf-search-icon {
    position: absolute;
    left: 14px;
    color: rgba(17,24,39,.4);
    font-size: 14px;
    pointer-events: none;
    z-index: 1;
    top: 50%;
    transform: translateY(-50%);
}
.bf-search-input {
    width: 100% !important;
    padding: 0 36px 0 42px !important;
    height: 48px !important;
    max-height: 48px !important;
    min-height: 48px !important;
    margin: 0 !important;
    box-sizing: border-box !important;
    border: 1px solid rgba(17,24,39,.12) !important;
    border-radius: 14px !important;
    font-size: 14px !important;
    font-weight: 600;
    background: #fff !important;
    color: rgba(17,24,39,.85);
    transition: border-color .2s, box-shadow .2s;
    line-height: 48px !important;
}
.bf-search-input:focus {
    outline: none;
    border-color: #ff4200 !important;
    box-shadow: 0 0 0 3px rgba(255,66,0,.08);
}
.bf-search-input::placeholder {
    color: rgba(17,24,39,.35);
    font-weight: 500;
    line-height: normal;
}
.bf-search-clear {
    position: absolute;
    right: 12px;
    color: rgba(17,24,39,.35);
    font-size: 13px;
    text-decoration: none;
}
.bf-search-clear:hover { color: #e53e3e; }

.bf-search-btn {
    flex: 1.5;
    padding: 0 16px;
    border: none;
    border-radius: 14px;
    background: #ff4200;
    color: #fff;
    font-weight: 800;
    font-size: 14px;
    cursor: pointer;
    transition: background .2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    white-space: nowrap;
}
.bf-search-btn:hover { background: #e03a00; }

.bf-filter-toggle {
    flex: 1.5;
    padding: 0 16px;
    border: 1px solid rgba(17,24,39,.12);
    border-radius: 14px;
    background: #fff;
    color: rgba(17,24,39,.7);
    font-weight: 800;
    font-size: 14px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all .2s;
    white-space: nowrap;
}
.bf-filter-toggle:hover { border-color: rgba(17,24,39,.25); color: rgba(17,24,39,.9); }
.bf-filter-toggle--active { border-color: #ff4200; color: #ff4200; background: rgba(255,66,0,.04); }
.bf-filter-badge {
    background: #ff4200;
    color: #fff;
    font-size: 10px;
    font-weight: 900;
    border-radius: 999px;
    width: 18px;
    height: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Filter panel */
.bf-filters-panel {
    margin-top: 14px;
    padding: 20px;
    background: #f8f9fb;
    border: 1px solid rgba(17,24,39,.06);
    border-radius: 16px;
    box-shadow: 0 2px 8px rgba(17,24,39,.04);
    transition: all .25s ease;
}

.bf-filters-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 16px;
}
.bf-filter-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.bf-filter-label {
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .6px;
    color: rgba(17,24,39,.45);
    margin-left: 2px;
}
.bf-filter-select {
    height: 44px !important;
    min-height: 44px !important;
    max-height: 44px !important;
    padding: 0 14px !important;
    margin: 0 !important;
    border: 1px solid rgba(17,24,39,.10) !important;
    border-radius: 10px !important;
    font-size: 13px !important;
    font-weight: 600 !important;
    background: #fff !important;
    color: rgba(17,24,39,.8) !important;
    box-sizing: border-box !important;
    transition: border-color .2s, box-shadow .2s;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    cursor: pointer;
}
select.bf-filter-select {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23999' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") !important;
    background-repeat: no-repeat !important;
    background-position: right 12px center !important;
    padding-right: 34px !important;
}
.bf-filter-select:hover {
    border-color: rgba(17,24,39,.2) !important;
}
.bf-filter-select:focus {
    outline: none !important;
    border-color: #ff4200 !important;
    box-shadow: 0 0 0 3px rgba(255,66,0,.06) !important;
}
input[type="date"].bf-filter-select {
    line-height: 44px !important;
}

.bf-filters-actions {
    margin-top: 18px;
    padding-top: 16px;
    border-top: 1px solid rgba(17,24,39,.06);
    display: flex;
    gap: 10px;
    align-items: center;
}
.bf-apply-btn {
    height: 42px;
    padding: 0 24px;
    border: none;
    border-radius: 10px;
    background: #ff4200;
    color: #fff;
    font-weight: 800;
    font-size: 13px;
    cursor: pointer;
    transition: background .2s, transform .15s;
    display: flex;
    align-items: center;
    gap: 6px;
}
.bf-apply-btn:hover { background: #e03a00; transform: translateY(-1px); }
.bf-apply-btn:active { transform: translateY(0); }
.bf-clear-btn {
    height: 42px;
    padding: 0 16px;
    border: none;
    border-radius: 10px;
    background: transparent;
    color: #e53e3e;
    font-weight: 700;
    font-size: 13px;
    text-decoration: none;
    transition: all .2s;
    display: flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
}
.bf-clear-btn:hover { color: #c53030; text-decoration: underline; }

/* Active pills */
.bf-active-pills {
    margin-top: 12px;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.bf-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    border-radius: 999px;
    background: rgba(255,66,0,.07);
    color: #ff4200;
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
    transition: all .2s;
}
.bf-pill:hover {
    background: rgba(255,66,0,.14);
    color: #cc3500;
}
.bf-pill i { font-size: 10px; opacity: .6; }

/* Mobile */
@media (max-width: 768px) {
    .bf-search-row { flex-wrap: wrap; height: auto !important; }
    .bf-search-wrap { flex: 1 1 100% !important; min-width: 0; height: 48px !important; }
    .bf-search-input { height: 48px !important; min-height: 48px !important; max-height: 48px !important; }
    .bf-filter-toggle { flex: 1 !important; justify-content: center; height: 44px !important; }
    .bf-search-btn { flex: 1 !important; height: 44px !important; }
    .bf-filters-grid { grid-template-columns: 1fr 1fr; }
    .bf-filters-panel { padding: 16px; }
}
@media (max-width: 480px) {
    .bf-filters-grid { grid-template-columns: 1fr; }
}

/* Pagination */
.bf-pagination {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    padding: 20px 0 8px;
}
.bf-pagination__info {
    font-size: 13px;
    font-weight: 600;
    color: rgba(17,24,39,.45);
}
.bf-pagination__links {
    display: flex;
    align-items: center;
    gap: 6px;
}
.bf-page {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 40px;
    height: 40px;
    padding: 0 12px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    border: 1px solid rgba(17,24,39,.1);
    background: #fff;
    color: rgba(17,24,39,.6);
    cursor: pointer;
    transition: all .2s;
}
.bf-page:hover {
    border-color: #ff4200;
    color: #ff4200;
    background: rgba(255,66,0,.03);
}
.bf-page--active {
    background: #ff4200 !important;
    color: #fff !important;
    border-color: #ff4200 !important;
}
.bf-page--disabled {
    opacity: .35;
    cursor: not-allowed;
    pointer-events: none;
}
.bf-page--dots {
    border: none;
    background: transparent;
    cursor: default;
    min-width: 24px;
    padding: 0;
    color: rgba(17,24,39,.35);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var toggle = document.getElementById('bfFilterToggle');
    var panel = document.getElementById('bfFiltersPanel');
    if (toggle && panel) {
        toggle.addEventListener('click', function() {
            var isHidden = panel.style.display === 'none';
            panel.style.display = isHidden ? 'block' : 'none';
            toggle.classList.toggle('bf-filter-toggle--active', isHidden);
        });
    }
});
</script>
