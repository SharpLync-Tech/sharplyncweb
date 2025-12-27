@extends('layouts.sharpfleet')

@section('title', 'Driver Dashboard')

@section('sharpfleet-content')
@php
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Schema;
    use App\Services\SharpFleet\CompanySettingsService;

    $user = session('sharpfleet.user');

    $settingsService = new CompanySettingsService($user['organisation_id']);
    $settings = $settingsService->all();

    $allowPrivateTrips = (bool) ($settings['trip']['allow_private_trips'] ?? false);

    $vehicles = DB::connection('sharpfleet')
        ->table('vehicles')
        ->where('organisation_id', $user['organisation_id'])
        ->where('is_active', 1)
        ->orderBy('name')
        ->get();

    $customers = collect();
    if (($settings['customer']['enabled'] ?? false) && Schema::connection('sharpfleet')->hasTable('customers')) {
        $customers = DB::connection('sharpfleet')
            ->table('customers')
            ->where('organisation_id', $user['organisation_id'])
            ->where('is_active', 1)
            ->orderBy('name')
            ->limit(500)
            ->get();
    }

    $lastTrips = DB::connection('sharpfleet')
        ->table('trips')
        ->select('vehicle_id', 'end_km')
        ->where('organisation_id', $user['organisation_id'])
        ->whereNotNull('ended_at')
        ->orderByDesc('ended_at')
        ->get()
        ->keyBy('vehicle_id');

    // Check for active trip
    $activeTrip = DB::connection('sharpfleet')
        ->table('trips')
        ->join('vehicles', 'trips.vehicle_id', '=', 'vehicles.id')
        ->select('trips.*', 'vehicles.name as vehicle_name', 'vehicles.registration_number', 'vehicles.tracking_mode')
        ->where('trips.user_id', $user['id'])
        ->where('trips.organisation_id', $user['organisation_id'])
        ->whereNotNull('trips.started_at')
        ->whereNull('trips.ended_at')
        ->first();
@endphp

<div id="offlineTripAlert" class="alert alert-info" style="display:none;"></div>

<div class="alert alert-info">
    Trips (start/end + readings) can be captured offline once this Driver Dashboard has been opened online at least once.
</div>

@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if($activeTrip)
    {{-- Active Trip Card --}}
    <div class="card" id="serverActiveTripCard">
        <div class="card-header">
            <h3 class="card-title">Trip in Progress</h3>
        </div>
        <div class="card-body">
            <div class="trip-info">
                <div class="info-row">
                    <strong>Vehicle:</strong> {{ $activeTrip->vehicle_name }} ({{ $activeTrip->registration_number }})
                </div>
                <div class="info-row">
                    <strong>Started:</strong> {{ \Carbon\Carbon::parse($activeTrip->started_at)->format('M j, Y g:i A') }}
                </div>
                <div class="info-row">
                    <strong>
                        {{ ($activeTrip->tracking_mode ?? 'distance') === 'hours' ? 'Starting Hours:' : 'Starting KM:' }}
                    </strong>
                    {{ number_format($activeTrip->start_km) }}
                </div>
                @php
                    // Backwards compatible: legacy values ('client' / 'no_client') are treated as Business.
                    $tripMode = (string) ($activeTrip->trip_mode ?? 'business');
                    $tripTypeLabel = $tripMode === 'private' ? 'Private' : 'Business';
                    $isBusinessTrip = $tripMode !== 'private';
                @endphp

                <div class="info-row">
                    <strong>Trip Type:</strong> {{ $tripTypeLabel }}
                </div>

                @if($isBusinessTrip && ($settings['client_presence']['enabled'] ?? false))
                    <div class="info-row">
                        <strong>{{ $settings['client_presence']['label'] ?? 'Client' }} Present:</strong>
                        {{ $activeTrip->client_present ? 'Yes' : 'No' }}
                    </div>
                    @if(($settings['client_presence']['enable_addresses'] ?? false) && $activeTrip->client_address)
                        <div class="info-row">
                            <strong>Client Address:</strong> {{ $activeTrip->client_address }}
                        </div>
                    @endif
                @endif
            </div>

            <form method="POST" action="/app/sharpfleet/trips/end" class="mt-4" id="endTripForm">
                @csrf
                <input type="hidden" name="trip_id" value="{{ $activeTrip->id }}">

                <div class="form-group">
                    <label class="form-label">
                        {{ ($activeTrip->tracking_mode ?? 'distance') === 'hours'
                            ? 'Ending hour meter (hours)'
                            : 'Ending odometer (km)'
                        }}
                    </label>
                    <input type="number" name="end_km" class="form-control" inputmode="numeric" required min="{{ (int) $activeTrip->start_km }}" placeholder="e.g. 124600">
                </div>

                <button type="submit" class="btn btn-primary btn-full">End Trip</button>
            </form>
        </div>
    </div>
@else
    {{-- Start Trip Card --}}
    <div class="card" id="startTripCard">
        <div class="card-header">
            <h3 class="card-title">Start a Trip</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="/app/sharpfleet/trips/start" id="startTripForm">
                @csrf

                {{-- Vehicle --}}
                <div class="form-group">
                    <label class="form-label">Vehicle</label>
                    @if($vehicles->count() > 10)
                        <input type="text" id="vehicleSearchInput" class="form-control" placeholder="Start typing to search (e.g. black toyota / camry / ABC123)">
                        <div id="vehicleSearchHint" class="hint-text">Showing {{ $vehicles->count() }} vehicles</div>
                    @endif
                    <select id="vehicleSelect" name="vehicle_id" class="form-control" required>
                        @foreach ($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}"
                                    data-tracking-mode="{{ $vehicle->tracking_mode ?? 'distance' }}"
                                    data-last-km="{{ $lastTrips[$vehicle->id]->end_km ?? (property_exists($vehicle, 'starting_km') ? ($vehicle->starting_km ?? '') : '') }}">
                                {{ $vehicle->name }} ({{ $vehicle->registration_number }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Trip Type (Business / Private) --}}
                <div class="form-group">
                    <label class="form-label">Trip Type</label>
                    @if($allowPrivateTrips)
                        <div class="radio-group">
                            <label class="radio-label">
                                <input type="radio" name="trip_mode" value="business" checked>
                                Business
                            </label>
                            <label class="radio-label">
                                <input type="radio" name="trip_mode" value="private">
                                Private
                            </label>
                        </div>
                    @else
                        <div class="hint-text">Business</div>
                        <input type="hidden" name="trip_mode" value="business">
                    @endif
                </div>

                {{-- Client presence --}}
                @if($settings['client_presence']['enabled'] ?? false)
                    <div class="form-group">
                        <label class="form-label">
                            {{ $settings['client_presence']['label'] ?? 'Client' }} Present? {{ $settings['client_presence']['required'] ? '(Required)' : '' }}
                        </label>
                        <select name="client_present" class="form-control" {{ $settings['client_presence']['required'] ? 'required' : '' }}>
                            <option value="">— Select —</option>
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </div>

                    {{-- Client address --}}
                    @if($settings['client_presence']['enable_addresses'] ?? false)
                        <div class="form-group">
                            <label class="form-label">Client Address (for billing/job tracking)</label>
                            <input type="text" name="client_address" class="form-control" placeholder="e.g. 123 Main St, Suburb">
                        </div>
                    @endif
                @endif

                {{-- Customer / Client (optional; never blocks trip start) --}}
                @if(($settings['customer']['enabled'] ?? false) && (($settings['customer']['allow_select'] ?? true) || ($settings['customer']['allow_manual'] ?? true)))
                    <div id="customerBlock" class="form-group">
                        <label class="form-label">Customer / Client (optional)</label>

                        @if(($settings['customer']['allow_select'] ?? true) && $customers->count() > 0)
                            <select id="customerSelect" name="customer_id" class="form-control">
                                <option value="">— Select from list —</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                            <div class="hint-text">If the customer isn’t in the list, type a name below.</div>
                        @endif

                        @if($settings['customer']['allow_manual'] ?? true)
                            <input id="customerNameInput" type="text" name="customer_name" class="form-control mt-2" maxlength="150" placeholder="Or enter customer name (e.g. Jannie B / Job 12345)">
                        @endif
                    </div>
                @endif

                {{-- Start KM --}}
                <div class="form-group">
                    <label id="startReadingLabel" class="form-label">Starting odometer (km)</label>
                    <div id="lastKmHint" class="hint-text d-none"></div>
                    <input type="number" id="startKmInput" name="start_km" class="form-control" inputmode="numeric" required placeholder="e.g. 124500">
                </div>

                <button type="submit" class="btn btn-primary btn-full">Start Trip</button>
            </form>
        </div>
    </div>

    {{-- Offline active trip (shown via JS when a trip was started offline) --}}
    <div class="card" id="offlineActiveTripCard" style="display:none;">
        <div class="card-header">
            <h3 class="card-title">Trip in Progress (Offline)</h3>
        </div>
        <div class="card-body">
            <div class="trip-info">
                <div class="info-row"><strong>Vehicle:</strong> <span id="offlineTripVehicle">—</span></div>
                <div class="info-row"><strong>Started:</strong> <span id="offlineTripStarted">—</span></div>
                <div class="info-row"><strong>Starting reading:</strong> <span id="offlineTripStartKm">—</span></div>
            </div>

            <form id="offlineEndTripForm" class="mt-4">
                <div class="form-group">
                    <label class="form-label">Ending reading</label>
                    <input type="number" id="offlineEndKm" class="form-control" inputmode="numeric" required min="0" placeholder="e.g. 124600">
                </div>
                <button type="submit" class="btn btn-primary btn-full">End Trip (Offline)</button>
            </form>
        </div>
    </div>

    {{-- Minimal JS for start trip form --}}
    <script>
        const offlineTripAlert = document.getElementById('offlineTripAlert');

        function showOfflineMessage(msg) {
            if (!offlineTripAlert) return;
            offlineTripAlert.textContent = msg;
            offlineTripAlert.style.display = '';
        }

        function getCsrfToken() {
            const meta = document.querySelector('meta[name="csrf-token"]');
            return meta ? meta.getAttribute('content') : '';
        }

        function getLocalJson(key, fallback) {
            try {
                const raw = localStorage.getItem(key);
                return raw ? JSON.parse(raw) : fallback;
            } catch (e) {
                return fallback;
            }
        }

        function setLocalJson(key, value) {
            localStorage.setItem(key, JSON.stringify(value));
        }

        const OFFLINE_ACTIVE_KEY = 'sharpfleet_offline_active_trip_v1';
        const OFFLINE_COMPLETED_KEY = 'sharpfleet_offline_completed_trips_v1';

        function getOfflineActiveTrip() {
            return getLocalJson(OFFLINE_ACTIVE_KEY, null);
        }

        function setOfflineActiveTrip(trip) {
            if (trip === null) {
                localStorage.removeItem(OFFLINE_ACTIVE_KEY);
                return;
            }
            setLocalJson(OFFLINE_ACTIVE_KEY, trip);
        }

        function getOfflineCompletedTrips() {
            return getLocalJson(OFFLINE_COMPLETED_KEY, []);
        }

        function setOfflineCompletedTrips(trips) {
            setLocalJson(OFFLINE_COMPLETED_KEY, trips);
        }

        function renderOfflineActiveTrip() {
            const card = document.getElementById('offlineActiveTripCard');
            const startCard = document.getElementById('startTripCard');
            if (!card) return;

            const t = getOfflineActiveTrip();
            if (!t) {
                card.style.display = 'none';
                if (startCard) startCard.style.display = '';
                return;
            }

            card.style.display = '';
            if (startCard) startCard.style.display = 'none';

            const v = document.getElementById('offlineTripVehicle');
            const s = document.getElementById('offlineTripStarted');
            const skm = document.getElementById('offlineTripStartKm');

            if (v) v.textContent = t.vehicle_text || '—';
            if (s) {
                try { s.textContent = new Date(t.started_at).toLocaleString(); } catch (e) { s.textContent = t.started_at; }
            }
            if (skm) skm.textContent = String(t.start_km ?? '—');
        }

        async function syncOfflineTripsIfPossible() {
            if (!navigator.onLine) return;
            const completed = getOfflineCompletedTrips();
            if (!Array.isArray(completed) || completed.length === 0) return;

            try {
                const res = await fetch('/app/sharpfleet/trips/offline-sync', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken(),
                    },
                    body: JSON.stringify({ trips: completed }),
                });

                if (!res.ok) {
                    // Show the first validation message if possible.
                    let msg = 'Could not sync offline trips yet.';
                    try {
                        const data = await res.json();
                        if (data && data.message) msg = data.message;
                        if (data && data.errors) {
                            const keys = Object.keys(data.errors);
                            if (keys.length && Array.isArray(data.errors[keys[0]]) && data.errors[keys[0]][0]) {
                                msg = data.errors[keys[0]][0];
                            }
                        }
                    } catch (e) {}
                    showOfflineMessage(msg);
                    return;
                }

                const data = await res.json();
                // Clear local completed trips on success.
                setOfflineCompletedTrips([]);
                showOfflineMessage(`Offline trips synced (${(data.synced || []).length} sent).`);
                // Refresh to show latest state.
                setTimeout(() => window.location.reload(), 800);
            } catch (e) {
                // ignore network errors
            }
        }

        const vehicleSelect = document.getElementById('vehicleSelect');
        const vehicleSearchInput = document.getElementById('vehicleSearchInput');
        const vehicleSearchHint = document.getElementById('vehicleSearchHint');
        const startKmInput  = document.getElementById('startKmInput');
        const lastKmHint    = document.getElementById('lastKmHint');
        const startReadingLabel = document.getElementById('startReadingLabel');

        const customerBlock = document.getElementById('customerBlock');
        const customerSelect = document.getElementById('customerSelect');
        const customerNameInput = document.getElementById('customerNameInput');

        const tripModeRadios = document.querySelectorAll('input[name="trip_mode"][type="radio"]');
        const tripModeHidden = document.querySelector('input[name="trip_mode"][type="hidden"]');

        const allVehicleOptions = Array.from(vehicleSelect.options).map(opt => ({
            value: opt.value,
            text: opt.text,
            trackingMode: opt.dataset.trackingMode || 'distance',
            lastKm: opt.dataset.lastKm || ''
        }));

        function rebuildVehicleOptions(filtered) {
            const currentValue = vehicleSelect.value;
            vehicleSelect.innerHTML = '';

            filtered.forEach(v => {
                const opt = document.createElement('option');
                opt.value = v.value;
                opt.textContent = v.text;
                opt.dataset.trackingMode = v.trackingMode;
                opt.dataset.lastKm = v.lastKm;
                vehicleSelect.appendChild(opt);
            });

            if (filtered.length === 0) {
                const opt = document.createElement('option');
                opt.value = '';
                opt.textContent = 'No vehicles match your search';
                vehicleSelect.appendChild(opt);
                vehicleSelect.value = '';
                return;
            }

            const stillExists = filtered.some(v => v.value === currentValue);
            if (stillExists) {
                vehicleSelect.value = currentValue;
            } else {
                vehicleSelect.value = filtered[0].value;
            }

            updateStartKm();
        }

        function filterVehicles() {
            const q = (vehicleSearchInput?.value || '').trim().toLowerCase();
            const filtered = q
                ? allVehicleOptions.filter(v => v.text.toLowerCase().includes(q))
                : allVehicleOptions;

            if (vehicleSearchHint) {
                vehicleSearchHint.textContent = `Showing ${filtered.length} of ${allVehicleOptions.length} vehicles`;
            }

            rebuildVehicleOptions(filtered);
        }

        function updateStartKm() {
            const selected = vehicleSelect.options[vehicleSelect.selectedIndex];
            const lastKm   = selected.dataset.lastKm;
            const mode     = selected.dataset.trackingMode || 'distance';

            if (startReadingLabel) {
                if (mode === 'hours') {
                    startReadingLabel.textContent = 'Starting hour meter (hours)';
                    startKmInput.placeholder = 'e.g. 1250';
                } else {
                    startReadingLabel.textContent = 'Starting odometer (km)';
                    startKmInput.placeholder = 'e.g. 124500';
                }
            }

            if (lastKm) {
                startKmInput.value = lastKm;
                lastKmHint.textContent = (mode === 'hours')
                    ? `Last recorded hour meter: ${Number(lastKm).toLocaleString()} hours`
                    : `Last recorded odometer: ${Number(lastKm).toLocaleString()} km`;
                lastKmHint.classList.remove('d-none');
            } else {
                startKmInput.value = '';
                lastKmHint.classList.add('d-none');
            }
        }

        vehicleSelect.addEventListener('change', updateStartKm);
        if (vehicleSearchInput) {
            vehicleSearchInput.addEventListener('input', filterVehicles);
        }

        function updateCustomerVisibility() {
            if (!customerBlock) return;

            const selected = document.querySelector('input[name="trip_mode"][type="radio"]:checked');
            const mode = selected ? selected.value : (tripModeHidden ? tripModeHidden.value : 'business');
            const isBusinessTrip = mode !== 'private';

            customerBlock.style.display = isBusinessTrip ? '' : 'none';
        }

        if (customerSelect && customerNameInput) {
            customerSelect.addEventListener('change', () => {
                if (customerSelect.value) {
                    customerNameInput.value = '';
                }
            });

            customerNameInput.addEventListener('input', () => {
                if (customerNameInput.value.trim()) {
                    customerSelect.value = '';
                }
            });
        }

        tripModeRadios.forEach(r => r.addEventListener('change', updateCustomerVisibility));

        // Initial load
        updateStartKm();
        updateCustomerVisibility();

        // Offline trip capture (start/end + readings)
        const startTripForm = document.getElementById('startTripForm');
        const offlineEndTripForm = document.getElementById('offlineEndTripForm');
        const offlineEndKm = document.getElementById('offlineEndKm');

        function buildOfflineStartPayload(form) {
            const fd = new FormData(form);
            const payload = Object.fromEntries(fd.entries());

            const mode = payload.trip_mode ? String(payload.trip_mode) : 'business';
            // Keep only what we need to create a trip later.
            return {
                vehicle_id: Number(payload.vehicle_id),
                trip_mode: mode,
                start_km: Number(payload.start_km),
                customer_id: payload.customer_id ? Number(payload.customer_id) : null,
                customer_name: payload.customer_name ? String(payload.customer_name) : null,
                client_present: payload.client_present !== undefined && payload.client_present !== '' ? payload.client_present : null,
                client_address: payload.client_address ? String(payload.client_address) : null,
            };
        }

        if (startTripForm) {
            startTripForm.addEventListener('submit', (e) => {
                if (navigator.onLine) return; // let server handle it

                e.preventDefault();

                if (getOfflineActiveTrip()) {
                    showOfflineMessage('A trip is already in progress offline. End it before starting another.');
                    return;
                }

                const payload = buildOfflineStartPayload(startTripForm);
                if (!payload.vehicle_id || Number.isNaN(payload.vehicle_id)) {
                    showOfflineMessage('Select a vehicle before starting.');
                    return;
                }
                if (Number.isNaN(payload.start_km)) {
                    showOfflineMessage('Enter a valid starting reading.');
                    return;
                }

                const selectedOpt = vehicleSelect && vehicleSelect.options[vehicleSelect.selectedIndex];
                const vehicleText = selectedOpt ? selectedOpt.textContent : '';

                setOfflineActiveTrip({
                    ...payload,
                    started_at: new Date().toISOString(),
                    vehicle_text: vehicleText,
                });

                showOfflineMessage('No signal: trip started offline. End it to sync later.');
                renderOfflineActiveTrip();
            });
        }

        if (offlineEndTripForm && offlineEndKm) {
            offlineEndTripForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const active = getOfflineActiveTrip();
                if (!active) {
                    showOfflineMessage('No offline trip found.');
                    renderOfflineActiveTrip();
                    return;
                }

                const endKmVal = Number(offlineEndKm.value);
                if (Number.isNaN(endKmVal)) {
                    showOfflineMessage('Enter a valid ending reading.');
                    return;
                }
                if (endKmVal < Number(active.start_km)) {
                    showOfflineMessage('Ending reading must be the same as or greater than the starting reading.');
                    return;
                }

                const completedTrip = {
                    vehicle_id: active.vehicle_id,
                    trip_mode: active.trip_mode,
                    start_km: active.start_km,
                    end_km: endKmVal,
                    started_at: active.started_at,
                    ended_at: new Date().toISOString(),
                    customer_id: active.customer_id,
                    customer_name: active.customer_name,
                    client_present: active.client_present,
                    client_address: active.client_address,
                };

                const completed = getOfflineCompletedTrips();
                completed.push(completedTrip);
                setOfflineCompletedTrips(completed);
                setOfflineActiveTrip(null);

                showOfflineMessage('Trip ended offline. Will sync when signal returns.');
                renderOfflineActiveTrip();
                await syncOfflineTripsIfPossible();
            });
        }

        window.addEventListener('online', () => {
            showOfflineMessage('Back online. Syncing offline trips...');
            syncOfflineTripsIfPossible();
        });

        // Initial render/sync
        renderOfflineActiveTrip();
        syncOfflineTripsIfPossible();
    </script>
@endif
@endsection
