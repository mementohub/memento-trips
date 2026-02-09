{{-- resources/views/agency/clients/edit.blade.php 

@extends('agency.master_layout')

@section('title')
    <title>{{ __('translate.Edit Client') }}</title>
@endsection

@section('body-header')
    {{-- Desktop header clasic --}}
    <div class="d-none d-md-block">
        <h3 class="crancy-header__title m-0">{{ __('translate.Edit Client') }}</h3>
        <p class="crancy-header__text">{{ __('translate.Clients') }} >> {{ __('translate.Edit Client') }}</p>
    </div>
@endsection

@section('body-content')
    <section class="crancy-adashboard crancy-show client-edit-v2">
        <div class="container container__bscreen">

            {{-- Mobile head --}}
            <div class="d-md-none client-edit-mobile-head">
                <div class="client-edit-kicker">{{ __('translate.Clients') }}</div>
                <div class="client-edit-title">{{ __('translate.Edit Client') }}</div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="crancy-body">
                        <div class="crancy-dsinner">
                            <div class="crancy-table crancy-table--v3 mg-top-30 client-edit-wrap">

                                <div class="dash-section-head">
                                    <div>
                                        <h4 class="dash-section-title">{{ __('translate.Edit Client') }}</h4>
                                        <p class="dash-section-sub">{{ __('translate.Update client details and save.') }}</p>
                                    </div>

                                    <a class="dash-back-btn" href="{{ route('agency.clients.index') }}">
                                        <i class="fas fa-arrow-left"></i>
                                        <span>{{ __('translate.Back') }}</span>
                                    </a>
                                </div>

                                {{-- Errors --}}
                                @if ($errors->any())
                                    <div class="form-alert form-alert--danger">
                                        <div class="form-alert__title">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            <span>{{ __('translate.Please fix the errors below') }}</span>
                                        </div>
                                        <ul class="form-alert__list">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <form method="POST" action="{{ route('agency.clients.update', $client) }}" class="client-form">
                                    @csrf
                                    @method('PUT')

                                    <div class="form-grid">
                                        <div class="form-field">
                                            <label class="form-label">First name <span class="req">*</span></label>
                                            <input class="form-input" name="first_name"
                                                   value="{{ old('first_name', $client->first_name) }}"
                                                   required autocomplete="given-name">
                                        </div>

                                        <div class="form-field">
                                            <label class="form-label">Last name <span class="req">*</span></label>
                                            <input class="form-input" name="last_name"
                                                   value="{{ old('last_name', $client->last_name) }}"
                                                   required autocomplete="family-name">
                                        </div>

                                        <div class="form-field">
                                            <label class="form-label">Email</label>
                                            <input class="form-input" type="email" name="email"
                                                   value="{{ old('email', $client->email) }}"
                                                   autocomplete="email">
                                        </div>

                                        <div class="form-field">
                                            <label class="form-label">Phone</label>
                                            <input class="form-input" name="phone"
                                                   value="{{ old('phone', $client->phone) }}"
                                                   autocomplete="tel">
                                        </div>

                                        <div class="form-field form-field--full">
                                            <label class="form-label">Address</label>
                                            <input class="form-input" name="address"
                                                   value="{{ old('address', $client->address) }}"
                                                   autocomplete="street-address">
                                        </div>

                                        <div class="form-field form-field--full">
                                            <label class="form-label">Notes</label>
                                            <textarea class="form-input form-textarea" name="notes" rows="4">{{ old('notes', $client->notes) }}</textarea>
                                        </div>

                                        <div class="form-actions">
                                            <button class="form-btn form-btn--primary" type="submit">
                                                <i class="fas fa-save"></i>
                                                <span>{{ __('translate.Update') }}</span>
                                            </button>

                                            <a class="form-btn form-btn--light" href="{{ route('agency.clients.index') }}">
                                                <i class="fas fa-times"></i>
                                                <span>{{ __('translate.Cancel') ?? 'Cancel' }}</span>
                                            </a>
                                        </div>
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

.client-edit-mobile-head{ margin-top:12px; margin-bottom:10px; }
.client-edit-kicker{ font-weight:800; color:rgba(17,24,39,.55); font-size:13px; }
.client-edit-title{
  font-size:28px; font-weight:950; line-height:1.05; margin-top:4px;
  color:rgba(17,24,39,.92);
}

/* avoid overlap bottom nav */
@media (max-width:768px){
  .client-edit-v2{ padding-bottom:110px!important; }
  .container__bscreen{ padding-left:16px; padding-right:16px; }
}

/* section head */
.dash-section-head{
  display:flex; align-items:flex-end; justify-content:space-between;
  gap:14px; padding:2px 4px 10px;
}
.dash-section-title{ margin:0; font-size:18px; font-weight:900; }
.dash-section-sub{ margin:4px 0 0; color:rgba(18,25,38,.65); font-size:13px; font-weight:650; }

.dash-back-btn{
  display:inline-flex; align-items:center; gap:10px;
  padding:10px 12px; border-radius:14px;
  border:1px solid rgba(17,24,39,.10);
  background:#fff;
  color:rgba(17,24,39,.85)!important;
  font-weight:900; text-decoration:none!important;
  white-space:nowrap;
}

/* alert */
.form-alert{
  margin: 6px 4px 14px;
  padding: 12px 12px;
  border-radius: 16px;
  border: 1px solid rgba(17,24,39,.10);
  background: #fff;
}
.form-alert--danger{
  border-color: rgba(220,38,38,.20);
  background: rgba(220,38,38,.06);
}
.form-alert__title{
  display:flex; align-items:center; gap:10px;
  font-weight:950; color: rgba(17,24,39,.90);
}
.form-alert--danger .form-alert__title{ color:#b91c1c; }
.form-alert__list{
  margin:10px 0 0; padding-left:18px;
  color: rgba(17,24,39,.85);
  font-weight:700;
}

/* form */
.client-form{ margin: 0 4px; }
.form-grid{
  display:grid;
  grid-template-columns: 1fr;
  gap:12px;
}
@media (min-width: 768px){
  .form-grid{
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap:14px;
  }
}
.form-field--full{ grid-column: 1 / -1; }

.form-label{
  display:block;
  font-size:12px;
  font-weight:950;
  letter-spacing:.4px;
  text-transform:uppercase;
  color: rgba(17,24,39,.55);
  margin-bottom:6px;
}
.req{ color:#ef4444; font-weight:950; }

.form-input{
  width:100%;
  border-radius:14px;
  padding:12px 12px;
  border:1px solid rgba(17,24,39,.10);
  background:#fff;
  font-weight:750;
  color: rgba(17,24,39,.92);
}
.form-input:focus{
  outline:none;
  border-color: rgba(255,66,0,.35);
  box-shadow: 0 0 0 4px rgba(255,66,0,.10);
}
.form-textarea{ resize: vertical; min-height: 110px; }

/* actions */
.form-actions{
  grid-column: 1 / -1;
  display:grid;
  grid-template-columns: 1fr;
  gap:10px;
  margin-top:4px;
}
@media (min-width: 576px){
  .form-actions{
    grid-template-columns: 220px 220px;
    justify-content:flex-start;
  }
}

.form-btn{
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
.form-btn--primary{
  background:#0f1a23;
  border-color:#0f1a23;
  color:#fff !important;
}
.form-btn--light{
  background:#fff;
  color:rgba(17,24,39,.85)!important;
}
</style>
@endpush