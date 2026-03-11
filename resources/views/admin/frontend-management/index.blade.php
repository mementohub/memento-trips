@extends('admin.master_layout')
@section('title')
    <title>{{ __('translate.Frontend Section') }}</title>
@endsection

@section('body-header')
    <h3 class="crancy-header__title m-0">{{ __('translate.Frontend Section') }}</h3>
    <p class="crancy-header__text">{{ __('translate.Manage Content') }} >> {{ __('translate.Frontend Section') }}</p>
@endsection
@section('body-content')

    <section class="crancy-adashboard crancy-show">
        <div class="container container__bscreen">
            <div class="row">
                <div class="col-12">
                    <div class="crancy-body">
                        <div class="crancy-dsinner">
                            <div class="crancy-table crancy-table--v3 mg-top-30">
                                <div class="crancy-customer-filter">
                                    <div class="container">
                                        <h4 class="mb-4">{{ __('translate.Theme Settings for') }}: <span class="badge bg-primary">{{ $activeTheme }}</span></h4>

                                        <!-- Tabs for pages -->
                                        <ul class="nav nav-tabs cms-tabs" id="pagesTabs" role="tablist">
                                            @foreach($sectionsByPage as $page => $pageSections)
                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link {{ $loop->first ? 'active' : '' }}"
                                                            id="{{ $page }}-tab"
                                                            data-bs-toggle="tab"
                                                            data-bs-target="#{{ $page }}"
                                                            type="button"
                                                            role="tab">
                                                        {{ ucfirst($page) }}
                                                        <span class="tab-count">{{ count($pageSections) }}</span>
                                                    </button>
                                                </li>
                                            @endforeach
                                        </ul>

                                        <!-- Tab content -->
                                        <div class="tab-content" id="pagesTabsContent">
                                            @foreach($sectionsByPage as $page => $pageSections)
                                                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                                                     id="{{ $page }}"
                                                     role="tabpanel">

                                                    @if($page === 'home')
                                                    <div class="drag-hint mt-3 mb-2">
                                                        <i class="fas fa-info-circle"></i>
                                                        Drag cards to reorder homepage sections
                                                    </div>
                                                    @endif

                                                    <div class="row mt-3 sortable-sections" data-page="{{ $page }}">
                                                        @foreach($pageSections as $key => $section)
                                                            <div class="col-md-4 col-sm-6 mb-4 sortable-card" data-key="{{ $key }}">
                                                                <div class="cms-card">
                                                                    <div class="cms-card-header">
                                                                        <div class="cms-card-header-left">
                                                                            <span class="cms-card-order">{{ $section['order'] ?? '-' }}</span>
                                                                            <h5 class="cms-card-title">{{ $section['name'] }}</h5>
                                                                        </div>
                                                                        <div class="cms-card-drag drag-handle">
                                                                            <i class="fas fa-grip-vertical"></i>
                                                                        </div>
                                                                    </div>
                                                                    <div class="cms-card-body">
                                                                        <div class="cms-card-tags">
                                                                            @if(isset($section['theme']))
                                                                                <span class="cms-tag cms-tag--theme">{{ $section['theme'] }}</span>
                                                                            @endif
                                                                            @if(isset($section['content']))
                                                                                <span class="cms-tag cms-tag--content">{{ count($section['content']) }} fields</span>
                                                                            @endif
                                                                            @if(isset($section['element']))
                                                                                <span class="cms-tag cms-tag--element">{{ count($section['element']) }} elements</span>
                                                                            @endif
                                                                        </div>
                                                                        <a href="{{ route('admin.front-end.section', ['id'=> $key, 'lang_code' => admin_lang()]) }}"
                                                                           class="cms-card-btn">
                                                                            <i class="fas fa-pen"></i> Edit Section
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Save toast -->
    <div id="orderSaveToast" class="save-toast">
        <i class="fas fa-check-circle"></i> Order saved!
    </div>
@endsection

@push('style_section')
<style>
    /* ===== TABS ===== */
    .cms-tabs { border-bottom: 2px solid #e9ecef; gap: 4px; }
    .cms-tabs .nav-link {
        color: #6c757d;
        font-weight: 600;
        font-size: 14px;
        border: none;
        border-radius: 8px 8px 0 0;
        padding: 10px 18px;
        transition: all .2s;
    }
    .cms-tabs .nav-link:hover { color: #333; background: #f8f9fa; }
    .cms-tabs .nav-link.active {
        color: #fff;
        background: #ff4200;
        border: none;
    }
    .tab-count {
        display: inline-block;
        background: rgba(255,255,255,.25);
        border-radius: 10px;
        padding: 0 7px;
        font-size: 11px;
        margin-left: 4px;
    }
    .cms-tabs .nav-link:not(.active) .tab-count {
        background: #e9ecef;
        color: #6c757d;
    }

    /* ===== DRAG HINT ===== */
    .drag-hint {
        font-size: 13px;
        color: #888;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    /* ===== CMS CARDS ===== */
    .cms-card {
        background: #fff;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,.06);
        border: 1px solid #eaedf0;
        transition: box-shadow .2s, transform .2s;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    .cms-card:hover {
        box-shadow: 0 6px 24px rgba(0,0,0,.1);
        transform: translateY(-2px);
    }

    .cms-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 16px;
        background: linear-gradient(135deg, #ff4200 0%, #e63b00 100%);
        color: #fff;
    }
    .cms-card-header-left {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
        flex: 1;
    }
    .cms-card-order {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 26px;
        height: 26px;
        background: rgba(255,255,255,.2);
        border-radius: 8px;
        font-size: 12px;
        font-weight: 800;
        flex-shrink: 0;
    }
    .cms-card-title {
        font-size: .95rem;
        font-weight: 700;
        margin: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .cms-card-drag {
        cursor: grab;
        padding: 4px 6px;
        border-radius: 6px;
        opacity: .5;
        transition: opacity .2s;
        flex-shrink: 0;
    }
    .cms-card-drag:hover { opacity: 1; }
    .cms-card-drag:active { cursor: grabbing; }

    .cms-card-body {
        padding: 16px;
        display: flex;
        flex-direction: column;
        flex: 1;
        justify-content: space-between;
        gap: 14px;
    }
    .cms-card-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }
    .cms-tag {
        font-size: 11px;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 20px;
    }
    .cms-tag--theme { background: #e0f2fe; color: #0369a1; }
    .cms-tag--content { background: #dcfce7; color: #15803d; }
    .cms-tag--element { background: #f3e8ff; color: #7e22ce; }

    .cms-card-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 18px;
        background: #ff4200;
        color: #fff;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        transition: all .2s;
        align-self: flex-start;
    }
    .cms-card-btn:hover {
        background: #e63b00;
        color: #fff;
        transform: translateX(2px);
    }

    /* ===== DRAG STATES ===== */
    .sortable-card.sortable-ghost { opacity: .35; }
    .sortable-card.sortable-chosen .cms-card {
        box-shadow: 0 12px 35px rgba(255,66,0,.25);
        transform: scale(1.03);
    }

    /* ===== SAVE TOAST ===== */
    .save-toast {
        position: fixed;
        bottom: 30px;
        right: 30px;
        background: #198754;
        color: #fff;
        padding: 12px 24px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 14px;
        box-shadow: 0 4px 15px rgba(0,0,0,.2);
        opacity: 0;
        transform: translateY(20px);
        transition: all .3s ease;
        z-index: 9999;
    }
    .save-toast.show {
        opacity: 1;
        transform: translateY(0);
    }
</style>
@endpush

@push('js_section')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
<script>
document.querySelectorAll('.sortable-sections').forEach(container => {
    new Sortable(container, {
        animation: 250,
        handle: '.drag-handle',
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        draggable: '.sortable-card',
        onEnd: function() {
            saveOrdering(container);
        }
    });
});

function saveOrdering(container) {
    const cards = container.querySelectorAll('.sortable-card');
    const sections = [];

    cards.forEach((card, index) => {
        const key = card.dataset.key;
        const orderNum = index + 1;
        sections.push({ key: key, ordering: orderNum });

        // Update the visual order badge
        const badge = card.querySelector('.cms-card-order');
        if (badge) badge.textContent = orderNum;
    });

    fetch("{{ route('admin.front-end.section-ordering') }}", {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ sections: sections })
    })
    .then(r => r.json())
    .then(data => {
        const toast = document.getElementById('orderSaveToast');
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 2000);
    })
    .catch(err => console.error('Error saving order:', err));
}
</script>
@endpush
