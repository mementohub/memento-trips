{{-- Modules/TourBooking/Resources/views/agency/bookings/wizard.blade.php --}}
@extends('agency.master_layout')

@section('title')
    {{ __('translate.New Booking') }} — Wizard
@endsection

@section('body-content')
<main class="crancy-adashboard" x-data="bookingWizard()" x-init="init()" x-cloak>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="crancy-body">
                    <div class="crancy-dsinner">

                        <div class="crancy-page-title d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <h3 class="crancy-page-title__heading mb-0">{{ __('translate.New Booking') }} — Wizard</h3>

                            <div class="d-flex gap-2">
                                <a class="btn btn-outline-secondary" href="{{ route('agency.tourbooking.bookings.index') }}">
                                    {{ __('translate.Back') }}
                                </a>
                            </div>
                        </div>

                        {{-- NON-BLOCKING global activity line --}}
                        <div class="alert alert-light border d-flex align-items-center gap-2 py-2"
                             x-show="isBusy()"
                             x-transition.opacity
                             x-cloak>
                            <div class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></div>
                            <div class="small mb-0" x-text="busyText()"></div>
                        </div>

                        {{-- alerts --}}
                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        @if(session('message'))
                            <div class="alert alert-{{ session('alert-type') ?? 'info' }}">{{ session('message') }}</div>
                        @endif
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- Stepper --}}
                        <div class="crancy-card mb-3" x-cloak>
                            <div class="crancy-card__content">
                                <div class="d-flex flex-wrap gap-2 align-items-center">
                                    <template x-for="s in steps" :key="s.id">
                                        <button type="button"
                                                class="btn"
                                                :class="step === s.id ? 'btn-primary' : (canGoTo(s.id) ? 'btn-outline-primary' : 'btn-outline-secondary disabled')"
                                                @click="goTo(s.id)"
                                                x-text="s.label"
                                                :disabled="isBusy()">
                                        </button>
                                    </template>

                                    <div class="ms-auto small text-muted">
                                        Step <span x-text="step"></span>/6
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Card container --}}
                        <div class="crancy-card" x-cloak>
                            <div class="crancy-card__content">

                                {{-- STEP 1: Select Client --}}
                                <section x-show="step === 1" x-transition x-cloak>
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <h5 class="mb-2">1) Select Client</h5>
                                            <p class="text-muted mb-0">Caută clientul și selectează-l pentru booking.</p>
                                        </div>

                                        <div class="col-md-8">
                                            <label class="form-label">Search client</label>
                                            <div class="d-flex gap-2">
                                                <input type="text" class="form-control"
                                                       placeholder="Name, email, phone…"
                                                       x-model.trim="clientQuery"
                                                       :disabled="loadingClients"
                                                       @keydown.enter.prevent="searchClients()">

                                                <button class="btn btn-outline-primary d-flex align-items-center gap-2"
                                                        type="button"
                                                        @click="searchClients()"
                                                        :disabled="loadingClients">
                                                    <span x-show="!loadingClients">Search</span>
                                                    <span x-show="loadingClients" class="d-flex align-items-center gap-2" x-cloak>
                                                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                                        Loading
                                                    </span>
                                                </button>
                                            </div>
                                            <div class="form-text">Tip: poți căuta după email sau telefon.</div>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Selected client</label>
                                            <div class="border rounded p-2 bg-light">
                                                <template x-if="selectedClient">
                                                    <div>
                                                        <div class="fw-semibold" x-text="selectedClient.name"></div>
                                                        <div class="small text-muted">
                                                            <span x-text="selectedClient.email || ''"></span>
                                                            <template x-if="selectedClient.phone">
                                                                <span> • <span x-text="selectedClient.phone"></span></span>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </template>
                                                <template x-if="!selectedClient">
                                                    <div class="text-muted">None</div>
                                                </template>
                                            </div>
                                        </div>

                                        <div class="col-12" x-show="clients.length" x-cloak>
                                            <label class="form-label">Results</label>
                                            <div class="table-responsive">
                                                <table class="table table-sm align-middle">
                                                    <thead>
                                                    <tr>
                                                        <th>Name</th>
                                                        <th>Email</th>
                                                        <th>Phone</th>
                                                        <th class="text-end">Action</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <template x-for="c in clients" :key="c.id">
                                                        <tr>
                                                            <td class="fw-semibold" x-text="c.name"></td>
                                                            <td x-text="c.email || '-'"></td>
                                                            <td x-text="c.phone || '-'"></td>
                                                            <td class="text-end">
                                                                <button class="btn btn-sm btn-primary" type="button" @click="selectClient(c)">
                                                                    Select
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    </template>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <div class="col-12 d-flex gap-2">
                                            <button class="btn btn-primary"
                                                    type="button"
                                                    :disabled="!selectedClient || isBusy()"
                                                    @click="next()">
                                                Next
                                            </button>
                                        </div>
                                    </div>
                                </section>

                                {{-- STEP 2: Date + Pax --}}
                                <section x-show="step === 2" x-transition x-cloak>
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <h5 class="mb-2">2) Select date of tour + number of persons</h5>
                                            <p class="text-muted mb-0">Alege data și pax pe categorii (adult/child/baby/infant). Adult ≥ 1.</p>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Date *</label>
                                            <input type="text"
                                                   class="form-control"
                                                   x-ref="dateInput"
                                                   placeholder="YYYY-MM-DD"
                                                   readonly
                                                   :disabled="loadingMap">
                                            <div class="form-text">
                                                Datepicker folosește availabilityMap (zile fără disponibilitate sunt blocate).
                                                <span class="ms-2" x-show="loadingMap" x-cloak>
                                                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                                    Loading dates…
                                                </span>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Total pax</label>
                                            <div class="border rounded p-2 bg-light">
                                                <div class="d-flex justify-content-between">
                                                    <span class="text-muted">Adults</span><span class="fw-semibold" x-text="pax.adult"></span>
                                                </div>
                                                <div class="d-flex justify-content-between">
                                                    <span class="text-muted">Children</span><span class="fw-semibold" x-text="pax.child"></span>
                                                </div>
                                                <div class="d-flex justify-content-between">
                                                    <span class="text-muted">Baby</span><span class="fw-semibold" x-text="pax.baby"></span>
                                                </div>
                                                <div class="d-flex justify-content-between">
                                                    <span class="text-muted">Infant</span><span class="fw-semibold" x-text="pax.infant"></span>
                                                </div>
                                                <hr class="my-2">
                                                <div class="d-flex justify-content-between">
                                                    <span class="fw-semibold">Total</span><span class="fw-bold" x-text="totalPax()"></span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label">Adults *</label>
                                            <input type="number" class="form-control" min="1" x-model.number="pax.adult" @input="onPaxChange()">
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label">Children</label>
                                            <input type="number" class="form-control" min="0" x-model.number="pax.child" @input="onPaxChange()">
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label">Baby</label>
                                            <input type="number" class="form-control" min="0" x-model.number="pax.baby" @input="onPaxChange()">
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label">Infant</label>
                                            <input type="number" class="form-control" min="0" x-model.number="pax.infant" @input="onPaxChange()">
                                        </div>

                                        <div class="col-12" x-show="pax.adult < 1" x-cloak>
                                            <div class="alert alert-danger mb-0">Adult must be at least 1.</div>
                                        </div>

                                        <div class="col-12 d-flex gap-2">
                                            <button class="btn btn-outline-secondary" type="button" @click="prev()" :disabled="isBusy()">Back</button>
                                            <button class="btn btn-primary" type="button" :disabled="!isStep2Valid() || loadingMap || isBusy()" @click="next()">
                                                Next
                                            </button>
                                        </div>
                                    </div>
                                </section>

                                {{-- STEP 3: Select Tour (Service) --}}
                                <section x-show="step === 3" x-transition x-cloak>
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <h5 class="mb-2">3) Select Tour (Service)</h5>
                                            <p class="text-muted mb-0">Caută și alege un trip. Cardurile fără availability pentru data + pax sunt dezactivate.</p>
                                        </div>

                                        <div class="col-md-8">
                                            <label class="form-label">Search</label>
                                            <input type="text"
                                                   class="form-control"
                                                   placeholder="Search by name…"
                                                   x-model.trim="serviceQuery"
                                                   :disabled="loadingServices"
                                                   @input.debounce.250ms="loadServices()">
                                        </div>
                                        <div class="col-md-4 d-flex align-items-end">
                                            <button class="btn btn-outline-primary w-100 d-flex align-items-center justify-content-center gap-2"
                                                    type="button"
                                                    @click="loadServices()"
                                                    :disabled="loadingServices">
                                                <span x-show="!loadingServices">Refresh list</span>
                                                <span x-show="loadingServices" x-cloak class="d-flex align-items-center gap-2">
                                                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                                    Loading
                                                </span>
                                            </button>
                                        </div>

                                        <div class="col-12" x-show="services.length === 0 && !loadingServices" x-cloak>
                                            <div class="alert alert-info mb-0">
                                                Nu ai încă servicii încărcate sau nu există rezultate. (Apasă “Refresh list”)
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="row g-3">
                                                <template x-for="s in services" :key="s.id">
                                                    <div class="col-12 col-md-6 col-lg-4">
                                                        <div class="card h-100"
                                                             :class="s.availability.is_available ? 'border' : 'border opacity-50'"
                                                             :style="s.availability.is_available ? '' : 'filter:grayscale(1); cursor:not-allowed;'">
                                                            <div class="card-body d-flex flex-column">
                                                                <div class="d-flex justify-content-between align-items-start gap-2">
                                                                    <div class="fw-semibold" style="line-height:1.2;" x-text="s.title"></div>
                                                                    <span class="badge"
                                                                          :class="s.availability.is_available ? 'bg-success' : 'bg-secondary'"
                                                                          x-text="s.availability.label"></span>
                                                                </div>

                                                                <div class="small text-muted mt-1">
                                                                    Spots: <span class="fw-semibold" x-text="s.availability.spots"></span>
                                                                    • Required: <span class="fw-semibold" x-text="totalPax()"></span>
                                                                </div>

                                                                <div class="mt-3">
                                                                    <div class="small text-muted">Price (Adult)</div>
                                                                    <div class="d-flex align-items-center gap-2">
                                                                        <div class="text-muted text-decoration-line-through"
                                                                             x-show="Number(s.prices.base.adult) !== Number(s.prices.availability.adult)"
                                                                             x-cloak>
                                                                            <span x-text="money(s.prices.base.adult)"></span>
                                                                        </div>
                                                                        <div class="fw-bold" style="font-size:1.05rem;">
                                                                            <span x-text="money(s.prices.availability.adult)"></span>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="mt-auto pt-3 d-flex gap-2">
                                                                    <button type="button"
                                                                            class="btn w-100"
                                                                            :class="selectedService && selectedService.id === s.id ? 'btn-primary' : 'btn-outline-primary'"
                                                                            :disabled="!s.availability.is_available"
                                                                            @click="selectService(s)">
                                                                        <span x-text="selectedService && selectedService.id === s.id ? 'Selected' : 'Select'"></span>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>

                                        <div class="col-12 d-flex gap-2">
                                            <button class="btn btn-outline-secondary" type="button" @click="prev()" :disabled="isBusy()">Back</button>
                                            <button class="btn btn-primary" type="button" :disabled="!selectedService || isBusy()" @click="next()">
                                                Next
                                            </button>
                                        </div>
                                    </div>
                                </section>

                                {{-- STEP 4: Extras (+ optional pickup) --}}
                                <section x-show="step === 4" x-transition x-cloak>
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <h5 class="mb-2">4) Select extra-charges</h5>
                                            <p class="text-muted mb-0">Mandatory extras sunt preselectate. Per-age extras se sincronizează cu pax.</p>
                                        </div>

                                        <div class="col-12" x-show="selectedService" x-cloak>
                                            <div class="border rounded p-2 bg-light">
                                                <div class="fw-semibold" x-text="selectedService?.title"></div>
                                                <div class="small text-muted">
                                                    Date: <span class="fw-semibold" x-text="selectedDate"></span>
                                                    • Pax: <span class="fw-semibold" x-text="totalPax()"></span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12" x-show="loadingExtras || loadingPickup" x-cloak>
                                            <div class="alert alert-light border mb-0 d-flex align-items-center gap-2">
                                                <div class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></div>
                                                <div class="small">Loading extras / pickup…</div>
                                            </div>
                                        </div>

                                        <div class="col-12" x-show="!loadingExtras && extras.length === 0" x-cloak>
                                            <div class="alert alert-info mb-0">Nu există extra-charges pentru acest service.</div>
                                        </div>

                                        <template x-for="ex in extras" :key="ex.id">
                                            <div class="col-12">
                                                <div class="border rounded p-3">
                                                    <div class="d-flex justify-content-between align-items-start gap-2">
                                                        <div>
                                                            <div class="fw-semibold" x-text="ex.name"></div>
                                                            <div class="small text-muted">
                                                                <span class="badge bg-light text-dark border" x-text="ex.charge_type"></span>
                                                                <template x-if="ex.is_tax">
                                                                    <span class="badge bg-warning text-dark ms-1">tax %</span>
                                                                </template>
                                                                <template x-if="ex.is_mandatory">
                                                                    <span class="badge bg-primary ms-1">mandatory</span>
                                                                </template>
                                                                <template x-if="ex.apply_to_all_persons">
                                                                    <span class="badge bg-secondary ms-1">apply to all</span>
                                                                </template>
                                                            </div>
                                                        </div>

                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox"
                                                                   :id="'ex_'+ex.id"
                                                                   :checked="extrasState[ex.id]?.active"
                                                                   :disabled="ex.is_mandatory"
                                                                   @change="toggleExtra(ex.id)">
                                                            <label class="form-check-label" :for="'ex_'+ex.id">
                                                                <span x-text="extrasState[ex.id]?.active ? 'On' : 'Off'"></span>
                                                            </label>
                                                        </div>
                                                    </div>

                                                    <div class="mt-2 small text-muted" x-show="!ex.is_tax" x-cloak>
                                                        <template x-if="ex.charge_type === 'per_booking'">
                                                            <span>Per booking: <span class="fw-semibold" x-text="money(ex.price)"></span></span>
                                                        </template>
                                                        <template x-if="ex.charge_type === 'per_person'">
                                                            <span>Per person: <span class="fw-semibold" x-text="money(ex.price)"></span> × pax</span>
                                                        </template>
                                                        <template x-if="ex.charge_type === 'per_age'">
                                                            <span>
                                                                Per age:
                                                                adult <span class="fw-semibold" x-text="money(ex.prices_per_age?.adult ?? 0)"></span>,
                                                                child <span class="fw-semibold" x-text="money(ex.prices_per_age?.child ?? 0)"></span>,
                                                                baby <span class="fw-semibold" x-text="money(ex.prices_per_age?.baby ?? 0)"></span>,
                                                                infant <span class="fw-semibold" x-text="money(ex.prices_per_age?.infant ?? 0)"></span>
                                                            </span>
                                                        </template>
                                                    </div>

                                                    <div class="mt-3"
                                                         x-show="ex.charge_type === 'per_age' && extrasState[ex.id]?.active && !ex.apply_to_all_persons && !ex.is_mandatory"
                                                         x-cloak>
                                                        <div class="row g-2">
                                                            <div class="col-6 col-md-3">
                                                                <label class="form-label mb-1 small">Adult qty</label>
                                                                <select class="form-control"
                                                                        x-model.number="extrasState[ex.id].quantities.adult"
                                                                        @change="onExtrasChange()">
                                                                    <template x-for="n in range(0, pax.adult)" :key="'a'+n">
                                                                        <option :value="n" x-text="n"></option>
                                                                    </template>
                                                                </select>
                                                            </div>
                                                            <div class="col-6 col-md-3">
                                                                <label class="form-label mb-1 small">Child qty</label>
                                                                <select class="form-control"
                                                                        x-model.number="extrasState[ex.id].quantities.child"
                                                                        @change="onExtrasChange()">
                                                                    <template x-for="n in range(0, pax.child)" :key="'c'+n">
                                                                        <option :value="n" x-text="n"></option>
                                                                    </template>
                                                                </select>
                                                            </div>
                                                            <div class="col-6 col-md-3">
                                                                <label class="form-label mb-1 small">Baby qty</label>
                                                                <select class="form-control"
                                                                        x-model.number="extrasState[ex.id].quantities.baby"
                                                                        @change="onExtrasChange()">
                                                                    <template x-for="n in range(0, pax.baby)" :key="'b'+n">
                                                                        <option :value="n" x-text="n"></option>
                                                                    </template>
                                                                </select>
                                                            </div>
                                                            <div class="col-6 col-md-3">
                                                                <label class="form-label mb-1 small">Infant qty</label>
                                                                <select class="form-control"
                                                                        x-model.number="extrasState[ex.id].quantities.infant"
                                                                        @change="onExtrasChange()">
                                                                    <template x-for="n in range(0, pax.infant)" :key="'i'+n">
                                                                        <option :value="n" x-text="n"></option>
                                                                    </template>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </template>

                                        {{-- Optional pickup --}}
                                        <div class="col-12" x-show="selectedService" x-cloak>
                                            <div class="border rounded p-3">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <div class="fw-semibold">Pickup (optional)</div>
                                                        <div class="small text-muted">Selectează un pickup point (dacă există).</div>
                                                    </div>
                                                    <div class="small text-muted" x-show="pickupCharge > 0" x-cloak>
                                                        Charge: <span class="fw-semibold" x-text="money(pickupCharge)"></span>
                                                    </div>
                                                </div>

                                                <div class="mt-2">
                                                    <select class="form-control"
                                                            x-model="pickupPointId"
                                                            @change="onPickupChange()"
                                                            :disabled="loadingPickup">
                                                        <option value="">No pickup</option>
                                                        <template x-for="pp in pickupPoints" :key="pp.id">
                                                            <option :value="pp.id"
                                                                    x-text="pp.name + (pp.has_charge ? (' — ' + money(pp.charge ?? pp.extra_charge ?? 0)) : ' — Free')"></option>
                                                        </template>
                                                    </select>
                                                </div>

                                                <div class="small text-muted mt-2" x-show="loadingPickup" x-cloak>
                                                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                                    Calculating pickup…
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12 d-flex gap-2">
                                            <button class="btn btn-outline-secondary" type="button" @click="prev()" :disabled="isBusy()">Back</button>
                                            <button class="btn btn-primary" type="button" :disabled="!selectedService || isBusy()" @click="next()">
                                                Next
                                            </button>
                                        </div>
                                    </div>
                                </section>

                                {{-- STEP 5: Discount + Payment method --}}
                                <section x-show="step === 5" x-transition x-cloak>
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <h5 class="mb-2">5) Discount + Payment method</h5>
                                            <p class="text-muted mb-0">Setează discount-ul și metoda de plată (cash/card/bank/provider).</p>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Discount amount</label>
                                            <input type="number" step="0.01" min="0" class="form-control"
                                                   x-model.number="discountAmount" @input="refreshQuoteDebounced()">
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Tax amount</label>
                                            <input type="number" step="0.01" min="0" class="form-control"
                                                   x-model.number="taxAmount" @input="refreshQuoteDebounced()">
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Payment method *</label>
                                            <select class="form-control"
                                                    x-model="paymentMethod"
                                                    @change="refreshQuoteDebounced()"
                                                    :disabled="loadingPaymentMethods">
                                                <option value="">Select</option>
                                                <template x-for="m in paymentMethods" :key="m.key">
                                                    <option :value="m.key" x-text="m.label"></option>
                                                </template>
                                            </select>
                                            <div class="form-text">
                                                Ex: cash / bank / Stripe / PayU…
                                                <span class="ms-2" x-show="loadingPaymentMethods" x-cloak>
                                                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                                    Loading…
                                                </span>
                                            </div>
                                        </div>

                                        <div class="col-12 d-flex gap-2">
                                            <button class="btn btn-outline-secondary" type="button" @click="prev()" :disabled="isBusy()">Back</button>
                                            <button class="btn btn-primary" type="button" :disabled="!paymentMethod || isBusy()" @click="next()">
                                                Next
                                            </button>
                                        </div>
                                    </div>
                                </section>

                                {{-- STEP 6: Summary + submit --}}
                                <section x-show="step === 6" x-transition x-cloak>
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <h5 class="mb-2">6) Rezumat</h5>
                                            <p class="text-muted mb-0">Verifică breakdown-ul și finalizează booking-ul.</p>
                                        </div>

                                        <div class="col-12" x-show="quoteError" x-cloak>
                                            <div class="alert alert-danger mb-0" x-text="quoteError"></div>
                                        </div>

                                        <div class="col-12" x-show="loadingQuote" x-cloak>
                                            <div class="alert alert-light border mb-0 d-flex align-items-center gap-2">
                                                <div class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></div>
                                                <div class="small">Calculating quote…</div>
                                            </div>
                                        </div>

                                        <div class="col-lg-7">
                                            <div class="border rounded p-3">
                                                <div class="fw-semibold mb-2">Breakdown</div>

                                                <template x-if="quote">
                                                    <div>
                                                        <div class="d-flex justify-content-between">
                                                            <span class="text-muted">Tickets subtotal</span>
                                                            <span class="fw-semibold" x-text="money(quote.tickets_subtotal)"></span>
                                                        </div>

                                                        <div class="mt-2">
                                                            <div class="small text-muted mb-1">Tickets lines</div>
                                                            <div class="d-flex justify-content-between">
                                                                <span>Adult</span><span x-text="money(quote.tickets.adult)"></span>
                                                            </div>
                                                            <div class="d-flex justify-content-between">
                                                                <span>Child</span><span x-text="money(quote.tickets.child)"></span>
                                                            </div>
                                                            <div class="d-flex justify-content-between">
                                                                <span>Baby</span><span x-text="money(quote.tickets.baby)"></span>
                                                            </div>
                                                            <div class="d-flex justify-content-between">
                                                                <span>Infant</span><span x-text="money(quote.tickets.infant)"></span>
                                                            </div>
                                                        </div>

                                                        <hr>

                                                        <div class="d-flex justify-content-between">
                                                            <span class="text-muted">Extras total</span>
                                                            <span class="fw-semibold" x-text="money(quote.extras_total)"></span>
                                                        </div>

                                                        <template x-if="quote.extras && quote.extras.length">
                                                            <div class="mt-2">
                                                                <div class="small text-muted mb-1">Extras lines</div>
                                                                <template x-for="l in quote.extras" :key="l.id">
                                                                    <div class="d-flex justify-content-between">
                                                                        <span x-text="l.name"></span>
                                                                        <span x-text="money(l.amount)"></span>
                                                                    </div>
                                                                </template>
                                                            </div>
                                                        </template>

                                                        <hr>

                                                        <div class="d-flex justify-content-between">
                                                            <span class="text-muted">Pickup</span>
                                                            <span class="fw-semibold" x-text="money(quote.pickup_amount)"></span>
                                                        </div>

                                                        <div class="d-flex justify-content-between">
                                                            <span class="text-muted">Discount</span>
                                                            <span class="fw-semibold">- <span x-text="money(quote.discount_amount)"></span></span>
                                                        </div>

                                                        <div class="d-flex justify-content-between">
                                                            <span class="text-muted">Subtotal</span>
                                                            <span class="fw-bold" x-text="money(quote.subtotal)"></span>
                                                        </div>

                                                        <div class="d-flex justify-content-between">
                                                            <span class="text-muted">Tax</span>
                                                            <span class="fw-semibold" x-text="money(quote.tax_amount)"></span>
                                                        </div>

                                                        <div class="d-flex justify-content-between mt-1">
                                                            <span class="fw-bold">Total</span>
                                                            <span class="fw-bold" style="font-size:1.1rem;" x-text="money(quote.total)"></span>
                                                        </div>
                                                    </div>
                                                </template>

                                                <template x-if="!quote && !loadingQuote">
                                                    <div class="text-muted">No quote yet.</div>
                                                </template>

                                                <div class="mt-3 d-flex gap-2">
                                                    <button class="btn btn-outline-primary"
                                                            type="button"
                                                            @click="refreshQuote()"
                                                            :disabled="loadingQuote || isBusy()">
                                                        Recalculate
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-5">
                                            <div class="border rounded p-3 bg-light">
                                                <div class="fw-semibold mb-2">Selection</div>
                                                <div class="small text-muted">Client</div>
                                                <div class="fw-semibold" x-text="selectedClient?.name || '-'"></div>

                                                <hr class="my-2">

                                                <div class="small text-muted">Date</div>
                                                <div class="fw-semibold" x-text="selectedDate"></div>

                                                <div class="small text-muted mt-2">Service</div>
                                                <div class="fw-semibold" x-text="selectedService?.title || '-'"></div>

                                                <div class="small text-muted mt-2">Payment</div>
                                                <div class="fw-semibold" x-text="paymentMethod || '-'"></div>

                                                <hr class="my-2">

                                                {{-- FINAL FORM SUBMIT --}}
                                                <form method="POST"
                                                      action="{{ route('agency.tourbooking.bookings.store') }}"
                                                      @submit.prevent="submitFinal($event)">
                                                    @csrf

                                                    <input type="hidden" name="agency_client_id" :value="selectedClient?.id || ''">
                                                    <input type="hidden" name="service_id" :value="selectedService?.id || ''">

                                                    <input type="hidden" name="check_in_date" :value="selectedDate">
                                                    <input type="hidden" name="availability_id" :value="quote?.availability_id || (selectedService?.availability?.id ?? '')">

                                                    {{-- legacy fields (dacă store-ul le folosește) --}}
                                                    <input type="hidden" name="adults"   :value="pax.adult">
                                                    <input type="hidden" name="children" :value="pax.child">
                                                    <input type="hidden" name="babies"   :value="pax.baby">
                                                    <input type="hidden" name="infants"  :value="pax.infant">

                                                    {{-- IMPORTANT: trimitem și array real (ca Laravel să-l vadă ca array) --}}
                                                    <input type="hidden" name="age_quantities[adult]"  :value="pax.adult">
                                                    <input type="hidden" name="age_quantities[child]"  :value="pax.child">
                                                    <input type="hidden" name="age_quantities[baby]"   :value="pax.baby">
                                                    <input type="hidden" name="age_quantities[infant]" :value="pax.infant">

                                                    {{-- păstrăm și JSON (pt debug / compatibilitate) --}}
                                                    <input type="hidden" name="age_quantities_json" :value="JSON.stringify(pax)">

                                                    {{-- extras_payload ca ARRAY real + JSON fallback --}}
                                                    <template x-for="(row, idx) in buildExtrasPayload()" :key="row.id">
                                                        <div style="display:none">
                                                            <input type="hidden" :name="`extras_payload[${idx}][id]`" :value="row.id">
                                                            <input type="hidden" :name="`extras_payload[${idx}][active]`" :value="row.active ? 1 : 0">
                                                            <input type="hidden" :name="`extras_payload[${idx}][quantities][adult]`"  :value="row.quantities?.adult ?? 0">
                                                            <input type="hidden" :name="`extras_payload[${idx}][quantities][child]`"  :value="row.quantities?.child ?? 0">
                                                            <input type="hidden" :name="`extras_payload[${idx}][quantities][baby]`"   :value="row.quantities?.baby ?? 0">
                                                            <input type="hidden" :name="`extras_payload[${idx}][quantities][infant]`" :value="row.quantities?.infant ?? 0">
                                                        </div>
                                                    </template>
                                                    <input type="hidden" name="extras_payload_json" :value="JSON.stringify(buildExtrasPayload())">

                                                    <input type="hidden" name="pickup_point_id" :value="pickupPointId || ''">
                                                    <input type="hidden" name="pickup_extra_charge" :value="quote?.pickup_amount ?? pickupCharge ?? 0">

                                                    <input type="hidden" name="discount_amount" :value="quote?.discount_amount || 0">
                                                    <input type="hidden" name="tax_amount" :value="quote?.tax_amount || 0">
                                                    <input type="hidden" name="extra_charges" :value="(quote?.extras_total || 0) + (quote?.pickup_amount || pickupCharge || 0)">
                                                    <input type="hidden" name="subtotal" :value="quote?.subtotal || 0">
                                                    <input type="hidden" name="total" :value="quote?.total || 0">

                                                    <input type="hidden" name="payment_method" :value="paymentMethod">

                                                    {{-- IMPORTANT: status corect pentru cash_paid/bank_paid --}}
                                                    <input type="hidden" name="payment_status" :value="paymentStatusComputed()">
                                                    <input type="hidden" name="booking_status" :value="bookingStatusComputed()">
                                                    <input type="hidden" name="paid_amount" :value="paidAmountComputed()">

                                                    <input type="hidden" name="admin_notes" :value="adminNotesPayload()">

                                                    <div class="d-grid gap-2">
                                                        <button class="btn btn-primary"
                                                                type="submit"
                                                                :disabled="!canSubmitFinal() || loadingQuote">
                                                            <span x-text="isCardLike(paymentMethod) ? 'Create booking & Pay now' : 'Create booking'"></span>
                                                        </button>

                                                        <button class="btn btn-outline-secondary" type="button" @click="prev()" :disabled="isBusy()">
                                                            Back
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>

                                            <div class="small text-muted mt-2">
                                                Dacă metoda e gateway (Stripe/PayU), controller-ul de store trebuie să redirecționeze către flow-ul de plată.
                                            </div>
                                        </div>
                                    </div>
                                </section>

                            </div>
                        </div>

                    </div><!-- crancy-dsinner -->
                </div><!-- crancy-body -->
            </div>
        </div>
    </div>
</main>
@endsection

@push('style_section')
<style>
  [x-cloak]{ display:none !important; }
  .card { border-radius: 14px; }
  .btn.disabled, .btn:disabled { pointer-events:none; }
</style>

{{-- flatpickr CSS (pune-l în HEAD) --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endpush

@push('js_section')
{{-- IMPORTANT:
   - Dacă master_layout deja include Alpine/flatpickr, scoate liniile de mai jos.
   - Dacă nu, astea merg ok (defer). --}}
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr" defer></script>

<script>
window.bookingWizard = function () {
  return {
    step: 1,
    steps: [
      {id:1, label:'1) Client'},
      {id:2, label:'2) Date + Pax'},
      {id:3, label:'3) Tour'},
      {id:4, label:'4) Extras + Pickup'},
      {id:5, label:'5) Discount + Pay'},
      {id:6, label:'6) Summary'},
    ],

    // loading flags (NON-blocking)
    loadingClients: false,
    loadingMap: false,
    loadingServices: false,
    loadingExtras: false,
    loadingPickup: false,
    loadingPaymentMethods: false,
    loadingQuote: false,

    _loadingSince: {},

    // Step 1
    clientQuery: '',
    clients: [],
    selectedClient: null,

    // Step 2
    selectedDate: (function localISODate(){
      const d = new Date();
      d.setMinutes(d.getMinutes() - d.getTimezoneOffset());
      return d.toISOString().slice(0,10);
    })(),
    availabilityMap: {},
    pax: { adult: 1, child: 0, baby: 0, infant: 0 },

    // Step 3
    serviceQuery: '',
    services: [],
    selectedService: null,

    // Step 4
    extras: [],
    extrasState: {},
    pickupPoints: [],
    pickupPointId: '',
    pickupCharge: 0,

    // Step 5
    discountAmount: 0,
    taxAmount: 0,
    paymentMethods: [],
    paymentMethod: 'cash',

    // Step 6
    quote: null,
    quoteError: '',

    routes: {
      clients: @json(route('agency.tourbooking.bookings.wizard.clients')),
      availabilityMap: @json(route('agency.tourbooking.bookings.wizard.availability-map')),
      services: @json(route('agency.tourbooking.bookings.wizard.services')),
      servicesBase: @json(url('agency/tourbooking/bookings/wizard/services')), // /{service}/extras, /{service}/pickup-points
      pickupCalc: @json(route('agency.tourbooking.bookings.wizard.pickup-points.calculate-charge')),
      quote: @json(route('agency.tourbooking.bookings.wizard.quote')),
      paymentMethods: @json(route('agency.tourbooking.bookings.wizard.payment-methods')),
    },

    init(){
      setInterval(() => this._killStuckLoading(20000), 2000);

      this.loadAvailabilityMap().then(() => this.initDatepicker());
      this.loadPaymentMethods();
    },

    csrf(){
      const el = document.querySelector('meta[name="csrf-token"]');
      return el ? el.getAttribute('content') : '';
    },

    notify(msg, type='error'){
      try{
        if (window.toastr) {
          toastr.options = toastr.options || {};
          toastr.options.closeButton = true;
          toastr.options.timeOut = 6000;
          (toastr[type] || toastr.error).call(toastr, msg);
          return;
        }
      }catch(e){}
      console[type === 'error' ? 'error' : 'log'](msg);
    },

    isBusy(){
      return !!(this.loadingClients || this.loadingMap || this.loadingServices || this.loadingExtras || this.loadingPickup || this.loadingPaymentMethods || this.loadingQuote);
    },

    busyText(){
      if (this.loadingClients) return 'Loading clients…';
      if (this.loadingMap) return 'Loading available dates…';
      if (this.loadingServices) return 'Loading services…';
      if (this.loadingExtras) return 'Loading extras…';
      if (this.loadingPickup) return 'Loading pickup / calculating charge…';
      if (this.loadingPaymentMethods) return 'Loading payment methods…';
      if (this.loadingQuote) return 'Calculating quote…';
      return 'Working…';
    },

    _setLoading(key, val){
      const prop = 'loading' + key.charAt(0).toUpperCase() + key.slice(1);
      this[prop] = !!val;
      if (val) this._loadingSince[prop] = Date.now();
      else delete this._loadingSince[prop];
    },

    _killStuckLoading(maxMs){
      const now = Date.now();
      Object.keys(this._loadingSince).forEach(prop => {
        if ((now - this._loadingSince[prop]) > maxMs) {
          this[prop] = false;
          delete this._loadingSince[prop];
          this.notify('Un request a rămas blocat prea mult (timeout). UI deblocat.', 'warning');
        }
      });
    },

    qs(params){
      const usp = new URLSearchParams();
      Object.keys(params || {}).forEach(k => {
        const v = params[k];
        if (v === null || v === undefined || v === '') return;
        if (typeof v === 'object') {
          Object.keys(v).forEach(sk => usp.append(`${k}[${sk}]`, v[sk]));
        } else {
          usp.append(k, v);
        }
      });
      return usp.toString();
    },

    range(min, max){
      const out = [];
      for (let i=min; i<=max; i++) out.push(i);
      return out;
    },

    totalPax(){
      return (Number(this.pax.adult)||0) + (Number(this.pax.child)||0) + (Number(this.pax.baby)||0) + (Number(this.pax.infant)||0);
    },

    clampPax(){
      this.pax.adult = Math.max(1, Number(this.pax.adult)||1);
      this.pax.child = Math.max(0, Number(this.pax.child)||0);
      this.pax.baby = Math.max(0, Number(this.pax.baby)||0);
      this.pax.infant = Math.max(0, Number(this.pax.infant)||0);
    },

    isStep2Valid(){
      return this.selectedDate && (Number(this.pax.adult)||0) >= 1 && this.totalPax() > 0;
    },

    money(v){
      const n = Number(v || 0);
      return n.toFixed(2);
    },

    async fetchJson(url, opts = {}){
      const { method='GET', headers={}, body=null, timeoutMs=12000 } = opts;

      const controller = new AbortController();
      const timer = setTimeout(() => controller.abort(), timeoutMs);

      try{
        const r = await fetch(url, {
          method,
          headers: { 'Accept':'application/json', ...headers },
          body,
          signal: controller.signal,
        });

        const text = await r.text();
        let data = null;
        try { data = text ? JSON.parse(text) : null; } catch(e) {}

        if (!r.ok) {
          const msg = (data && (data.message || data.error)) ? (data.message || data.error) : `Request failed: ${r.status}`;
          throw new Error(msg);
        }
        if (data === null) throw new Error('Server returned non-JSON (HTML/redirect).');

        return data;
      } catch(e){
        if (e.name === 'AbortError') throw new Error('Request timeout.');
        throw e;
      } finally {
        clearTimeout(timer);
      }
    },

    // ---------------- step nav ----------------
    canGoTo(target){
      if (target === 1) return true;
      if (target === 2) return !!this.selectedClient;
      if (target === 3) return !!this.selectedClient && this.isStep2Valid();
      if (target === 4) return !!this.selectedService;
      if (target === 5) return !!this.selectedService;
      if (target === 6) return !!this.selectedService && !!this.paymentMethod;
      return false;
    },

    goTo(target){
      if (!this.canGoTo(target) || this.isBusy()) return;
      this.step = target;
      this.onEnterStep();
    },

    next(){
      const t = this.step + 1;
      if (this.canGoTo(t) && !this.isBusy()) { this.step = t; this.onEnterStep(); }
    },

    prev(){
      if (this.isBusy()) return;
      this.step = Math.max(1, this.step - 1);
      this.onEnterStep();
    },

    onEnterStep(){
      if (this.step === 3) this.loadServices();
      if (this.step === 4) this.loadExtrasAndPickup();
      if (this.step === 5) this.loadPaymentMethods();
      if (this.step === 6) this.refreshQuote();
    },

    // ---------------- Step 1 ----------------
    async searchClients(){
      this._setLoading('clients', true);
      try{
        const url = this.routes.clients + '?' + this.qs({ q: this.clientQuery });
        const j = await this.fetchJson(url, { timeoutMs: 12000 });
        this.clients = (j.data || []);
      }catch(e){
        this.clients = [];
        this.notify('Nu pot încărca clienții: ' + e.message, 'error');
      }finally{
        this._setLoading('clients', false);
      }
    },

    selectClient(c){ this.selectedClient = c; },

    // ---------------- Step 2 ----------------
    async loadAvailabilityMap(){
      this._setLoading('map', true);
      try{
        const url = this.routes.availabilityMap + '?' + this.qs({ days: 180 });
        const j = await this.fetchJson(url, { timeoutMs: 20000 });
        this.availabilityMap = j.data || {};
      }catch(e){
        this.availabilityMap = {};
        this.notify('Nu pot încărca availabilityMap: ' + e.message, 'error');
      }finally{
        this._setLoading('map', false);
      }
    },

    initDatepicker(){
      if (!this.$refs.dateInput) return;

      const enabledDates = Object.keys(this.availabilityMap || {})
        .filter(d => this.availabilityMap[d] && this.availabilityMap[d].is_available);

      if (typeof flatpickr === 'undefined') {
        this.$refs.dateInput.value = this.selectedDate;
        return;
      }

      const opts = {
        dateFormat: "Y-m-d",
        minDate: "today",
        defaultDate: this.selectedDate,
        disableMobile: true,
        onChange: (selectedDates, dateStr) => {
          this.selectedDate = dateStr;
          this.resetAfterDateOrPaxChange();
        }
      };

      // IMPORTANT: doar dacă avem date enabled; altfel nu blocăm complet datepicker-ul
      if (enabledDates.length) {
        opts.enable = enabledDates;
      }

      flatpickr(this.$refs.dateInput, opts);
      this.$refs.dateInput.value = this.selectedDate;
    },

    resetAfterDateOrPaxChange(){
      this.selectedService = null;
      this.services = [];
      this.extras = [];
      this.extrasState = {};
      this.pickupPoints = [];
      this.pickupPointId = '';
      this.pickupCharge = 0;
      this.quote = null;
      this.quoteError = '';
    },

    onPaxChange(){
      this.clampPax();

      // clamp quantities pentru extras per_age active
      Object.keys(this.extrasState || {}).forEach(id => {
        const st = this.extrasState[id];
        if (!st?.active || !st?.quantities) return;
        st.quantities.adult = Math.min(st.quantities.adult||0, this.pax.adult||0);
        st.quantities.child = Math.min(st.quantities.child||0, this.pax.child||0);
        st.quantities.baby  = Math.min(st.quantities.baby||0,  this.pax.baby||0);
        st.quantities.infant= Math.min(st.quantities.infant||0,this.pax.infant||0);
      });

      this.refreshQuoteDebounced();
    },

    // ---------------- Step 3 ----------------
    async loadServices(){
      if (!this.isStep2Valid()) return;

      this._setLoading('services', true);
      try{
        const url = this.routes.services + '?' + this.qs({ date: this.selectedDate, pax: this.pax, q: this.serviceQuery });
        const j = await this.fetchJson(url, { timeoutMs: 20000 });
        this.services = (j.data || []);
      }catch(e){
        this.services = [];
        this.notify('Nu pot încărca services: ' + e.message, 'error');
      }finally{
        this._setLoading('services', false);
      }
    },

    selectService(s){
      if (!s?.availability?.is_available) return;
      this.selectedService = s;

      // reset downstream
      this.extras = [];
      this.extrasState = {};
      this.pickupPoints = [];
      this.pickupPointId = '';
      this.pickupCharge = 0;
      this.quote = null;
      this.quoteError = '';
    },

    // ---------------- Step 4 ----------------
    async loadExtrasAndPickup(){
      if (!this.selectedService?.id) return;

      const serviceId = this.selectedService.id;

      this._setLoading('extras', true);
      this._setLoading('pickup', true);

      try{
        const extrasUrl = `${this.routes.servicesBase}/${serviceId}/extras?` + this.qs({ pax: this.pax });
        const pickupUrl = `${this.routes.servicesBase}/${serviceId}/pickup-points`;

        const [ex, pp] = await Promise.all([
          this.fetchJson(extrasUrl, { timeoutMs: 20000 }),
          this.fetchJson(pickupUrl, { timeoutMs: 20000 }),
        ]);

        this.extras = (ex.data || []);
        this.pickupPoints = (pp.data || []);

        // init state
        const st = {};
        this.extras.forEach(x => {
          st[x.id] = {
            active: !!x.is_mandatory,
            quantities: {
              adult:  this.pax.adult || 0,
              child:  this.pax.child || 0,
              baby:   this.pax.baby || 0,
              infant: this.pax.infant || 0,
            }
          };
        });
        this.extrasState = st;

      }catch(e){
        this.extras = [];
        this.pickupPoints = [];
        this.notify('Nu pot încărca extras/pickup: ' + e.message, 'error');
      }finally{
        this._setLoading('extras', false);
        this._setLoading('pickup', false);
      }

      this.refreshQuoteDebounced();
    },

    toggleExtra(id){
      const ex = this.extras.find(x => String(x.id) === String(id));
      if (!ex) return;

      if (!this.extrasState[id]) {
        this.extrasState[id] = { active:false, quantities:{adult:0,child:0,baby:0,infant:0} };
      }

      if (ex.is_mandatory) {
        this.extrasState[id].active = true;
        return;
      }

      const now = !this.extrasState[id].active;
      this.extrasState[id].active = now;

      if (ex.charge_type === 'per_age' && !ex.apply_to_all_persons) {
        if (now) {
          this.extrasState[id].quantities = {
            adult:  this.pax.adult || 0,
            child:  this.pax.child || 0,
            baby:   this.pax.baby || 0,
            infant: this.pax.infant || 0,
          };
        } else {
          this.extrasState[id].quantities = {adult:0,child:0,baby:0,infant:0};
        }
      }

      this.onExtrasChange();
    },

    onExtrasChange(){
      this.refreshQuoteDebounced();
    },

    async onPickupChange(){
      if (!this.pickupPointId) {
        this.pickupCharge = 0;
        this.refreshQuoteDebounced();
        return;
      }

      this._setLoading('pickup', true);
      try{
        const payload = {
          pickup_point_id: Number(this.pickupPointId),
          age_quantities: this.pax,
        };

        const j = await this.fetchJson(this.routes.pickupCalc, {
          method: 'POST',
          headers: {
            'Content-Type':'application/json',
            'X-CSRF-TOKEN': this.csrf(),
          },
          body: JSON.stringify(payload),
          timeoutMs: 15000,
        });

        this.pickupCharge = Number(j?.data?.amount ?? j?.data?.extra_charge ?? 0);
      }catch(e){
        this.pickupCharge = 0;
        this.notify('Nu pot calcula pickup: ' + e.message, 'error');
      }finally{
        this._setLoading('pickup', false);
      }

      this.refreshQuoteDebounced();
    },

    // ---------------- Step 5 ----------------
    async loadPaymentMethods(){
      this._setLoading('paymentMethods', true);
      try{
        const j = await this.fetchJson(this.routes.paymentMethods, { timeoutMs: 15000 });
        this.paymentMethods = (j.data || []);
        if (!this.paymentMethod) this.paymentMethod = (this.paymentMethods[0]?.key || 'cash');
      }catch(e){
        this.paymentMethods = [{key:'cash', label:'Cash'},{key:'bank', label:'Bank transfer'}];
        if (!this.paymentMethod) this.paymentMethod = 'cash';
        this.notify('Payment methods fallback: ' + e.message, 'warning');
      }finally{
        this._setLoading('paymentMethods', false);
      }
    },

    // ---------------- Quote ----------------
    buildExtrasPayload(){
      const out = [];
      (this.extras || []).forEach(ex => {
        const st = this.extrasState[ex.id];
        if (!st) return;
        out.push({
          id: ex.id,
          active: !!st.active,
          quantities: st.quantities || {adult:0,child:0,baby:0,infant:0},
        });
      });
      return out;
    },

    refreshQuoteDebounced: (function(){
      let t = null;
      return function(){
        clearTimeout(t);
        t = setTimeout(() => this.refreshQuote(), 350);
      }
    })(),

    async refreshQuote(){
      if (!this.selectedService?.id) return;

      this._setLoading('quote', true);
      this.quoteError = '';

      try{
        const payload = {
          service_id: this.selectedService.id,
          date: this.selectedDate,
          age_quantities: this.pax,
          extras_payload: this.buildExtrasPayload(),
          pickup_point_id: this.pickupPointId ? Number(this.pickupPointId) : null,
          discount_amount: Number(this.discountAmount || 0),
          tax_amount: Number(this.taxAmount || 0),
          payment_method: this.paymentMethod || 'cash',
        };

        const j = await this.fetchJson(this.routes.quote, {
          method: 'POST',
          headers: {
            'Content-Type':'application/json',
            'X-CSRF-TOKEN': this.csrf(),
          },
          body: JSON.stringify(payload),
          timeoutMs: 20000,
        });

        this.quote = j.data || null;

      }catch(e){
        this.quote = null;
        this.quoteError = e.message || 'Quote error';
      }finally{
        this._setLoading('quote', false);
      }
    },

    // ---------------- Payment mapping (IMPORTANT) ----------------
    isPaidOffline(){
      return (this.paymentMethod === 'cash_paid' || this.paymentMethod === 'bank_paid');
    },

    paymentStatusComputed(){
      // conform cerinței: cash_paid/bank_paid => completed
      return this.isPaidOffline() ? 'completed' : 'pending';
    },

    bookingStatusComputed(){
      // poți schimba dacă la tine e 'confirmed'/'completed'
      return this.isPaidOffline() ? 'completed' : 'pending';
    },

    paidAmountComputed(){
      return this.isPaidOffline() ? Number(this.quote?.total || 0) : 0;
    },

    // ---------------- Final submit ----------------
    isCardLike(method){
      const m = String(method || '').toLowerCase();
      if (!m) return false;
      if (m === 'cash' || m === 'bank' || m === 'cash_paid' || m === 'bank_paid') return false;
      return true;
    },

    canSubmitFinal(){
      return !!this.selectedClient?.id
        && !!this.selectedService?.id
        && !!this.selectedDate
        && (Number(this.pax.adult)||0) >= 1
        && !!this.paymentMethod
        && !!this.quote
        && !this.loadingQuote;
    },

    adminNotesPayload(){
      return JSON.stringify({
        source: 'agency-wizard',
        pax: this.pax,
        extras_payload: this.buildExtrasPayload(),
        pickup_point_id: this.pickupPointId || null,
        payment_method: this.paymentMethod,
        availability_id: this.quote?.availability_id || null,
        computed_payment_status: this.paymentStatusComputed(),
        computed_paid_amount: this.paidAmountComputed(),
      });
    },

    submitFinal(e){
      if (!this.canSubmitFinal()) {
        this.notify('Nu poți crea booking-ul: lipsesc date sau quote-ul nu e calculat.', 'error');
        return;
      }
      e.target.submit();
    },
  }
}
</script>
@endpush