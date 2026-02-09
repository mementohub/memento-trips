<li class="pt-item {{ Route::is('seller.my-withdraw.*') ? 'is-active' : '' }}">
    <a class="pt-link" href="{{ route('seller.my-withdraw.index') }}">
        <span class="pt-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                stroke-linecap="round" stroke-linejoin="round">
                <path
                    d="M21 11C21.8626 10.7762 22 9.98695 22 9.04763V5.01588C22 3.90254 21.1046 3 20 3H4C2.89543 3 2 3.90254 2 5.01588V9.04763C2 9.98695 2.13739 10.7762 3 11" />
                <path
                    d="M12 21C15.866 21 19 17.866 19 14C19 10.134 15.866 7 12 7C8.13401 7 5 10.134 5 14C5 17.866 8.13401 21 12 21Z" />
                <path
                    d="M12 11C10.8954 11 10 11.6716 10 12.5C10 13.3284 10.8954 14 12 14C13.1046 14 14 14.6716 14 15.5C14 16.3284 13.1046 17 12 17M12 11C12.8708 11 13.6116 11.4174 13.8862 12M12 11V10M12 17C11.1292 17 10.3884 16.5826 10.1138 16M12 17V18" />
                <path d="M5 7H19" />
            </svg>
        </span>
        <span class="pt-text">{{ __('translate.My Withdrawal') }}</span>
    </a>
</li>