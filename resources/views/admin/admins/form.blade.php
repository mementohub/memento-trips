@extends('admin.master_layout')
@section('title')
    <title>{{ $page_title }}</title>
@endsection

@section('body-header')
    <h3 class="crancy-header__title m-0">{{ $page_title }}</h3>
    <p class="crancy-header__text">Team & Users >> <a href="{{ route('admin.admins.index') }}">Manage Admins</a> >> {{ isset($admin) ? 'Edit' : 'Create' }}</p>
@endsection

@section('body-content')
<section class="crancy-adashboard crancy-show">
    <div class="container container__bscreen">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="crancy-body">
                    <div class="crancy-dsinner">
                        <div class="adm-form-card">
                            <div class="adm-form-header">
                                <i class="fas fa-{{ isset($admin) ? 'user-edit' : 'user-plus' }}"></i>
                                {{ isset($admin) ? 'Edit Admin Account' : 'Create New Admin' }}
                            </div>

                            <form action="{{ isset($admin) ? route('admin.admins.update', $admin->id) : route('admin.admins.store') }}"
                                  method="POST" class="adm-form-body">
                                @csrf
                                @if(isset($admin)) @method('PUT') @endif

                                <div class="mb-3">
                                    <label class="adm-label">Name</label>
                                    <input type="text" name="name" class="adm-input @error('name') is-invalid @enderror"
                                           value="{{ old('name', $admin->name ?? '') }}" required>
                                    @error('name') <div class="adm-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="adm-label">Email</label>
                                    <input type="email" name="email" class="adm-input @error('email') is-invalid @enderror"
                                           value="{{ old('email', $admin->email ?? '') }}" required>
                                    @error('email') <div class="adm-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="adm-label">
                                        Password
                                        @if(isset($admin))
                                        <small class="text-muted">(leave blank to keep current)</small>
                                        @endif
                                    </label>
                                    <input type="password" name="password" class="adm-input @error('password') is-invalid @enderror"
                                           {{ isset($admin) ? '' : 'required' }}>
                                    @error('password') <div class="adm-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="adm-label">Confirm Password</label>
                                    <input type="password" name="password_confirmation" class="adm-input"
                                           {{ isset($admin) ? '' : 'required' }}>
                                </div>

                                <div class="mb-4">
                                    <label class="adm-label">Status</label>
                                    <div class="adm-radio-group">
                                        <label class="adm-radio">
                                            <input type="radio" name="status" value="enable"
                                                   {{ old('status', $admin->status ?? 'enable') === 'enable' ? 'checked' : '' }}>
                                            <span class="adm-radio-mark"></span>
                                            Active
                                        </label>
                                        <label class="adm-radio">
                                            <input type="radio" name="status" value="disable"
                                                   {{ old('status', $admin->status ?? '') === 'disable' ? 'checked' : '' }}>
                                            <span class="adm-radio-mark"></span>
                                            Disabled
                                        </label>
                                    </div>
                                </div>

                                <div class="adm-form-footer">
                                    <a href="{{ route('admin.admins.index') }}" class="adm-btn adm-btn--muted">Cancel</a>
                                    <button type="submit" class="adm-btn adm-btn--primary">
                                        <i class="fas fa-check"></i>
                                        {{ isset($admin) ? 'Update Admin' : 'Create Admin' }}
                                    </button>
                                </div>
                            </form>
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
    .adm-form-card {
        background: #fff;
        border-radius: 16px;
        border: 1px solid #eaedf0;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,.05);
    }
    .adm-form-header {
        padding: 18px 24px;
        font-size: 16px;
        font-weight: 700;
        background: linear-gradient(135deg, #ff4200 0%, #ff6b3d 100%);
        color: #fff;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .adm-form-body { padding: 24px; }
    .adm-label {
        display: block;
        font-weight: 600;
        font-size: 13px;
        color: #374151;
        margin-bottom: 6px;
    }
    .adm-input {
        width: 100%;
        padding: 10px 14px;
        border: 1.5px solid #e5e7eb;
        border-radius: 10px;
        font-size: 14px;
        transition: border-color .2s;
    }
    .adm-input:focus {
        outline: none;
        border-color: #ff4200;
        box-shadow: 0 0 0 3px rgba(255,66,0,.1);
    }
    .adm-input.is-invalid { border-color: #dc2626; }
    .adm-error { color: #dc2626; font-size: 12px; margin-top: 4px; }

    .adm-radio-group { display: flex; gap: 20px; }
    .adm-radio {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
    }
    .adm-radio input { display: none; }
    .adm-radio-mark {
        width: 18px;
        height: 18px;
        border: 2px solid #d1d5db;
        border-radius: 50%;
        position: relative;
        transition: all .2s;
    }
    .adm-radio input:checked + .adm-radio-mark {
        border-color: #ff4200;
    }
    .adm-radio input:checked + .adm-radio-mark::after {
        content: '';
        position: absolute;
        top: 3px; left: 3px;
        width: 8px; height: 8px;
        border-radius: 50%;
        background: #ff4200;
    }

    .adm-form-footer {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding-top: 16px;
        border-top: 1px solid #f3f4f6;
    }

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
    .adm-btn--primary { background: #ff4200; color: #fff; }
    .adm-btn--primary:hover { background: #e63b00; color: #fff; }
    .adm-btn--muted { background: #e5e7eb; color: #374151; }
    .adm-btn--muted:hover { background: #d1d5db; color: #374151; }
</style>
@endpush
