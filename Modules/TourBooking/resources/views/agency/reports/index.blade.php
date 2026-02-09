@extends('agency.master_layout')

@section('title')
    <title>{{ __('translate.Reports') }}</title>
@endsection

@section('body-header')
@php
    $reportsIndexUrl = \Illuminate\Support\Facades\Route::has('agency.tourbooking.reports.index')
        ? route('agency.tourbooking.reports.index')
        : (\Illuminate\Support\Facades\Route::has('agency.reports.index')
            ? route('agency.reports.index')
            : url('/agency/reports'));

    $byClientSafe  = isset($byClient)  ? $byClient  : collect();
    $byServiceSafe = isset($byService) ? $byService : collect();
@endphp

<div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h3 class="crancy-header__title m-0">{{ __('translate.Reports') }}</h3>
        <p class="crancy-header__text">{{ __('translate.Dashboard') }} >> {{ __('translate.Reports') }}</p>
    </div>
</div>
@endsection

@section('body-content')
<section class="crancy-adashboard crancy-show">
    <div class="container container__bscreen">
        <div class="row">
            <div class="col-12">
                <div class="crancy-body">
                    <div class="crancy-dsinner">

                        {{-- FILTERS --}}
                        <div class="crancy-table crancy-table--v3 mg-top-30">
                            <div class="crancy-customer-filter">
                                <div class="crancy-customer-filter__single crancy-customer-filter__single--csearch d-flex items-center justify-between create_new_btn_box">
                                    <div class="crancy-header__form crancy-header__form--customer create_new_btn_inline_box">
                                        <h4 class="crancy-product-card__title">{{ __('translate.Filters') }}</h4>
                                    </div>
                                </div>
                            </div>

                            <div class="p-3">
                                <form method="GET" class="row g-2">
                                    <div class="col-md-3">
                                        <input type="date" class="form-control" name="from" value="{{ request('from') }}">
                                    </div>
                                    <div class="col-md-3">
                                        <input type="date" class="form-control" name="to" value="{{ request('to') }}">
                                    </div>
                                    <div class="col-md-3">
                                        <select name="status" class="form-control">
                                            <option value="">{{ __('translate.All statuses') }}</option>
                                            @foreach(['pending','confirmed','cancelled','completed'] as $s)
                                                <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3 d-flex gap-2">
                                        <button class="btn btn-outline-secondary w-100" type="submit">{{ __('translate.Filter') }}</button>
                                        <a class="btn btn-outline-light w-100" href="{{ $reportsIndexUrl }}">{{ __('translate.Reset') }}</a>
                                    </div>
                                </form>
                            </div>
                        </div>

                        {{-- BY CLIENT --}}
                        <div class="crancy-table crancy-table--v3 mg-top-30">
                            <div class="crancy-customer-filter">
                                <div class="crancy-customer-filter__single crancy-customer-filter__single--csearch d-flex items-center justify-between create_new_btn_box">
                                    <div class="crancy-header__form crancy-header__form--customer create_new_btn_inline_box">
                                        <h4 class="crancy-product-card__title">{{ __('translate.By Client') }}</h4>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="crancy-table__main crancy-table__main-v3">
                                    <thead class="crancy-table__head">
                                        <tr>
                                            <th>{{ __('translate.Client') }}</th>
                                            <th class="text-end">{{ __('translate.Bookings') }}</th>
                                            <th class="text-end">{{ __('translate.Total') }}</th>
                                            <th class="text-end">{{ __('translate.Commission') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="crancy-table__body">
                                        @forelse($byClientSafe as $row)
                                            <tr>
                                                <td>{{ $row->client_name ?? ('Client #'.($row->agency_client_id ?? '')) }}</td>
                                                <td class="text-end">{{ (int) ($row->bookings_count ?? 0) }}</td>
                                                <td class="text-end">{{ currency((float) ($row->total_value ?? 0)) }}</td>
                                                <td class="text-end">{{ currency((float) ($row->total_commission ?? 0)) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-4">
                                                    {{ __('translate.No data') }}
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- BY SERVICE --}}
                        <div class="crancy-table crancy-table--v3 mg-top-30">
                            <div class="crancy-customer-filter">
                                <div class="crancy-customer-filter__single crancy-customer-filter__single--csearch d-flex items-center justify-between create_new_btn_box">
                                    <div class="crancy-header__form crancy-header__form--customer create_new_btn_inline_box">
                                        <h4 class="crancy-product-card__title">{{ __('translate.By Service') }}</h4>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="crancy-table__main crancy-table__main-v3">
                                    <thead class="crancy-table__head">
                                        <tr>
                                            <th>{{ __('translate.Service') }}</th>
                                            <th class="text-end">{{ __('translate.Bookings') }}</th>
                                            <th class="text-end">{{ __('translate.Total') }}</th>
                                            <th class="text-end">{{ __('translate.Commission') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="crancy-table__body">
                                        @forelse($byServiceSafe as $row)
                                            <tr>
                                                <td>{{ $row->service_name ?? ('Service #'.($row->service_id ?? '')) }}</td>
                                                <td class="text-end">{{ (int) ($row->bookings_count ?? 0) }}</td>
                                                <td class="text-end">{{ currency((float) ($row->total_value ?? 0)) }}</td>
                                                <td class="text-end">{{ currency((float) ($row->total_commission ?? 0)) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-4">
                                                    {{ __('translate.No data') }}
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div><!-- /dsinner -->
                </div>
            </div>
        </div>
    </div>
</section>
@endsection