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

@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if($activeTrip)
    {{-- Active Trip Card --}}
    <div class="card">
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
                @if($activeTrip->trip_mode === 'client')
                    <div class="info-row">
                        <strong>Trip Type:</strong> Client / Business
                    </div>
                    @if($settings['client_presence']['enabled'] ?? false)
                        <div class="info-row">
                            <strong>{{ $settings['client_presence']['label'] ?? 'Client' }} Present:</strong>
                            {{ $activeTrip->client_present ? 'Yes' : 'No' }}
                        </div>
                        @if($settings['client_presence']['enable_addresses'] ?? false && $activeTrip->client_address)
                            <div class="info-row">
                                <strong>Client Address:</strong> {{ $activeTrip->client_address }}
                            </div>
                        @endif
                    @endif
                @else
                    <div class="info-row">
                        <strong>Trip Type:</strong> Internal
                    </div>
                @endif
            </div>

            <form method="POST" action="/app/sharpfleet/trips/end" class="mt-4">
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
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Start a Trip</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="/app/sharpfleet/trips/start">
                @csrf

                {{-- Vehicle --}}
                <div class="form-group">
                    <label class="form-label">Vehicle</label>
                    <input type="text" id="vehicleSearchInput" class="form-control" placeholder="Start typing to search (e.g. black toyota / camry / ABC123)">
                    <div id="vehicleSearchHint" class="hint-text">Showing {{ $vehicles->count() }} vehicles</div>
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

                {{-- Trip type --}}
                <div class="form-group">
                    <label class="form-label">Trip type</label>
                    <div class="radio-group">
                        <label class="radio-label">
                            <input type="radio" name="trip_mode" value="client" checked>
                            Client / business trip
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="trip_mode" value="no_client">
                            No client / internal
                        </label>
                    </div>
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

    {{-- Minimal JS for start trip form --}}
    <script>
        const vehicleSelect = document.getElementById('vehicleSelect');
        const vehicleSearchInput = document.getElementById('vehicleSearchInput');
        const vehicleSearchHint = document.getElementById('vehicleSearchHint');
        const startKmInput  = document.getElementById('startKmInput');
        const lastKmHint    = document.getElementById('lastKmHint');
        const startReadingLabel = document.getElementById('startReadingLabel');

        const customerBlock = document.getElementById('customerBlock');
        const customerSelect = document.getElementById('customerSelect');
        const customerNameInput = document.getElementById('customerNameInput');

        const tripModeRadios = document.querySelectorAll('input[name="trip_mode"]');

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
            const selected = document.querySelector('input[name="trip_mode"]:checked');
            const isClientTrip = selected && selected.value === 'client';
            customerBlock.style.display = isClientTrip ? '' : 'none';
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
    </script>
@endif
@endsection
