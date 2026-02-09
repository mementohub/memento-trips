@extends('admin.master_layout')

@section('title')
    <title>{{ __('translate.Extra Charges') }} - {{ $service->translation->title ?? $service->title }}</title>
@endsection

@section('body-header')
    <h3 class="crancy-header__title m-0">{{ __('translate.Extra Charges') }}</h3>
    <p class="crancy-header__text">
        {{ __('translate.Tour Booking') }} >> {{ __('translate.Services') }} >> {{ __('translate.Extra Charges') }}
    </p>
@endsection

@push('style_section')
    <style>
        .crancy__item-form--currency {
            position: relative;
            display: flex;
            align-items: center;
        }

        .crancy__item-form--currency .crancy__item-input {
            width: 100%;
            padding: 10px 40px 10px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.3s ease;
        }

        .crancy__item-form--currency .crancy__item-input:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .crancy__currency-icon {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            z-index: 2;
        }

        .crancy__currency-icon span {
            font-size: 14px;
            color: #666;
            font-weight: 500;
        }

        .crancy__item-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }

        .mg-top-form-20 {
            margin-top: 20px;
        }

        .extra-charge-pricing-card {
            border: 1px solid #e5e5e5;
            border-radius: 8px;
            padding: 15px 15px 5px;
            margin-top: 15px;
            background: #fafafa;
        }

        .extra-charge-pricing-card__title {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .extra-charge-pricing-card__subtitle {
            font-size: 12px;
            color: #777;
            margin-bottom: 12px;
        }

        .text-muted {
            font-size: 12px;
            color: #777;
        }

        .divider-or {
            text-align: center;
            font-size: 11px;
            color: #999;
            margin: 6px 0 8px;
            position: relative;
        }

        .divider-or::before,
        .divider-or::after {
            content: "";
            position: absolute;
            top: 50%;
            width: 40%;
            height: 1px;
            background: #e0e0e0;
        }

        .divider-or::before { left: 0; }
        .divider-or::after { right: 0; }

        @media (max-width: 768px) {
            .crancy__item-form--currency .crancy__item-input {
                padding-right: 35px;
            }
            .crancy__currency-icon { right: 10px; }
            .crancy__currency-icon span { font-size: 13px; }
        }
    </style>
@endpush

@section('body-content')
    @php
        $currency = config('settings.currency_icon', '€');
    @endphp

    <section class="crancy-adashboard crancy-show">
        <div class="container container__bscreen">
            <div class="row">
                <div class="col-12">
                    <div class="crancy-body">
                        <div class="crancy-dsinner">
                            <div class="row">
                                <div class="col-12 mg-top-30">
                                    <div class="crancy-product-card">
                                        {{-- Header --}}
                                        <div class="create_new_btn_inline_box">
                                            <h4 class="crancy-product-card__title">
                                                {{ __('translate.Extra Charges for') }}: {{ $service->translation->title ?? $service->title }}
                                            </h4>
                                            <div>
                                                <a href="{{ route('admin.tourbooking.services.edit', $service->id) }}" class="crancy-btn">
                                                    <i class="fa fa-edit"></i> {{ __('translate.Edit Service') }}
                                                </a>
                                                <a href="{{ route('admin.tourbooking.services.index') }}" class="crancy-btn">
                                                    <i class="fa fa-list"></i> {{ __('translate.Service List') }}
                                                </a>
                                            </div>
                                        </div>

                                        {{-- ADD NEW EXTRA CHARGE --}}
                                        <div class="row mg-top-30">
                                            <div class="col-12">
                                                <div class="accordion" id="extraChargesAccordion">
                                                    <div class="accordion-item">
                                                        <h2 class="accordion-header" id="headingOne">
                                                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                                                {{ __('translate.Add New Extra Charge') }}
                                                            </button>
                                                        </h2>
                                                        <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#extraChargesAccordion">
                                                            <div class="accordion-body">
                                                                <form action="{{ route('admin.tourbooking.services.extra-charges.store', $service->id) }}" method="POST">
                                                                    @csrf
                                                                    <div class="row">
                                                                        {{-- Name --}}
                                                                        <div class="col-lg-6 col-md-6 col-12">
                                                                            <div class="crancy__item-form--group mg-top-form-20">
                                                                                <label class="crancy__item-label">{{ __('translate.Name') }} *</label>
                                                                                <input class="crancy__item-input" type="text" name="name" value="{{ old('name') }}" required>
                                                                                @error('name')<span class="text-danger">{{ $message }}</span>@enderror
                                                                            </div>
                                                                        </div>

                                                                        {{-- Description --}}
                                                                        <div class="col-lg-6 col-md-6 col-12">
                                                                            <div class="crancy__item-form--group mg-top-form-20">
                                                                                <label class="crancy__item-label">{{ __('translate.Description') }}</label>
                                                                                <textarea class="crancy__item-input summernote" name="description" rows="3">{{ old('description') }}</textarea>
                                                                                @error('description')<span class="text-danger">{{ $message }}</span>@enderror
                                                                            </div>
                                                                        </div>

                                                                        {{-- PRICING CARD --}}
                                                                        <div class="col-12">
                                                                            <div class="extra-charge-pricing-card">
                                                                                <div class="extra-charge-pricing-card__title">{{ __('translate.Pricing') }}</div>
                                                                                <div class="extra-charge-pricing-card__subtitle">
                                                                                    {{ __('translate.Choose between a general price for the trip or prices per age category.') }}
                                                                                </div>

                                                                                <div class="row">
                                                                                    {{-- General Price --}}
                                                                                    <div class="col-lg-4 col-md-6 col-12">
                                                                                        <div class="crancy__item-form--group mg-top-form-20">
                                                                                            <label class="crancy__item-label">{{ __('translate.General Price (Flat)') }}</label>
                                                                                            <div class="crancy__item-form--currency">
                                                                                                <input class="crancy__item-input js-general-price" type="number" step="0.01" name="general_price" value="{{ old('general_price') }}">
                                                                                                <div class="crancy__currency-icon"><span>{{ $currency }}</span></div>
                                                                                            </div>
                                                                                            <small class="text-muted">{{ __('translate.If you set a general price, age category fields will be disabled automatically.') }}</small>
                                                                                        </div>
                                                                                    </div>

                                                                                    <div class="col-12">
                                                                                        <div class="divider-or">{{ __('translate.or use age categories') }}</div>
                                                                                    </div>

                                                                                    {{-- Adult Price --}}
                                                                                    <div class="col-lg-4 col-md-4 col-12">
                                                                                        <div class="crancy__item-form--group mg-top-form-20">
                                                                                            <label class="crancy__item-label">{{ __('translate.Adult Price') }}</label>
                                                                                            <div class="crancy__item-form--currency">
                                                                                                <input class="crancy__item-input js-age-price" type="number" step="0.01" name="adult_price" value="{{ old('adult_price') }}">
                                                                                                <div class="crancy__currency-icon"><span>{{ $currency }}</span></div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>

                                                                                    {{-- Child Price --}}
                                                                                    <div class="col-lg-4 col-md-4 col-12">
                                                                                        <div class="crancy__item-form--group mg-top-form-20">
                                                                                            <label class="crancy__item-label">{{ __('translate.Child Price') }}</label>
                                                                                            <div class="crancy__item-form--currency">
                                                                                                <input class="crancy__item-input js-age-price" type="number" step="0.01" name="child_price" value="{{ old('child_price') }}">
                                                                                                <div class="crancy__currency-icon"><span>{{ $currency }}</span></div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>

                                                                                    {{-- Infant Price --}}
                                                                                    <div class="col-lg-4 col-md-4 col-12">
                                                                                        <div class="crancy__item-form--group mg-top-form-20">
                                                                                            <label class="crancy__item-label">{{ __('translate.Infant Price') }}</label>
                                                                                            <div class="crancy__item-form--currency">
                                                                                                <input class="crancy__item-input js-age-price" type="number" step="0.01" name="infant_price" value="{{ old('infant_price') }}">
                                                                                                <div class="crancy__currency-icon"><span>{{ $currency }}</span></div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        {{-- Is Mandatory --}}
                                                                        <div class="col-lg-4 col-md-4 col-12">
                                                                            <div class="crancy__item-form--group mg-top-form-20">
                                                                                <label class="crancy__item-label">{{ __('translate.Is Mandatory') }}</label>
                                                                                <div class="crancy-ptabs__notify-switch crancy-ptabs__notify-switch--two">
                                                                                    <label class="crancy__item-switch">
                                                                                        <input name="is_mandatory" type="hidden" value="0">
                                                                                        <input name="is_mandatory" type="checkbox" {{ old('is_mandatory') ? 'checked' : '' }}>
                                                                                        <span class="crancy__item-switch--slide crancy__item-switch--round"></span>
                                                                                    </label>
                                                                                </div>
                                                                                <small class="text-muted">{{ __('translate.If enabled, the customer cannot remove this extra from the booking.') }}</small>
                                                                            </div>
                                                                        </div>

                                                                        {{-- Apply to All Persons --}}
                                                                        <div class="col-lg-4 col-md-4 col-12">
                                                                            <div class="crancy__item-form--group mg-top-form-20">
                                                                                <label class="crancy__item-label">{{ __('translate.Mandatory for all persons') }}</label>
                                                                                <div class="crancy-ptabs__notify-switch crancy-ptabs__notify-switch--two">
                                                                                    <label class="crancy__item-switch">
                                                                                        <input name="apply_to_all_persons" type="hidden" value="0">
                                                                                        <input name="apply_to_all_persons" type="checkbox" {{ old('apply_to_all_persons') ? 'checked' : '' }}>
                                                                                        <span class="crancy__item-switch--slide crancy__item-switch--round"></span>
                                                                                    </label>
                                                                                </div>
                                                                                <small class="text-muted">{{ __('translate.If enabled, this extra will be applied to every person in the booking (no per-age quantity selection).') }}</small>
                                                                            </div>
                                                                        </div>

                                                                        {{-- Is Tax --}}
                                                                        <div class="col-lg-4 col-md-4 col-12">
                                                                            <div class="crancy__item-form--group mg-top-form-20">
                                                                                <label class="crancy__item-label">{{ __('translate.Is Tax') }}</label>
                                                                                <div class="crancy-ptabs__notify-switch crancy-ptabs__notify-switch--two">
                                                                                    <label class="crancy__item-switch">
                                                                                        <input name="is_tax" type="hidden" value="0">
                                                                                        <input name="is_tax" type="checkbox" class="js-create-tax-toggle" {{ old('is_tax') ? 'checked' : '' }}>
                                                                                        <span class="crancy__item-switch--slide crancy__item-switch--round"></span>
                                                                                    </label>
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        {{-- Tax Percentage --}}
                                                                        <div class="col-lg-6 col-md-6 col-12" id="tax_percentage_field" style="display: none;">
                                                                            <div class="crancy__item-form--group mg-top-form-20">
                                                                                <label class="crancy__item-label">{{ __('translate.Tax Percentage') }}</label>
                                                                                <div class="crancy__item-form--currency">
                                                                                    <input class="crancy__item-input" type="number" step="0.01" min="0" max="100" name="tax_percentage" value="{{ old('tax_percentage') }}">
                                                                                    <div class="crancy__currency-icon"><span>%</span></div>
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        {{-- Max Quantity --}}
                                                                        <div class="col-lg-6 col-md-6 col-12">
                                                                            <div class="crancy__item-form--group mg-top-form-20">
                                                                                <label class="crancy__item-label">{{ __('translate.Max Quantity') }}</label>
                                                                                <input class="crancy__item-input" type="number" name="max_quantity" value="{{ old('max_quantity') }}" min="1">
                                                                                @error('max_quantity')<span class="text-danger">{{ $message }}</span>@enderror
                                                                            </div>
                                                                        </div>

                                                                        {{-- Status --}}
                                                                        <div class="col-lg-4 col-md-4 col-12">
                                                                            <div class="crancy__item-form--group mg-top-form-20">
                                                                                <label class="crancy__item-label">{{ __('translate.Status') }}</label>
                                                                                <div class="crancy-ptabs__notify-switch crancy-ptabs__notify-switch--two">
                                                                                    <label class="crancy__item-switch">
                                                                                        <input name="status" type="hidden" value="0">
                                                                                        <input name="status" type="checkbox" {{ old('status', 1) ? 'checked' : '' }} value="1">
                                                                                        <span class="crancy__item-switch--slide crancy__item-switch--round"></span>
                                                                                    </label>
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <div class="col-12 mg-top-30">
                                                                            <button type="submit" class="crancy-btn">{{ __('translate.Add Extra Charge') }}</button>
                                                                        </div>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- EXISTING EXTRA CHARGES TABLE --}}
                                        <div class="crancy-product-table mg-top-25">
                                            <table id="crancy-table__vendor">
                                                <thead>
                                                    <tr>
                                                        <th>{{ __('translate.Name') }}</th>
                                                        <th>{{ __('translate.Price') }}</th>
                                                        <th>{{ __('translate.Price Type') }}</th>
                                                        <th>{{ __('translate.Mandatory') }}</th>
                                                        <th>{{ __('translate.Tax') }}</th>
                                                        <th>{{ __('translate.Status') }}</th>
                                                        <th>{{ __('translate.Action') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($service->extraCharges as $charge)
                                                        @php
                                                            $hasGeneral = $charge->general_price !== null && (float)$charge->general_price > 0;
                                                            $hasAge = collect([$charge->adult_price, $charge->child_price, $charge->infant_price])
                                                                ->filter(fn($v) => $v !== null && $v !== '' && (float)$v > 0)
                                                                ->isNotEmpty();

                                                            $headline = 0;
                                                            $effectiveType = 'flat';

                                                            if ($hasAge) {
                                                                $effectiveType = 'per_person';
                                                                foreach ([$charge->adult_price, $charge->child_price, $charge->infant_price] as $v) {
                                                                    if ($v !== null && $v !== '' && (float)$v > 0) {
                                                                        $headline = (float)$v;
                                                                        break;
                                                                    }
                                                                }
                                                            } elseif ($hasGeneral) {
                                                                $headline = (float)$charge->general_price;
                                                            }

                                                            $effectiveType = $charge->price_type ?: $effectiveType;
                                                            $typeText = $effectiveType === 'per_person' ? __('translate.Per Person') : __('translate.Flat');
                                                        @endphp
                                                        <tr>
                                                            <td>
                                                                <strong>{{ $charge->name }}</strong>
                                                                @if($charge->description)
                                                                    <div class="small text-muted">{{ \Illuminate\Support\Str::limit(strip_tags($charge->description), 50) }}</div>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                {{ $currency }} {{ number_format($headline, 2) }}
                                                                <div class="small text-muted">{{ $effectiveType === 'per_person' ? __('translate.Per person') : __('translate.Per booking') }}</div>
                                                            </td>
                                                            <td>{{ $typeText }}</td>
                                                            <td>
                                                                <span class="crancy-badge crancy-badge-{{ $charge->is_mandatory ? 'success' : 'secondary' }}">
                                                                    {{ $charge->is_mandatory ? __('translate.Yes') : __('translate.No') }}
                                                                </span>
                                                                @if($charge->apply_to_all_persons)
                                                                    <div class="small text-muted">{{ __('translate.Applied to all persons') }}</div>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if($charge->is_tax)
                                                                    <span class="crancy-badge crancy-badge-info">{{ __('translate.Yes') }} ({{ number_format((float)$charge->tax_percentage, 2) }}%)</span>
                                                                @else
                                                                    <span class="crancy-badge crancy-badge-secondary">{{ __('translate.No') }}</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <span class="crancy-badge crancy-badge-{{ $charge->status ? 'success' : 'danger' }}">
                                                                    {{ $charge->status ? __('translate.Active') : __('translate.Inactive') }}
                                                                </span>
                                                            </td>
                                                            <td class="crancy-table__action">
                                                                <div class="crancy-table__action-btn">
                                                                    <a href="#" class="crancy-action__btn crancy-action__edit" data-bs-toggle="modal" data-bs-target="#editModal{{ $charge->id }}">
                                                                        <i class="fa fa-edit"></i>
                                                                    </a>
                                                                    <a href="#" class="crancy-action__btn crancy-action__delete" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $charge->id }}">
                                                                        <i class="fa fa-trash"></i>
                                                                    </a>
                                                                </div>
                                                            </td>

                                                            {{-- EDIT MODAL --}}
                                                            <div class="modal fade" id="editModal{{ $charge->id }}" tabindex="-1" aria-hidden="true">
                                                                <div class="modal-dialog modal-lg">
                                                                    <div class="modal-content">
                                                                        <div class="modal-header">
                                                                            <h5 class="modal-title">{{ __('translate.Edit Extra Charge') }}</h5>
                                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                        </div>
                                                                        <form action="{{ route('admin.tourbooking.services.extra-charges.update', $charge->id) }}" method="POST">
                                                                            @csrf
                                                                            @method('PUT')
                                                                            <div class="modal-body">
                                                                                <div class="row">
                                                                                    {{-- Name --}}
                                                                                    <div class="col-lg-6 col-md-6 col-12">
                                                                                        <div class="crancy__item-form--group">
                                                                                            <label class="crancy__item-label">{{ __('translate.Name') }} *</label>
                                                                                            <input class="crancy__item-input" type="text" name="name" value="{{ $charge->name }}" required>
                                                                                        </div>
                                                                                    </div>

                                                                                    {{-- Description --}}
                                                                                    <div class="col-lg-6 col-md-6 col-12">
                                                                                        <div class="crancy__item-form--group">
                                                                                            <label class="crancy__item-label">{{ __('translate.Description') }}</label>
                                                                                            <textarea class="crancy__item-input summernote" name="description" rows="3">{{ $charge->description }}</textarea>
                                                                                        </div>
                                                                                    </div>

                                                                                    {{-- PRICING CARD --}}
                                                                                    <div class="col-12">
                                                                                        <div class="extra-charge-pricing-card">
                                                                                            <div class="extra-charge-pricing-card__title">{{ __('translate.Pricing') }}</div>
                                                                                            <div class="extra-charge-pricing-card__subtitle">{{ __('translate.Choose between a general price for the trip or prices per age category.') }}</div>

                                                                                            <div class="row">
                                                                                                {{-- General Price --}}
                                                                                                <div class="col-lg-4 col-md-6 col-12">
                                                                                                    <div class="crancy__item-form--group mg-top-form-20">
                                                                                                        <label class="crancy__item-label">{{ __('translate.General Price (Flat)') }}</label>
                                                                                                        <div class="crancy__item-form--currency">
                                                                                                            <input class="crancy__item-input js-general-price-edit" type="number" step="0.01" name="general_price" value="{{ $charge->general_price }}">
                                                                                                            <div class="crancy__currency-icon"><span>{{ $currency }}</span></div>
                                                                                                        </div>
                                                                                                        <small class="text-muted">{{ __('translate.If you set a general price, age category fields will be disabled automatically.') }}</small>
                                                                                                    </div>
                                                                                                </div>

                                                                                                <div class="col-12">
                                                                                                    <div class="divider-or">{{ __('translate.or use age categories') }}</div>
                                                                                                </div>

                                                                                                {{-- Adult Price --}}
                                                                                                <div class="col-lg-4 col-md-4 col-12">
                                                                                                    <div class="crancy__item-form--group mg-top-form-20">
                                                                                                        <label class="crancy__item-label">{{ __('translate.Adult Price') }}</label>
                                                                                                        <div class="crancy__item-form--currency">
                                                                                                            <input class="crancy__item-input js-age-price-edit" type="number" step="0.01" name="adult_price" value="{{ $charge->adult_price }}">
                                                                                                            <div class="crancy__currency-icon"><span>{{ $currency }}</span></div>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </div>

                                                                                                {{-- Child Price --}}
                                                                                                <div class="col-lg-4 col-md-4 col-12">
                                                                                                    <div class="crancy__item-form--group mg-top-form-20">
                                                                                                        <label class="crancy__item-label">{{ __('translate.Child Price') }}</label>
                                                                                                        <div class="crancy__item-form--currency">
                                                                                                            <input class="crancy__item-input js-age-price-edit" type="number" step="0.01" name="child_price" value="{{ $charge->child_price }}">
                                                                                                            <div class="crancy__currency-icon"><span>{{ $currency }}</span></div>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </div>

                                                                                                {{-- Infant Price --}}
                                                                                                <div class="col-lg-4 col-md-4 col-12">
                                                                                                    <div class="crancy__item-form--group mg-top-form-20">
                                                                                                        <label class="crancy__item-label">{{ __('translate.Infant Price') }}</label>
                                                                                                        <div class="crancy__item-form--currency">
                                                                                                            <input class="crancy__item-input js-age-price-edit" type="number" step="0.01" name="infant_price" value="{{ $charge->infant_price }}">
                                                                                                            <div class="crancy__currency-icon"><span>{{ $currency }}</span></div>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>

                                                                                    {{-- Is Mandatory --}}
                                                                                    <div class="col-lg-4 col-md-4 col-12">
                                                                                        <div class="crancy__item-form--group mg-top-form-20">
                                                                                            <label class="crancy__item-label">{{ __('translate.Is Mandatory') }}</label>
                                                                                            <div class="crancy-ptabs__notify-switch crancy-ptabs__notify-switch--two">
                                                                                                <label class="crancy__item-switch">
                                                                                                    <input name="is_mandatory" type="hidden" value="0">
                                                                                                    <input name="is_mandatory" type="checkbox" {{ $charge->is_mandatory ? 'checked' : '' }} value="1">
                                                                                                    <span class="crancy__item-switch--slide crancy__item-switch--round"></span>
                                                                                                </label>
                                                                                            </div>
                                                                                            <small class="text-muted">{{ __('translate.If enabled, the customer cannot remove this extra from the booking.') }}</small>
                                                                                        </div>
                                                                                    </div>

                                                                                    {{-- Apply to All Persons --}}
                                                                                    <div class="col-lg-4 col-md-4 col-12">
                                                                                        <div class="crancy__item-form--group mg-top-form-20">
                                                                                            <label class="crancy__item-label">{{ __('translate.Mandatory for all persons') }}</label>
                                                                                            <div class="crancy-ptabs__notify-switch crancy-ptabs__notify-switch--two">
                                                                                                <label class="crancy__item-switch">
                                                                                                    <input name="apply_to_all_persons" type="hidden" value="0">
                                                                                                    <input name="apply_to_all_persons" type="checkbox" {{ $charge->apply_to_all_persons ? 'checked' : '' }} value="1">
                                                                                                    <span class="crancy__item-switch--slide crancy__item-switch--round"></span>
                                                                                                </label>
                                                                                            </div>
                                                                                            <small class="text-muted">{{ __('translate.If enabled, this extra will be applied to every person in the booking (no per-age quantity selection).') }}</small>
                                                                                        </div>
                                                                                    </div>

                                                                                    {{-- Is Tax --}}
                                                                                    <div class="col-lg-4 col-md-4 col-12">
                                                                                        <div class="crancy__item-form--group mg-top-form-20">
                                                                                            <label class="crancy__item-label">{{ __('translate.Is Tax') }}</label>
                                                                                            <div class="crancy-ptabs__notify-switch crancy-ptabs__notify-switch--two">
                                                                                                <label class="crancy__item-switch">
                                                                                                    <input name="is_tax" type="hidden" value="0">
                                                                                                    <input name="is_tax" type="checkbox" class="tax-toggle" {{ $charge->is_tax ? 'checked' : '' }} value="1">
                                                                                                    <span class="crancy__item-switch--slide crancy__item-switch--round"></span>
                                                                                                </label>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>

                                                                                    {{-- Tax Percentage --}}
                                                                                    <div class="col-lg-6 col-md-6 col-12 tax-percentage-field" style="{{ $charge->is_tax ? '' : 'display: none;' }}">
                                                                                        <div class="crancy__item-form--group mg-top-form-20">
                                                                                            <label class="crancy__item-label">{{ __('translate.Tax Percentage') }}</label>
                                                                                            <div class="crancy__item-form--currency">
                                                                                                <input class="crancy__item-input" type="number" step="0.01" min="0" max="100" name="tax_percentage" value="{{ $charge->tax_percentage }}">
                                                                                                <div class="crancy__currency-icon"><span>%</span></div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>

                                                                                    {{-- Max Quantity --}}
                                                                                    <div class="col-lg-6 col-md-6 col-12">
                                                                                        <div class="crancy__item-form--group mg-top-form-20">
                                                                                            <label class="crancy__item-label">{{ __('translate.Max Quantity') }}</label>
                                                                                            <input class="crancy__item-input" type="number" name="max_quantity" value="{{ $charge->max_quantity }}" min="1">
                                                                                        </div>
                                                                                    </div>

                                                                                    {{-- Status --}}
                                                                                    <div class="col-lg-4 col-md-4 col-12">
                                                                                        <div class="crancy__item-form--group mg-top-form-20">
                                                                                            <label class="crancy__item-label">{{ __('translate.Status') }}</label>
                                                                                            <div class="crancy-ptabs__notify-switch crancy-ptabs__notify-switch--two">
                                                                                                <label class="crancy__item-switch">
                                                                                                    <input name="status" type="hidden" value="0">
                                                                                                    <input name="status" type="checkbox" {{ $charge->status ? 'checked' : '' }} value="1">
                                                                                                    <span class="crancy__item-switch--slide crancy__item-switch--round"></span>
                                                                                                </label>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="modal-footer">
                                                                                <button type="button" class="crancy-btn crancy-btn__default" data-bs-dismiss="modal">{{ __('translate.Cancel') }}</button>
                                                                                <button type="submit" class="crancy-btn">{{ __('translate.Update') }}</button>
                                                                            </div>
                                                                        </form>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            {{-- DELETE MODAL --}}
                                                            <div class="modal fade" id="deleteModal{{ $charge->id }}" tabindex="-1" aria-hidden="true">
                                                                <div class="modal-dialog">
                                                                    <div class="modal-content">
                                                                        <div class="modal-header">
                                                                            <h5 class="modal-title">{{ __('translate.Confirm Delete') }}</h5>
                                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                        </div>
                                                                        <div class="modal-body">
                                                                            {{ __('translate.Are you sure you want to delete this extra charge?') }}
                                                                        </div>
                                                                        <div class="modal-footer">
                                                                            <button type="button" class="crancy-btn crancy-btn__default" data-bs-dismiss="modal">{{ __('translate.Cancel') }}</button>
                                                                            <form action="{{ route('admin.tourbooking.services.extra-charges.destroy', $charge->id) }}" method="POST" style="display: inline;">
                                                                                @csrf
                                                                                @method('DELETE')
                                                                                <button type="submit" class="crancy-btn crancy-btn__danger">{{ __('translate.Delete') }}</button>
                                                                            </form>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="7" class="text-center">{{ __('translate.No extra charges found') }}</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
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
@endsection

@push('js_section')
    <script src="{{ asset('global/tinymce/js/tinymce/tinymce.min.js') }}"></script>
    <script>
        (function($) {
            "use strict";
            $(document).ready(function() {
                // TinyMCE Init
                tinymce.init({
                    selector: '.summernote',
                    plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
                    toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table mergetags | addcomment showcomments | spellcheckdialog a11ycheck typography | align lineheight | checklist numlist bullist indent outdent | emoticons charmap | removeformat',
                    tinycomments_mode: 'embedded',
                    tinycomments_author: 'Author name',
                    mergetags_list: [
                        { value: 'First.Name', title: 'First Name' },
                        { value: 'Email', title: 'Email' }
                    ]
                });

                // Tax Percentage Toggle - Create Form
                const $createTaxToggle = $('.js-create-tax-toggle');
                const $createTaxField = $('#tax_percentage_field');

                function syncCreateTax() {
                    $createTaxField.toggle($createTaxToggle.is(':checked'));
                }

                $createTaxToggle.on('change', syncCreateTax);
                syncCreateTax();

                // Tax Percentage Toggle - Edit Modals
                $('.tax-toggle').each(function() {
                    const $toggle = $(this);
                    const $taxField = $toggle.closest('.modal-body').find('.tax-percentage-field');

                    function syncTax() {
                        $taxField.toggle($toggle.is(':checked'));
                    }

                    syncTax();
                    $toggle.on('change', syncTax);
                });

                // Pricing Mode Logic - Create Form
                const $generalPriceInput = $('.js-general-price');
                const $agePriceInputs = $('.js-age-price');

                function syncCreatePricingMode() {
                    const hasGeneral = $.trim($generalPriceInput.val()).length > 0;
                    let hasAge = false;

                    $agePriceInputs.each(function() {
                        if ($.trim($(this).val()).length > 0) hasAge = true;
                    });

                    if (hasGeneral) {
                        $agePriceInputs.val('').prop('disabled', true);
                    } else {
                        $agePriceInputs.prop('disabled', false);
                    }

                    if (hasAge) {
                        $generalPriceInput.val('').prop('disabled', true);
                    } else if (!hasGeneral) {
                        $generalPriceInput.prop('disabled', false);
                    }
                }

                $generalPriceInput.on('input', syncCreatePricingMode);
                $agePriceInputs.on('input', syncCreatePricingMode);
                syncCreatePricingMode();

                // Pricing Mode Logic - Edit Modals
                $('.modal').on('shown.bs.modal', function() {
                    const $modal = $(this);
                    const $gen = $modal.find('.js-general-price-edit');
                    const $ages = $modal.find('.js-age-price-edit');

                    if (!$gen.length) return;

                    function syncEditPricingMode() {
                        const hasGeneral = $.trim($gen.val()).length > 0;
                        let hasAge = false;

                        $ages.each(function() {
                            if ($.trim($(this).val()).length > 0) hasAge = true;
                        });

                        if (hasGeneral) {
                            $ages.val('').prop('disabled', true);
                        } else {
                            $ages.prop('disabled', false);
                        }

                        if (hasAge) {
                            $gen.val('').prop('disabled', true);
                        } else if (!hasGeneral) {
                            $gen.prop('disabled', false);
                        }
                    }

                    $gen.on('input', syncEditPricingMode);
                    $ages.on('input', syncEditPricingMode);
                    syncEditPricingMode();
                });

                // DataTable
                $('#crancy-table__vendor').DataTable({
                    responsive: true,
                    paging: false,
                    info: false,
                    searching: true,
                    ordering: true
                });
            });
        })(jQuery);
    </script>
@endpush