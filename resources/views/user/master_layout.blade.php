<!DOCTYPE html>
<html class="no-js" lang="zxx">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  @yield('title')
  <link rel="icon" href="{{ asset($general_setting->favicon) }}">

  <link rel="stylesheet" href="{{ asset('backend/css/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('global/datatable/dataTables.bootstrap4.min.css') }}">
  <link rel="stylesheet" href="{{ asset('backend/css/slick.min.css') }}">
  <link rel="stylesheet" href="{{ asset('backend/css/font-awesome-all.min.css') }}">
  <link rel="stylesheet" href="{{ asset('backend/css/nice-select.min.css') }}">
  <link rel="stylesheet" href="{{ asset('backend/css/reset.css') }}">
  <link rel="stylesheet" href="{{ asset('backend/css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('backend/css/enrollment.css') }}">
  <link rel="stylesheet" href="{{ asset('backend/css/overview.css') }}">
  <link rel="stylesheet" href="{{ asset('backend/css/dev.css') }}">
  <link rel="stylesheet" href="{{ asset('global/toastr/toastr.min.css') }}">
  <link rel="stylesheet" href="{{ asset('backend/css/custom.css') }}">

  <style>
    :root {
      --pt-sb-open: 300px;
      --sbw: var(--pt-sb-open);
      --pt-brand: var(--theme-color, #ff4200);
      --pt-ink: #2a2f3a;
      --pt-bg: #fff;
      --pt-sep: #eef0f4;
    }

    body.sb-closed {
      --sbw: 0px;
    }

    /* ===== Sidebar ===== */
    .crancy-smenu {
      position: fixed;
      inset: 0 auto 0 0;
      width: var(--sbw);
      overflow: hidden;
      transition: width .22s ease;
      z-index: 1040;
      background: #fff;
      border-right: 1px solid var(--pt-sep);
      transform: none !important;
    }

    body.sb-closed .crancy-smenu {
      border-right: 0;
    }

    /* ===== Header ===== */
    .pt-header {
      position: sticky;
      top: 0;
      z-index: 1020;
      background: var(--pt-bg);
      border-bottom: 1px solid var(--pt-sep);
      margin-left: var(--sbw);
      width: calc(100% - var(--sbw));
      transition: margin-left .22s ease, width .22s ease;
    }

    .pt-header>.container {
      max-width: 100% !important;
      padding-left: 0 !important;
      padding-right: 0 !important;
    }

    .pt-headbar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
      padding: 10px 16px;
    }

    .pt-left {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .pt-title {
      font-weight: 700;
      color: var(--pt-ink);
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    #ptHeaderToggle {
      display: inline-grid;
      place-items: center;
      width: 42px;
      height: 42px;
      border-radius: 12px;
      border: 1px solid var(--pt-sep);
      background: #fff;
      box-shadow: 0 1px 0 rgba(0, 0, 0, .02);
      cursor: pointer;
      transition: .2s;
    }

    #ptHeaderToggle:hover {
      background: #f9f8ff;
    }

    #ptHeaderToggle svg {
      width: 18px;
      height: 18px;
      color: var(--pt-brand);
      transition: transform .2s;
    }

    body.sb-closed #ptHeaderToggle svg {
      transform: rotate(180deg);
    }

    .pt-actions {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .pt-cluster {
      display: flex;
      align-items: center;
      gap: 6px;
      padding: 4px;
      border: 1px solid var(--pt-sep);
      background: #fff;
      border-radius: 14px;
      box-shadow: 0 1px 0 rgba(0, 0, 0, .02);
    }

    .pt-ctl {
      display: inline-grid;
      place-items: center;
      width: 38px;
      height: 38px;
      border-radius: 10px;
      border: 1px solid transparent;
      transition: .15s;
      background: #fafbff;
    }

    .pt-ctl:hover {
      border-color: #ffd7c6;
      background: #fff;
    }

    .pt-ctl svg {
      width: 20px;
      height: 20px;
      color: var(--pt-brand);
    }

    .pt-sep-v {
      width: 1px;
      height: 24px;
      background: var(--pt-sep);
      margin: 0 2px;
      border-radius: 1px;
    }

    .pt-account {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .pt-avatar {
      width: 38px;
      height: 38px;
      border-radius: 50%;
      overflow: hidden;
      border: 2px solid var(--pt-sep);
      box-shadow: 0 1px 0 rgba(0, 0, 0, .02);
    }

    .pt-avatar img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }

    .crancy-body-area {
      margin-left: 0 !important;
      padding-left: 0 !important;
    }

    #ptPage {
      margin-left: var(--sbw);
      transition: margin-left .22s ease;
    }

    #ptPage,
    #ptPage .crancy-main,
    #ptPage .crancy-content,
    #ptPage .crancy-header__inner,
    #ptPage .crancy-header__middle,
    #ptPage .crancy-main__content,
    #ptPage .crancy-wrapper {
      padding-left: 0 !important;
      margin-left: 0 !important;
      border-left: 0 !important;
      box-shadow: none !important;
    }

    #ptPage .container,
    #ptPage .container-fluid {
      max-width: 100% !important;
      padding-left: 0 !important;
      padding-right: 0 !important;
      margin-left: 0 !important;
    }

    #ptPage .row {
      margin-left: 0 !important;
    }

    .pt-normalize>*:first-child {
      margin-top: 0 !important;
    }

    /* sb-closed state */
    body.sb-closed .crancy-smenu {
      width: 0 !important;
      border-right: 0 !important;
      box-shadow: none !important;
      pointer-events: none !important;
    }

    body.sb-closed .pt-header {
      margin-left: 0 !important;
      width: 100% !important;
    }

    body.sb-closed #ptPage {
      margin-left: 0 !important;
    }

    body.sb-closed #ptPage .container,
    body.sb-closed #ptPage .container-fluid,
    body.sb-closed #ptPage .crancy-main,
    body.sb-closed #ptPage .crancy-content,
    body.sb-closed #ptPage .crancy-wrapper {
      padding-left: 0 !important;
      margin-left: 0 !important;
      border-left: 0 !important;
    }

    body.sb-closed #ptPage,
    body.sb-closed #ptPage .crancy-header__inner,
    body.sb-closed #ptPage .crancy-header__middle {
      padding-left: 0 !important;
      margin-left: 0 !important;
      border-left: 0 !important;
    }

    body.sb-closed #ptPage>*,
    body.sb-closed #ptPage>*>* {
      padding-left: 0 !important;
      margin-left: 0 !important;
      border-left: 0 !important;
    }

    html,
    body {
      overflow-x: hidden;
    }

    @media (max-width: 992px) {
      body {
        --sbw: 0px;
      }
    }
  </style>

  {{-- Mobile app mode styles --}}
  <style>
    @media (max-width: 991.98px) {
      :root {
        --app-accent: #ff4200;
        --app-text: #0f172a;
        --app-muted: #64748b;
        --app-bg: #fff;
        --app-border: rgba(15, 23, 42, .10);
        --app-shadow: 0 -12px 30px rgba(15, 23, 42, .10);
        --app-radius: 18px;
        --app-bar-h: 68px;
      }

      header.pt-header {
        display: none !important;
      }

      #CrancySidebar {
        display: none !important;
      }

      .crancy-body-area {
        padding-left: 0 !important;
        margin-left: 0 !important;
        padding-bottom: calc(var(--app-bar-h) + env(safe-area-inset-bottom, 0px) + 12px) !important;
      }

      #ptPage {
        margin-left: 0 !important;
      }

      .app-bottom-bar {
        position: fixed;
        left: 0;
        right: 0;
        bottom: 0;
        height: calc(var(--app-bar-h) + env(safe-area-inset-bottom, 0px));
        padding: 10px 10px calc(10px + env(safe-area-inset-bottom, 0px));
        background: var(--app-bg);
        border-top: 1px solid var(--app-border);
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 6px;
        z-index: 9999;
      }

      .app-bottom-item {
        border: 0;
        background: transparent;
        width: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 6px;
        text-decoration: none !important;
        color: var(--app-muted) !important;
        font-weight: 700;
        padding: 8px 6px;
        border-radius: 14px;
        line-height: 1;
      }

      .app-bottom-item--btn {
        cursor: pointer;
      }

      .app-bottom-ico {
        width: 36px;
        height: 26px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: currentColor;
      }

      .app-bottom-txt {
        font-size: 11px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
      }

      .app-bottom-item.is-active {
        color: var(--app-accent) !important;
        background: rgba(255, 66, 0, .08);
      }

      .app-nav-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, .35);
        opacity: 0;
        pointer-events: none;
        transition: opacity .18s ease;
        z-index: 9997;
      }

      .app-nav-sheet {
        position: fixed;
        left: 12px;
        right: 12px;
        bottom: calc(var(--app-bar-h) + env(safe-area-inset-bottom, 0px) + 10px);
        background: var(--app-bg);
        border: 1px solid var(--app-border);
        box-shadow: var(--app-shadow);
        border-radius: var(--app-radius);
        transform: translateY(18px);
        opacity: 0;
        pointer-events: none;
        transition: transform .18s ease, opacity .18s ease;
        z-index: 9998;
        max-height: min(62vh, 520px);
        overflow: hidden;
      }

      .app-nav-sheet__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 14px 10px;
        border-bottom: 1px solid var(--app-border);
      }

      .app-nav-user {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
      }

      .app-nav-user__avatar {
        width: 44px;
        height: 44px;
        border-radius: 999px;
        overflow: hidden;
        flex: 0 0 auto;
        border: 1px solid var(--app-border);
        background: #f8fafc;
      }

      .app-nav-user__avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
      }

      .app-nav-user__meta {
        min-width: 0;
      }

      .app-nav-user__name {
        font-size: 14px;
        font-weight: 800;
        color: var(--app-text);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
      }

      .app-nav-user__email {
        font-size: 12px;
        color: var(--app-muted);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
      }

      .app-nav-sheet__close {
        width: 40px;
        height: 40px;
        border-radius: 14px;
        border: 1px solid var(--app-border);
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        line-height: 1;
        color: var(--app-text);
      }

      .app-nav-sheet__content {
        padding: 12px;
        overflow: auto;
        -webkit-overflow-scrolling: touch;
      }

      .app-nav-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
      }

      .app-nav-card {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px;
        border-radius: 14px;
        border: 1px solid var(--app-border);
        background: #fff;
        text-decoration: none !important;
        color: var(--app-text) !important;
        font-weight: 700;
        min-height: 48px;
      }

      .app-nav-card__ico {
        width: 36px;
        height: 36px;
        border-radius: 12px;
        background: rgba(255, 66, 0, .10);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--app-accent);
        flex: 0 0 auto;
      }

      .app-nav-card__txt {
        font-size: 13px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
      }

      .app-nav-card--danger .app-nav-card__ico {
        background: rgba(220, 38, 38, .10);
        color: #dc2626;
      }

      .app-nav-sheet__footer {
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px solid var(--app-border);
      }

      .app-nav-wide {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        padding: 12px;
        border-radius: 14px;
        border: 1px solid var(--app-border);
        background: rgba(255, 66, 0, .08);
        color: var(--app-accent) !important;
        font-weight: 800;
        text-decoration: none !important;
      }

      body.app-nav-open .app-nav-backdrop {
        opacity: 1;
        pointer-events: auto;
      }

      body.app-nav-open .app-nav-sheet {
        opacity: 1;
        pointer-events: auto;
        transform: translateY(0);
      }
    }

    .app-bottom-bar {
      padding: 8px 8px calc(8px + env(safe-area-inset-bottom, 0px));
      gap: 4px;
    }

    .app-bottom-item {
      padding: 6px 6px;
      gap: 4px;
      border-radius: 12px;
    }

    .app-bottom-ico {
      width: 32px;
      height: 24px;
      font-size: 19px;
    }

    .app-bottom-txt {
      font-size: 10.5px;
      letter-spacing: .1px;
    }

    .app-bottom-item.is-active {
      background: rgba(255, 66, 0, .06);
    }

    .app-bottom-item,
    .app-bottom-item--btn {
      min-height: 48px;
    }
  </style>

  @stack('style_section')
</head>

<body id="crancy-dark-light">

  @php
  $auth_user = Auth::guard('web')->user();
  $avatarUrl = $auth_user?->image ? asset($auth_user->image) : asset($general_setting->default_avatar);
  @endphp

  <div class="crancy-body-area">

    <!-- ===== Sidebar ===== -->
    <div class="crancy-smenu" id="CrancySidebar">
      <aside class="pt-sidebar" id="userSidebar" style="border-right:0;height:100%;overflow-y:auto;">
        <div
          style="height:72px;box-sizing:border-box;padding:0 16px !important;min-height:auto !important;display:flex !important;align-items:center !important;justify-content:center !important;border-bottom:1px solid var(--pt-sep) !important;">
          <a class="crancy-logo" href="{{ route('user.dashboard') }}"
            style="display:flex;align-items:center;justify-content:center;width:100%;">
            <img src="{{ asset($general_setting->secondary_logo) }}" alt="logo"
              style="width:100%;height:42px;object-fit:contain;">
          </a>
        </div>
        @include('user.sidebar')
        <div class="pt-bottom">{{ __('translate.Version') }} : {{ $general_setting->app_version }}</div>
      </aside>
    </div>

    <!-- ===== Header ===== -->
    <header class="pt-header">
      <div class="container">
        <div class="pt-headbar">
          <div class="pt-left">
            <button id="ptHeaderToggle" type="button" aria-label="Toggle sidebar" aria-pressed="false"
              title="Toggle sidebar">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M15 6l-6 6 6 6" />
              </svg>
            </button>
            <div class="pt-title">@yield('body-header')</div>
          </div>

          <div class="pt-actions">
            {{-- Switch to Agency / Create Agency --}}
            @if ($auth_user->instructor_joining_request == 'approved')
            <div class="profile-switcher-wrapper d-md-flex d-none gap-3 align-items-center">
              <p class="text">{{ __('translate.Switch to Agency Dashboard') }}</p>
              <label class="custom-switch switch_to_instructor">
                <input type="checkbox">
                <span class="slider"></span>
              </label>
            </div>
            @else
            <div class="profile-switcher-wrapper d-md-flex d-none gap-3 align-items-center">
              <p class="text">{{ __('translate.Create a agency') }}</p>
              <label class="custom-switch join_as_instructor">
                <input type="checkbox" {{ Route::is('user.create-agency') ? 'checked' : '' }}>
                <span class="slider"></span>
              </label>
            </div>
            @endif

            <div class="pt-cluster">
              <a target="_blank" class="pt-ctl" href="{{ route('home') }}" title="Open site">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                  <circle cx="12" cy="12" r="10"></circle>
                  <path d="M2 12h20M12 2a15 15 0 0 1 0 20M12 2a15 15 0 0 0 0 20"></path>
                </svg>
              </a>
            </div>

            <div class="pt-account">
              <a href="{{ route('user.edit-profile') }}" class="pt-avatar">
                <img src="{{ $avatarUrl }}" alt="#">
              </a>
              <div class="crancy-dropdown crancy-dropdown--acount">
                <div class="crancy-dropdown__hover--inner">
                  <ul class="crancy-dmenu">
                    <li>
                      <a href="{{ route('user.edit-profile') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                          <path
                            d="M12.1202 12.78C12.0502 12.77 11.9602 12.77 11.8802 12.78C10.1202 12.72 8.72021 11.28 8.72021 9.50998C8.72021 7.69998 10.1802 6.22998 12.0002 6.22998C13.8102 6.22998 15.2802 7.69998 15.2802 9.50998C15.2702 11.28 13.8802 12.72 12.1202 12.78Z"
                            stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                          <path
                            d="M18.7398 19.3801C16.9598 21.0101 14.5998 22.0001 11.9998 22.0001C9.39977 22.0001 7.03977 21.0101 5.25977 19.3801C5.35977 18.4401 5.95977 17.5201 7.02977 16.8001C9.76977 14.9801 14.2498 14.9801 16.9698 16.8001C18.0398 17.5201 18.6398 18.4401 18.7398 19.3801Z"
                            stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                          <path
                            d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z"
                            stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        {{ __('translate.My Profile') }}
                      </a>
                    </li>
                    <li>
                      <a href="{{ route('user.logout') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                          <path
                            d="M15 10L13.7071 11.2929C13.3166 11.6834 13.3166 12.3166 13.7071 12.7071L15 14M14 12L22 12M6 20C3.79086 20 2 18.2091 2 16V8C2 5.79086 3.79086 4 6 4M6 20C8.20914 20 10 18.2091 10 16V8C10 5.79086 8.20914 4 6 4M6 20H14C16.2091 20 18 18.2091 18 16M6 4H14C16.2091 4 18 5.79086 18 8"
                            stroke-width="1.5" stroke-linecap="round" />
                        </svg>
                        {{ __('translate.Logout') }}
                      </a>
                    </li>
                  </ul>
                </div>
              </div>
            </div><!-- /.pt-account -->
          </div><!-- /.pt-actions -->
        </div><!-- /.pt-headbar -->
      </div>
    </header>

    <!-- ===== Content ===== -->
    <main id="ptPage" class="pt-normalize">
      @yield('body-content')
    </main>

    {{-- MOBILE APP NAV (bottom bar + drop-up) --}}
    @include('user.partials.mobile_bottom_nav')

  </div>

  <!-- JS -->
  <script src="{{ asset('global/js/jquery-3.7.1.min.js') }}"></script>
  <script src="{{ asset('global/datatable/jquery.dataTables.min.js') }}"></script>
  <script src="{{ asset('global/datatable/dataTables.bootstrap4.min.js') }}"></script>
  <script src="{{ asset('backend/js/jquery-migrate.js') }}"></script>
  <script src="{{ asset('backend/js/popper.min.js') }}"></script>
  <script src="{{ asset('backend/js/bootstrap.min.js') }}"></script>
  <script src="{{ asset('backend/js/nice-select.min.js') }}"></script>
  <script src="{{ asset('backend/js/main.js') }}"></script>
  <script src="{{ asset('global/toastr/toastr.min.js') }}"></script>

  <script>
    (function ($) {
      "use strict";

      const body = document.body;
      const btn = document.getElementById('ptHeaderToggle');

      // purge legacy sidebar classes
      (function purgeLegacy() {
        ['sidebar-close', 'sidebar-collapsed', 'menu-close', 'menu-collapsed', 'crancy-close', 'crancy-menu-close', 'admin-menu-close']
          .forEach(c => body.classList.remove(c));
        const sb = document.getElementById('CrancySidebar');
        if (sb) {
          sb.style.removeProperty('width');
          sb.style.removeProperty('transform');
          sb.style.removeProperty('display');
        }
      })();

      // restore saved state
      try {
        const savedClosed = localStorage.getItem('sb-closed') === '1';
        body.classList.toggle('sb-closed', savedClosed);
        if (btn) btn.setAttribute('aria-pressed', savedClosed ? 'true' : 'false');
      } catch (e) { }

      function toggleSidebar() {
        body.classList.toggle('sb-closed');
        const closed = body.classList.contains('sb-closed');
        try { localStorage.setItem('sb-closed', closed ? '1' : '0'); } catch (e) { }
        if (btn) btn.setAttribute('aria-pressed', closed ? 'true' : 'false');
      }

      if (btn) {
        btn.addEventListener('click', toggleSidebar);
        btn.addEventListener('keydown', (ev) => { if (ev.key === 'Enter' || ev.key === ' ') { ev.preventDefault(); toggleSidebar(); } });
      }

      // Toastr notifications
      $(document).ready(function () {
        const session_notify_message = @json(Session:: get('message'));
        if (session_notify_message != null) {
          const session_notify_type = @json(Session:: get('alert-type', 'info'));
          switch (session_notify_type) {
            case 'info': toastr.info(session_notify_message); break;
            case 'success': toastr.success(session_notify_message); break;
            case 'warning': toastr.warning(session_notify_message); break;
            case 'error': toastr.error(session_notify_message); break;
          }
        }
        const validation_errors = @json($errors -> all());
        if (validation_errors.length > 0) validation_errors.forEach(e => toastr.error(e));

        if ($('#dataTable').length) {
          if (!$.fn.DataTable.isDataTable('#dataTable')) {
            $('#dataTable').DataTable();
          }
        }

        $(".switch_to_instructor").on("change", function () {
          window.location = `{{ route('agency.dashboard') }}`
        });

        $(".join_as_instructor").on("change", function () {
          window.location = `{{ route('user.create-agency') }}`
        });
      });
    })(jQuery);
  </script>

  <script>
    // Mobile bottom sheet open/close
    (function () {
      function openSheet() {
        document.body.classList.add('app-nav-open');
        const btn = document.querySelector('[data-app-sheet-open]');
        if (btn) btn.setAttribute('aria-expanded', 'true');
        const sheet = document.getElementById('appNavSheet');
        if (sheet) sheet.setAttribute('aria-hidden', 'false');
      }
      function closeSheet() {
        document.body.classList.remove('app-nav-open');
        const btn = document.querySelector('[data-app-sheet-open]');
        if (btn) btn.setAttribute('aria-expanded', 'false');
        const sheet = document.getElementById('appNavSheet');
        if (sheet) sheet.setAttribute('aria-hidden', 'true');
      }

      document.addEventListener('click', function (e) {
        const openBtn = e.target.closest('[data-app-sheet-open]');
        const closeBtn = e.target.closest('[data-app-sheet-close]');
        const linkInSheet = e.target.closest('.app-nav-sheet a');

        if (openBtn) { e.preventDefault(); document.body.classList.contains('app-nav-open') ? closeSheet() : openSheet(); }
        if (closeBtn) { e.preventDefault(); closeSheet(); }
        if (linkInSheet) { closeSheet(); }
      });

      document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeSheet();
      });
    })();
  </script>

  @stack('js_section')
</body>

</html>