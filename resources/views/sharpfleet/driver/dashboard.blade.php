@extends('layouts.sharpfleet')

@section('title', 'Driver Dashboard')

@section('sharpfleet-content')
@php
    use Illuminate\Support\Facades\DB;
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

    $lastTrips = DB::connection('sharpfleet')
        ->table('trips')
        ->select('vehicle_id', 'end_km')
        ->where('organisation_id', $user['organisation_id'])
        ->whereNotNull('ended_at')
        ->orderByDesc('ended_at')
        ->get()
        ->keyBy('vehicle_id');
@endphp

<div class="mb-4">
    <h1 class="mb-2">Driver Dashboard</h1>
    <p>Welcome back, {{ $user['first_name'] }}!</p>
</div>

@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

<div class="card">
    <div class="card-header">
        <h3 class="card-title">User Info</h3>
    </div>
    <div class="mb-3">
        <strong>{{ $user['first_name'] }} {{ $user['last_name'] }}</strong><br>
        {{ $user['email'] }}
    </div>
</div>

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
                <select id="vehicleSelect" name="vehicle_id" class="form-control" required>
                    @foreach ($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}"
                                data-last-km="{{ $lastTrips[$vehicle->id]->end_km ?? '' }}">
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

            {{-- Start KM --}}
            <div class="form-group">
                <label class="form-label">Starting odometer (km)</label>
                <div id="lastKmHint" class="hint-text" style="display:none;"></div>
                <input type="number" id="startKmInput" name="start_km" class="form-control" inputmode="numeric" required placeholder="e.g. 124500">
            </div>

            <button type="submit" class="btn btn-primary btn-full">Start Trip</button>
        </form>
    </div>
</div>

<div class="mt-4">
    <form method="POST" action="/app/sharpfleet/logout">
        @csrf
        <button type="submit" class="btn btn-danger">Log out</button>
    </form>
</div>

{{-- Minimal JS --}}
<script>
    const vehicleSelect = document.getElementById('vehicleSelect');
    const startKmInput  = document.getElementById('startKmInput');
    const lastKmHint    = document.getElementById('lastKmHint');

    function updateStartKm() {
        const selected = vehicleSelect.options[vehicleSelect.selectedIndex];
        const lastKm   = selected.dataset.lastKm;

        if (lastKm) {
            startKmInput.value = lastKm;
            lastKmHint.textContent = `Last recorded odometer: ${Number(lastKm).toLocaleString()} km`;
            lastKmHint.style.display = 'block';
        } else {
            startKmInput.value = '';
            lastKmHint.style.display = 'none';
        }
    }

    vehicleSelect.addEventListener('change', updateStartKm);

    // Initial load
    updateStartKm();
</script>
@endsection
