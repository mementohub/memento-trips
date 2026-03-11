@extends('admin.master_layout')
@section('title')
    <title>{{ $page_title }}</title>
@endsection

@section('body-header')
    <h3 class="crancy-header__title m-0">{{ $page_title }}</h3>
    <p class="crancy-header__text">Team & Users >> Manage Admins</p>
@endsection

@section('body-content')
<section class="crancy-adashboard crancy-show">
    <div class="container container__bscreen">
        <div class="row">
            <div class="col-12">
                <div class="crancy-body">
                    <div class="crancy-dsinner">

                        {{-- Pending Delete Requests --}}
                        @if($pendingRequests->count() > 0)
                        <div class="adm-alert-box mb-4">
                            <div class="adm-alert-header">
                                <i class="fas fa-exclamation-triangle"></i>
                                Pending Delete Requests ({{ $pendingRequests->count() }})
                            </div>
                            @foreach($pendingRequests as $req)
                            <div class="adm-alert-item">
                                <div class="adm-alert-info">
                                    <strong>{{ $req->admin->name ?? 'Deleted' }}</strong>
                                    <span class="adm-alert-meta">
                                        requested by <strong>{{ $req->requester->name ?? '?' }}</strong>
                                        · {{ $req->created_at->diffForHumans() }}
                                    </span>
                                </div>
                                <div class="adm-alert-actions">
                                    @if(auth('admin')->id() !== $req->requested_by)
                                    <form action="{{ route('admin.admins.approve-delete', $req->id) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Are you sure you want to PERMANENTLY DELETE this admin?')">
                                        @csrf @method('PUT')
                                        <button class="adm-btn adm-btn--danger adm-btn--sm">
                                            <i class="fas fa-check"></i> Approve Delete
                                        </button>
                                    </form>
                                    @endif
                                    <form action="{{ route('admin.admins.reject-delete', $req->id) }}" method="POST" class="d-inline">
                                        @csrf @method('PUT')
                                        <button class="adm-btn adm-btn--muted adm-btn--sm">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    </form>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif

                        {{-- Header bar --}}
                        <div class="adm-header-bar mb-4">
                            <h4 class="mb-0">Admin Accounts</h4>
                            <a href="{{ route('admin.admins.create') }}" class="adm-btn adm-btn--primary">
                                <i class="fas fa-plus"></i> New Admin
                            </a>
                        </div>

                        {{-- Admin cards grid --}}
                        <div class="row">
                            @foreach($admins as $admin)
                            <div class="col-md-4 col-sm-6 mb-4">
                                <div class="adm-card">
                                    <div class="adm-card-top">
                                        <div class="adm-card-avatar">
                                            {{ strtoupper(substr($admin->name, 0, 1)) }}
                                        </div>
                                        <div class="adm-card-info">
                                            <h5 class="adm-card-name">{{ $admin->name }}</h5>
                                            <span class="adm-card-email">{{ $admin->email }}</span>
                                        </div>
                                        <span class="adm-status adm-status--{{ $admin->status === 'enable' ? 'active' : 'inactive' }}">
                                            {{ $admin->status === 'enable' ? 'Active' : 'Disabled' }}
                                        </span>
                                    </div>

                                    <div class="adm-card-meta">
                                        <span><i class="far fa-calendar"></i> {{ $admin->created_at?->format('M d, Y') ?? 'N/A' }}</span>
                                        @if($admin->pendingDeleteRequest)
                                            <span class="adm-tag adm-tag--warning">
                                                <i class="fas fa-clock"></i> Delete Pending
                                            </span>
                                        @endif
                                    </div>

                                    <div class="adm-card-actions">
                                        <a href="{{ route('admin.admins.edit', $admin->id) }}" class="adm-btn adm-btn--outline adm-btn--sm">
                                            <i class="fas fa-pen"></i> Edit
                                        </a>
                                        @if(auth('admin')->id() !== $admin->id && !$admin->pendingDeleteRequest)
                                        <form action="{{ route('admin.admins.request-delete', $admin->id) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Request deletion of {{ $admin->name }}? Another admin must approve.')">
                                            @csrf
                                            <button class="adm-btn adm-btn--ghost-danger adm-btn--sm">
                                                <i class="fas fa-trash-alt"></i> Delete
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
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
    /* ===== Header Bar ===== */
    .adm-header-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }

    /* ===== Buttons ===== */
    .adm-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 10px 20px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 13px;
        border: none;
        cursor: pointer;
        text-decoration: none;
        transition: all .2s;
    }
    .adm-btn--sm { padding: 6px 14px; font-size: 12px; }
    .adm-btn--primary { background: #ff4200; color: #fff; }
    .adm-btn--primary:hover { background: #e63b00; color: #fff; }
    .adm-btn--outline { background: transparent; color: #ff4200; border: 1.5px solid #ff4200; }
    .adm-btn--outline:hover { background: #ff4200; color: #fff; }
    .adm-btn--danger { background: #dc2626; color: #fff; }
    .adm-btn--danger:hover { background: #b91c1c; color: #fff; }
    .adm-btn--muted { background: #e5e7eb; color: #374151; }
    .adm-btn--muted:hover { background: #d1d5db; }
    .adm-btn--ghost-danger { background: transparent; color: #dc2626; border: 1.5px solid #fecaca; }
    .adm-btn--ghost-danger:hover { background: #dc2626; color: #fff; border-color: #dc2626; }

    /* ===== Admin Cards ===== */
    .adm-card {
        background: #fff;
        border-radius: 16px;
        border: 1px solid #eaedf0;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,.04);
        transition: all .2s;
        padding: 20px;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    .adm-card:hover { box-shadow: 0 6px 24px rgba(0,0,0,.08); transform: translateY(-2px); }

    .adm-card-top {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 14px;
    }
    .adm-card-avatar {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: linear-gradient(135deg, #ff4200 0%, #ff6b3d 100%);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 18px;
        flex-shrink: 0;
    }
    .adm-card-info { flex: 1; min-width: 0; }
    .adm-card-name {
        font-size: 15px;
        font-weight: 700;
        margin: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .adm-card-email {
        font-size: 12px;
        color: #888;
        display: block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .adm-status {
        font-size: 11px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 20px;
        flex-shrink: 0;
    }
    .adm-status--active { background: #dcfce7; color: #15803d; }
    .adm-status--inactive { background: #fee2e2; color: #dc2626; }

    .adm-card-meta {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 12px;
        color: #888;
        margin-bottom: 14px;
        flex-wrap: wrap;
    }

    .adm-tag {
        font-size: 11px;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 6px;
    }
    .adm-tag--warning { background: #fef3c7; color: #d97706; }

    .adm-card-actions {
        display: flex;
        gap: 8px;
        margin-top: auto;
        padding-top: 14px;
        border-top: 1px solid #f3f4f6;
    }

    /* ===== Alert Box ===== */
    .adm-alert-box {
        background: #fffbeb;
        border: 1px solid #fde68a;
        border-radius: 14px;
        overflow: hidden;
    }
    .adm-alert-header {
        padding: 14px 18px;
        font-weight: 700;
        font-size: 14px;
        color: #b45309;
        display: flex;
        align-items: center;
        gap: 8px;
        border-bottom: 1px solid #fde68a;
    }
    .adm-alert-item {
        padding: 12px 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }
    .adm-alert-item + .adm-alert-item { border-top: 1px solid #fef3c7; }
    .adm-alert-info { flex: 1; min-width: 200px; }
    .adm-alert-meta { font-size: 12px; color: #92400e; margin-left: 6px; }
    .adm-alert-actions { display: flex; gap: 8px; flex-shrink: 0; }
</style>
@endpush
