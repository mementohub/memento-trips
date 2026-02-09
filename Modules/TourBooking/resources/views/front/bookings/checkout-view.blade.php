@extends('layout_inner_page')

@section('title')
    <title>{{ __('translate.Booking Checkout') }}</title>
@endsection

@section('front-content')
    @include('breadcrumb', ['breadcrumb_title' => __('translate.Booking Checkout')])

    <section class="checkout-area pb-100 pt-125">
        <div class="container">
            <div class="row">
                {{-- LEFT: Billing --}}
                <div class="col-lg-8">
                    <div class="tg-checkout-form-wrapper mr-50">
                        <h2 class="tg-checkout-form-title mb-30">{{ __('translate.Billing Details') }}</h2>

                        <div class="row gx-24">
                            <div class="tg-checkout-form-input mb-25">
                                <label>{{ __('translate.Customer name') }}</label>
                                <input id="customer_name" class="input" type="text"
                                       value="{{ auth()->user()->name ?? '' }}"
                                       name="customer_name"
                                       placeholder="Customer name">
                            </div>

                            <div class="tg-checkout-form-input mb-25">
                                <label>{{ __('translate.Customer email') }}</label>
                                <input id="customer_email" class="input" type="email"
                                       value="{{ auth()->user()->email ?? '' }}"
                                       name="customer_email"
                                       placeholder="Customer email">
                            </div>

                            <div class="tg-checkout-form-input mb-25">
                                <label>{{ __('translate.Customer phone') }}</label>
                                <input id="customer_phone" class="input" type="text"
                                       value="{{ auth()->user()->phone ?? '' }}"
                                       name="customer_phone"
                                       placeholder="Customer phone">
                            </div>

                            <div class="tg-checkout-form-input mb-25">
                                <label>{{ __('translate.Customer address') }}</label>
                                <input id="customer_address" class="input"
                                       value="{{ auth()->user()->address ?? '' }}"
                                       name="customer_address"
                                       type="text"
                                       placeholder="{{ __('translate.House number and Street name') }}">
                            </div>
                        </div>

                        {{-- AGENCY BLOCK --}}
                        @if(!empty($isAgencyUser))
                            <div class="tg-tour-about-border-doted mb-20 mt-10"></div>

                            <div style="border:1px solid rgba(22,163,74,.35); padding:14px; border-radius:10px;">
                                <div class="d-flex align-items-center justify-content-between" style="gap:12px;">
                                    <div>
                                        <div style="font-weight:700; font-size:16px;">Agency booking</div>
                                        <div style="color:#64748b; font-size:13px;">Book this reservation for one of your agency clients.</div>
                                    </div>

                                    <label style="display:flex;align-items:center;gap:10px; margin:0; cursor:pointer;">
                                        <input type="checkbox" id="book_as_agency" style="width:18px;height:18px;">
                                        <span style="font-weight:600;">Book as agency</span>
                                    </label>
                                </div>

                                {{-- CLIENT PICKER --}}
                                <div id="agency_booking_block" style="display:none; margin-top:14px;">
                                    @php
                                        $agencyClientsArr = isset($agencyClients) ? $agencyClients : collect();
                                    @endphp

                                    <div style="position:relative;">
                                        <label style="display:block; font-weight:600; margin-bottom:6px;">
                                            Select existing client
                                        </label>

                                        <input type="text"
                                               id="agency_client_search"
                                               class="input"
                                               placeholder="Search by name / email / phone..."
                                               autocomplete="off">

                                        <input type="hidden" id="agency_client_id" value="">

                                        <div id="agency_client_results"
                                             style="display:none; position:absolute; left:0; right:0; top:100%; z-index:50;
                                                    background:#fff; border:1px solid rgba(15,23,42,.12);
                                                    border-radius:10px; margin-top:6px; box-shadow:0 10px 25px rgba(15,23,42,.12);
                                                    max-height:260px; overflow:auto;">
                                        </div>

                                        <div style="margin-top:10px; display:flex; gap:10px; flex-wrap:wrap;">
                                            <button type="button" id="btn_add_new_client"
                                                    style="border:1px solid rgba(15,23,42,.12); background:#fff; padding:10px 12px; border-radius:10px; font-weight:600;">
                                                + Add new client
                                            </button>

                                            <button type="button" id="btn_clear_client"
                                                    style="border:1px solid rgba(220,38,38,.25); background:#fff; padding:10px 12px; border-radius:10px; font-weight:600;">
                                                Clear selection
                                            </button>
                                        </div>
                                    </div>

                                    {{-- NEW CLIENT FIELDS --}}
                                    <div id="new_client_block" style="display:none; margin-top:14px;">
                                        <div style="font-weight:700; margin-bottom:10px;">New client details</div>

                                        <div class="row gx-24">
                                            <div class="tg-checkout-form-input mb-25 col-md-6">
                                                <label>First name <span style="color:#dc2626">*</span></label>
                                                <input id="new_client_first_name" class="input" type="text" placeholder="First name">
                                            </div>

                                            <div class="tg-checkout-form-input mb-25 col-md-6">
                                                <label>Last name <span style="color:#dc2626">*</span></label>
                                                <input id="new_client_last_name" class="input" type="text" placeholder="Last name">
                                            </div>

                                            <div class="tg-checkout-form-input mb-25 col-md-6">
                                                <label>Email</label>
                                                <input id="new_client_email" class="input" type="email" placeholder="Email">
                                            </div>

                                            <div class="tg-checkout-form-input mb-25 col-md-6">
                                                <label>Phone</label>
                                                <input id="new_client_phone" class="input" type="text" placeholder="Phone">
                                            </div>

                                            <div class="tg-checkout-form-input mb-25 col-md-6">
                                                <label>City</label>
                                                <input id="new_client_city" class="input" type="text" placeholder="City">
                                            </div>

                                            <div class="tg-checkout-form-input mb-25 col-md-6">
                                                <label>Country</label>
                                                <input id="new_client_country" class="input" type="text" placeholder="Country">
                                            </div>

                                            <div class="tg-checkout-form-input mb-25 col-md-12">
                                                <label>Address</label>
                                                <input id="new_client_address" class="input" type="text" placeholder="Address">
                                            </div>

                                            <div class="tg-checkout-form-input mb-0 col-md-12">
                                                <label>Notes</label>
                                                <input id="new_client_notes" class="input" type="text" placeholder="Notes (optional)">
                                            </div>
                                        </div>

                                        <div style="margin-top:10px; color:#64748b; font-size:13px;">
                                            Tip: when you fill new client details, we will create it automatically at booking/payment step and assign it to the booking.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        {{-- /AGENCY BLOCK --}}
                    </div>
                </div>

                {{-- RIGHT: Order Summary + Payment --}}
                <div class="col-lg-4">
                    <div class="tg-blog-sidebar top-sticky mb-30">
                        <div class="tg-blog-sidebar-box mb-30">
                            <h2 class="tg-checkout-form-title tg-checkout-form-title-3 mb-15">Your Order</h2>

                            @php
                                $ticketLines = $data['lines'] ?? [];
                                $grandTotal  = $data['total'] ?? 0;
                            @endphp

                            <div>
                                <div class="tg-tour-about-border-doted mb-15"></div>

                                <div class="tg-tour-about-tickets-wrap mb-15">
                                    <span class="tg-tour-about-sidebar-title">Tickets:</span>

                                    @forelse($ticketLines as $line)
                                        @if(!($line['is_extra'] ?? false))
                                            <div class="tg-tour-about-tickets mb-10">
                                                <div class="tg-tour-about-tickets-adult">
                                                    <span>{{ $line['label'] }}</span>
                                                </div>
                                                <div class="tg-tour-about-tickets-quantity">
                                                    {{ $line['qty'] }}
                                                    x {{ currency($line['unit']) }}
                                                    = {{ currency($line['subtotal']) }}
                                                </div>
                                            </div>
                                        @endif
                                    @empty
                                        <div class="text-muted">No tickets selected.</div>
                                    @endforelse
                                </div>

                                @php
                                    $extraLines = collect($ticketLines)->filter(fn($line) => $line['is_extra'] ?? false);
                                @endphp

                                @if($extraLines->count() > 0)
                                    <div class="tg-tour-about-extra mb-10">
                                        <span class="tg-tour-about-sidebar-title mb-10 d-inline-block">Add Extra:</span>
                                        <div class="tg-filter-list">
                                            <ul>
                                                @foreach ($extraLines as $line)
                                                    <li>
                                                        <div class="checkbox d-flex">
                                                            <label class="tg-label">{{ $line['label'] }}</label>
                                                        </div>
                                                        <span class="quantity">{{ currency($line['subtotal']) }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                @endif

                                <div class="tg-tour-about-border-doted mb-15"></div>
                                <div class="tg-tour-about-coast d-flex align-items-center flex-wrap justify-content-between">
                                    <span class="tg-tour-about-sidebar-title d-inline-block">Total Cost:</span>
                                    <h5 class="total-price">{{ currency($grandTotal, 2) }}</h5>
                                </div>
                            </div>
                        </div>

                        {{-- Payment methods (existing partial) --}}
                        @include('tourbooking::front.bookings.payment')
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('js_section')
<script>
    $(document).ready(function () {
        // ------------------------------
        // Billing sync (existing behavior)
        // ------------------------------
        function syncBillingToPaymentForms() {
            const name = $('#customer_name').val() || '';
            const email = $('#customer_email').val() || '';
            const phone = $('#customer_phone').val() || '';
            const address = $('#customer_address').val() || '';

            $('.form_customer_name').val(name);
            $('.form_customer_email').val(email);
            $('.form_customer_phone').val(phone);
            $('.form_customer_address').val(address);
        }

        $('#customer_name').on('keyup', syncBillingToPaymentForms);
        $('#customer_email').on('change', syncBillingToPaymentForms);
        $('#customer_phone').on('change', syncBillingToPaymentForms);
        $('#customer_address').on('change', syncBillingToPaymentForms);
        syncBillingToPaymentForms();

        // -----------------------------------------
        // Inject hidden inputs into ALL payment forms
        // (so you don't have to touch the payment partial)
        // -----------------------------------------
        function setHiddenOnAllForms(name, value) {
            const forms = $('form');
            forms.each(function () {
                let $f = $(this);
                let $i = $f.find('input[name="' + name.replace(/"/g, '\\"') + '"]');
                if ($i.length === 0) {
                    $i = $('<input/>', { type: 'hidden', name: name });
                    $f.append($i);
                }
                $i.val(value);
            });
        }

        // -------------------------------------------------
        // Agency clients data (direct from DB, no wizard)
        // -------------------------------------------------
        const isAgencyUser = @json(!empty($isAgencyUser));
        const agencyClients = @json(isset($agencyClients) ? $agencyClients : []);

        function normalizeStr(s){ return (s || '').toString().toLowerCase().trim(); }

        function renderResults(list) {
            const $box = $('#agency_client_results');
            $box.empty();

            if (!list.length) {
                $box.append('<div style="padding:10px 12px; color:#64748b;">No results</div>');
                $box.show();
                return;
            }

            list.forEach(function (c) {
                const fullName = (c.first_name || '') + ' ' + (c.last_name || '');
                const meta = [c.email, c.phone, c.city].filter(Boolean).join(' • ');
                const addr = c.address ? ('<div style="color:#64748b;font-size:12px;margin-top:4px;">' + c.address + '</div>') : '';

                const item = `
                    <div class="agency-client-item"
                         data-id="${c.id}"
                         style="padding:10px 12px; cursor:pointer; border-bottom:1px solid rgba(15,23,42,.06);">
                        <div style="font-weight:700;">${fullName.trim() || ('Client #' + c.id)}</div>
                        <div style="color:#64748b; font-size:13px;">${meta}</div>
                        ${addr}
                    </div>
                `;
                $box.append(item);
            });

            $box.show();
        }

        function pickClientById(id) {
            const c = agencyClients.find(x => String(x.id) === String(id));
            if (!c) return;

            const fullName = ((c.first_name || '') + ' ' + (c.last_name || '')).trim();

            $('#agency_client_id').val(c.id);
            $('#agency_client_search').val(fullName || ('Client #' + c.id));
            $('#agency_client_results').hide();
            $('#new_client_block').hide();

            // Optional autofill billing with client data (keeps things consistent)
            if (fullName) $('#customer_name').val(fullName);
            if (c.email) $('#customer_email').val(c.email);
            if (c.phone) $('#customer_phone').val(c.phone);
            if (c.address) $('#customer_address').val(c.address);

            syncBillingToPaymentForms();
            syncAgencyToPaymentForms();
        }

        function clearClientSelection() {
            $('#agency_client_id').val('');
            $('#agency_client_search').val('');
            $('#agency_client_results').hide();

            // keep new client fields, only if visible
            syncAgencyToPaymentForms();
        }

        function isNewClientValidIfVisible() {
            if ($('#new_client_block').is(':visible') === false) return true;

            const fn = ($('#new_client_first_name').val() || '').trim();
            const ln = ($('#new_client_last_name').val() || '').trim();
            return fn.length > 0 && ln.length > 0;
        }

        function syncAgencyToPaymentForms() {
    // Agency booking ON/OFF
    const bookAsAgency = $('#book_as_agency').is(':checked') ? 1 : 0;

    // backend expects: book_as_agency
    setHiddenOnAllForms('book_as_agency', bookAsAgency);

    // Existing client selection
    const clientId = ($('#agency_client_id').val() || '').trim();
    setHiddenOnAllForms('agency_client_id', clientId);

    // NEW CLIENT payload (backend expects flat fields)
    const useNew = ($('#new_client_block').is(':visible') && bookAsAgency === 1) ? 1 : 0;

    // dacă user alege client existent, NU trimitem new client fields ca să nu încurce
    if (useNew === 1) {
        setHiddenOnAllForms('agency_new_first_name', ($('#new_client_first_name').val() || '').trim());
        setHiddenOnAllForms('agency_new_last_name',  ($('#new_client_last_name').val() || '').trim());
        setHiddenOnAllForms('agency_new_email',      ($('#new_client_email').val() || '').trim());
        setHiddenOnAllForms('agency_new_phone',      ($('#new_client_phone').val() || '').trim());
        setHiddenOnAllForms('agency_new_country',    ($('#new_client_country').val() || '').trim());
        setHiddenOnAllForms('agency_new_state',      ($('#new_client_state').val() || '').trim());
        setHiddenOnAllForms('agency_new_city',       ($('#new_client_city').val() || '').trim());
        setHiddenOnAllForms('agency_new_address',    ($('#new_client_address').val() || '').trim());
        setHiddenOnAllForms('agency_new_notes',      ($('#new_client_notes').val() || '').trim());
    } else {
        // curățăm ca să nu existe conflict
        setHiddenOnAllForms('agency_new_first_name', '');
        setHiddenOnAllForms('agency_new_last_name', '');
        setHiddenOnAllForms('agency_new_email', '');
        setHiddenOnAllForms('agency_new_phone', '');
        setHiddenOnAllForms('agency_new_country', '');
        setHiddenOnAllForms('agency_new_state', '');
        setHiddenOnAllForms('agency_new_city', '');
        setHiddenOnAllForms('agency_new_address', '');
        setHiddenOnAllForms('agency_new_notes', '');
    }
}

        // -----------------------------------------
        // Agency UI wiring
        // -----------------------------------------
        if (isAgencyUser) {
            $('#book_as_agency').on('change', function () {
                const on = $(this).is(':checked');
                $('#agency_booking_block').toggle(on);

                if (!on) {
                    // clear everything if user turns it off
                    clearClientSelection();
                    $('#new_client_block').hide();
                    syncAgencyToPaymentForms();
                } else {
                    syncAgencyToPaymentForms();
                }
            });

            $('#agency_client_search').on('input', function () {
                const q = normalizeStr($(this).val());
                $('#agency_client_id').val('');
                syncAgencyToPaymentForms();

                if (!q) {
                    $('#agency_client_results').hide();
                    return;
                }

                const results = agencyClients
                    .filter(function (c) {
                        const s = normalizeStr((c.first_name||'')+' '+(c.last_name||'')+' '+(c.email||'')+' '+(c.phone||'')+' '+(c.city||'')+' '+(c.address||''));
                        return s.includes(q);
                    })
                    .slice(0, 12);

                renderResults(results);
            });

            $(document).on('click', '.agency-client-item', function () {
                const id = $(this).data('id');
                pickClientById(id);
            });

            $('#btn_add_new_client').on('click', function () {
                // switch to new client mode
                $('#agency_client_id').val('');
                $('#agency_client_search').val('');
                $('#agency_client_results').hide();

                $('#new_client_block').toggle(true);
                syncAgencyToPaymentForms();
            });

            $('#btn_clear_client').on('click', function () {
                clearClientSelection();
                $('#new_client_block').hide();
                syncAgencyToPaymentForms();
            });

            // keep hidden inputs updated
            $('#new_client_block input').on('keyup change', function () {
                syncAgencyToPaymentForms();
            });

            // Click outside results closes dropdown
            $(document).on('click', function (e) {
                if (!$(e.target).closest('#agency_client_search, #agency_client_results').length) {
                    $('#agency_client_results').hide();
                }
            });

            // Validate before submitting ANY payment form
            $(document).on('submit', 'form', function (e) {
                const bookAsAgency = $('#book_as_agency').is(':checked');

                if (bookAsAgency) {
                    const clientId = ($('#agency_client_id').val() || '').trim();
                    const newBlockVisible = $('#new_client_block').is(':visible');

                    if (!clientId && !newBlockVisible) {
                        e.preventDefault();
                        alert('Select an existing agency client or add a new client.');
                        return false;
                    }

                    if (newBlockVisible && !isNewClientValidIfVisible()) {
                        e.preventDefault();
                        alert('Please fill First name and Last name for the new client.');
                        return false;
                    }
                }

                // ensure hidden fields are fresh
                syncBillingToPaymentForms();
                syncAgencyToPaymentForms();
                return true;
            });

            // initial sync
            syncAgencyToPaymentForms();
        }
    });
</script>
@endpush