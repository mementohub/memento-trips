{{-- resources/views/agency/clients/index.blade.php --}}

@extends('agency.master_layout')

@section('title')
    <title>{{ __('translate.Clients') }}</title>
@endsection

@section('body-header')
    {{-- Desktop header clasic --}}
    <div class="d-none d-md-block">
        <h3 class="crancy-header__title m-0">{{ __('translate.Clients') }}</h3>
        <p class="crancy-header__text">{{ __('translate.Clients') }} >> {{ __('translate.Clients') }}</p>
    </div>
@endsection

@section('body-content')
    <section class="crancy-adashboard crancy-show clients-v2">
        <div class="container container__bscreen">

            {{-- Mobile head --}}
            <div class="d-md-none clients-mobile-head">
                <div class="clients-mobile-kicker">{{ __('translate.Dashboard') }}</div>
                <div class="clients-mobile-title">{{ __('translate.Clients') }}</div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="crancy-body">
                        <div class="crancy-dsinner">
                            <div class="crancy-table crancy-table--v3 mg-top-30 clients-wrap">

                                {{-- top bar --}}
                                <div class="dash-section-head">
                                    <div>
                                        <h4 class="dash-section-title">{{ __('translate.Clients') }}</h4>
                                        <p class="dash-section-sub">{{ __('translate.Search and manage clients fast.') }}</p>
                                    </div>

                                    <a class="dash-add-btn" href="{{ route('agency.clients.create') }}">
                                        <i class="fas fa-plus"></i>
                                        <span>{{ __('translate.Add Client') }}</span>
                                    </a>
                                </div>

                                {{-- flash (optional) --}}
                                @if (session('message'))
                                    <div class="clients-alert">
                                        {{ session('message') }}
                                    </div>
                                @endif

                                {{-- Filters --}}
                                <form method="GET" class="clients-filter">
                                    <div class="clients-filter__grid">
                                        <div class="clients-filter__search">
                                            <input
                                                type="text"
                                                name="q"
                                                value="{{ request('q') }}"
                                                class="clients-input"
                                                placeholder="{{ __('translate.Search') }}: name / email / phone"
                                            >
                                        </div>

                                        <button class="clients-btn clients-btn--ghost" type="submit">
                                            <i class="fas fa-search"></i>
                                            <span>{{ __('translate.Search') ?? 'Search' }}</span>
                                        </button>

                                        <a class="clients-btn clients-btn--light" href="{{ route('agency.clients.index') }}">
                                            <i class="fas fa-undo"></i>
                                            <span>Reset</span>
                                        </a>
                                    </div>
                                </form>

                                {{-- Cards container --}}
                                <div id="clientCards" class="client-cards"></div>

                                {{-- Table (hidden on mobile, used for desktop + pagination if you want) --}}
                                <div class="table-responsive d-none d-md-block">
                                    <table class="table table-borderless align-middle clients-table">
                                        <thead>
                                            <tr>
                                                <th style="width:80px;">#</th>
                                                <th>{{ __('translate.Name') }}</th>
                                                <th>{{ __('translate.Email') }}</th>
                                                <th>{{ __('translate.Phone') }}</th>
                                                <th class="text-end">{{ __('translate.Actions') }}</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @forelse($clients as $client)
                                                @php
                                                    $name = $client->full_name ?? trim(($client->first_name ?? '').' '.($client->last_name ?? ''));
                                                    $email = $client->email ?? '';
                                                    $phone = $client->phone ?? '';
                                                    $editUrl = route('agency.clients.edit', $client);
                                                    $deleteUrl = route('agency.clients.destroy', $client);
                                                @endphp
                                                <tr
                                                    data-id="{{ e($client->id) }}"
                                                    data-name="{{ e($name) }}"
                                                    data-email="{{ e($email) }}"
                                                    data-phone="{{ e($phone) }}"
                                                    data-edit="{{ e($editUrl) }}"
                                                    data-delete="{{ e($deleteUrl) }}"
                                                >
                                                    <td>{{ $client->id }}</td>
                                                    <td>{{ $name }}</td>
                                                    <td>{{ $email }}</td>
                                                    <td>{{ $phone }}</td>
                                                    <td class="text-end">
                                                        <a class="btn btn-sm btn-outline-secondary" href="{{ $editUrl }}">
                                                            {{ __('translate.Edit') }}
                                                        </a>
                                                        <form class="d-inline" method="POST" action="{{ $deleteUrl }}"
                                                              onsubmit="return confirm('{{ __('translate.Are you sure?') }}')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button class="btn btn-sm btn-outline-danger" type="submit">
                                                                {{ __('translate.Delete') }}
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted py-4">
                                                        {{ __('translate.No data') }}
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                {{-- Pagination --}}
                                @if (method_exists($clients, 'links'))
                                    <div class="mt-3">
                                        {{ $clients->links() }}
                                    </div>
                                @endif

                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
@endsection

@push('style_section')
<style>

.clients-mobile-head{ margin-top:12px; margin-bottom:10px; }
.clients-mobile-kicker{ font-weight:800; color:rgba(17,24,39,.55); font-size:13px; }
.clients-mobile-title{
  font-size:28px; font-weight:950; line-height:1.05; margin-top:4px;
  color:rgba(17,24,39,.92);
}

/* avoid overlap with bottom bar */
@media (max-width:768px){
  .clients-v2{ padding-bottom:110px!important; }
  .container__bscreen{ padding-left:16px; padding-right:16px; }
}

/* section head */
.dash-section-head{
  display:flex; align-items:flex-end; justify-content:space-between;
  gap:14px; padding:2px 4px 10px;
}
.dash-section-title{ margin:0; font-size:18px; font-weight:900; }
.dash-section-sub{ margin:4px 0 0; color:rgba(18,25,38,.65); font-size:13px; font-weight:650; }

.dash-add-btn{
  display:inline-flex; align-items:center; gap:10px;
  padding:10px 12px; border-radius:14px;
  border:1px solid rgba(17,24,39,.10);
  background:rgba(255,66,0,.08);
  color:#ff4200 !important;
  font-weight:900; text-decoration:none!important;
  white-space:nowrap;
}
.dash-add-btn i{ font-size:14px; }

/* alert */
.clients-alert{
  margin: 6px 4px 12px;
  padding: 12px 12px;
  border-radius: 14px;
  border: 1px solid rgba(59,130,246,.18);
  background: rgba(59,130,246,.08);
  color: rgba(17,24,39,.85);
  font-weight: 800;
}

/* filter */
.clients-filter{ margin: 2px 4px 12px; }
.clients-filter__grid{
  display:grid;
  grid-template-columns: 1fr;
  gap:10px;
}
@media (min-width: 768px){
  .clients-filter__grid{
    grid-template-columns: 1fr 170px 140px;
    align-items:center;
  }
}

.clients-input{
  width:100%;
  border-radius:14px;
  padding:12px 12px;
  border:1px solid rgba(17,24,39,.10);
  background:#fff;
  font-weight:750;
}

.clients-btn{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  gap:10px;
  padding:12px 12px;
  border-radius:14px;
  border:1px solid rgba(17,24,39,.10);
  font-weight:900;
  text-decoration:none!important;
  line-height:1;
}
.clients-btn--ghost{
  background:#0f1a23;
  color:#fff;
}
.clients-btn--light{
  background:#fff;
  color:rgba(17,24,39,.85)!important;
}

/* wrap */
.clients-wrap{ overflow:hidden; position:relative; }

/* Desktop table polish */
.clients-table th{
  color: rgba(17,24,39,.55);
  font-weight:900;
}
.clients-table td{
  color: rgba(17,24,39,.92);
  font-weight:700;
}

/* =========================
   Mobile cards
   ========================= */
.client-cards{ margin-top:6px; margin-bottom:12px; }

@media (max-width:768px){
  .client-cards{
    display:grid;
    grid-template-columns: 1fr;
    gap:12px;
  }
}

.client-card{
  background:#fff;
  border:1px solid rgba(17,24,39,.08);
  border-radius:18px;
  box-shadow:0 10px 30px rgba(17,24,39,.06);
  overflow:hidden;
  padding:14px;
}

.client-card__top{
  display:flex;
  align-items:flex-start;
  justify-content:space-between;
  gap:10px;
}
.client-card__id{
  font-size:12px;
  font-weight:950;
  color:rgba(17,24,39,.55);
}
.client-card__name{
  font-size:16px;
  font-weight:950;
  color:rgba(17,24,39,.92);
  line-height:1.2;
  margin-top:4px;
}

.client-card__meta{
  margin-top:12px;
  border-top:1px solid rgba(17,24,39,.08);
  padding-top:10px;
  display:grid;
  gap:8px;
}
.client-meta-row{
  display:flex;
  align-items:flex-start;
  justify-content:space-between;
  gap:10px;
}
.client-meta-row span{
  font-size:11px;
  font-weight:950;
  letter-spacing:.5px;
  text-transform:uppercase;
  color:rgba(17,24,39,.55);
}
.client-meta-row strong{
  font-size:14px;
  font-weight:900;
  color:rgba(17,24,39,.92);
  text-align:right;
  line-height:1.2;
  word-break:break-word;
}

.client-card__actions{
  margin-top:12px;
  display:grid;
  grid-template-columns: 1fr 1fr;
  gap:10px;
}
.client-action{
  width:100%;
  border-radius:14px;
  padding:12px 12px;
  display:flex;
  align-items:center;
  justify-content:center;
  gap:10px;
  text-decoration:none!important;
  font-weight:950;
  border:1px solid rgba(17,24,39,.10);
  background:#fff;
  color:rgba(17,24,39,.92)!important;
}
.client-action--danger{
  border:1px solid rgba(220,38,38,.18);
  background:rgba(220,38,38,.10);
  color:#b91c1c !important;
}
</style>
@endpush

@push('js_section')
<script>
(function($){
  "use strict";

  function escapeHtml(str){
    return $('<div/>').text(str ?? '').html();
  }

  function renderClientCards(){
    const $wrap = $('#clientCards');
    if(!$wrap.length) return;

    // mobile only
    if(window.matchMedia('(min-width: 769px)').matches){
      $wrap.empty();
      return;
    }

    const $rows = $('.clients-table tbody tr[data-id]');
    if(!$rows.length){
      $wrap.html('<div style="padding:12px 4px; font-weight:850; color:rgba(17,24,39,.55)">{{ __('translate.No data') }}</div>');
      return;
    }

    const html = $rows.map(function(){
      const $tr = $(this);

      const id = $tr.data('id') || '';
      const name = $tr.data('name') || '';
      const email = $tr.data('email') || '';
      const phone = $tr.data('phone') || '';
      const edit = $tr.data('edit') || '#';
      const del  = $tr.data('delete') || '#';

      return `
        <article class="client-card">
          <div class="client-card__top">
            <div>
              <div class="client-card__id">#${escapeHtml(id)}</div>
              <div class="client-card__name">${escapeHtml(name || '—')}</div>
            </div>
          </div>

          <div class="client-card__meta">
            <div class="client-meta-row">
              <span>{{ __('translate.Email') }}</span>
              <strong>${escapeHtml(email || '—')}</strong>
            </div>
            <div class="client-meta-row">
              <span>{{ __('translate.Phone') }}</span>
              <strong>${escapeHtml(phone || '—')}</strong>
            </div>
          </div>

          <div class="client-card__actions">
            <a class="client-action" href="${escapeHtml(edit)}">
              <i class="fas fa-pen"></i>
              <span>{{ __('translate.Edit') }}</span>
            </a>

            <form method="POST" action="${escapeHtml(del)}" onsubmit="return confirm('{{ __('translate.Are you sure?') }}')" style="margin:0;">
              <input type="hidden" name="_token" value="{{ csrf_token() }}">
              <input type="hidden" name="_method" value="DELETE">
              <button type="submit" class="client-action client-action--danger">
                <i class="fas fa-trash"></i>
                <span>{{ __('translate.Delete') }}</span>
              </button>
            </form>
          </div>
        </article>
      `;
    }).get().join('');

    $wrap.html(html);
  }

  $(document).ready(function(){
    renderClientCards();
    window.addEventListener('resize', function(){
      clearTimeout(window.__clientsResizeT);
      window.__clientsResizeT = setTimeout(renderClientCards, 120);
    });
  });

})(jQuery);
</script>
@endpush