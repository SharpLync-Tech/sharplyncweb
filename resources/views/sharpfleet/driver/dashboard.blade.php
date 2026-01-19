@extends('layouts.sharpfleet')

@section('title', 'Driver Dashboard')

@section('sharpfleet-content')
@php
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Schema;
    use App\Services\SharpFleet\CompanySettingsService;
    use App\Services\SharpFleet\BranchService;

    $user = session('sharpfleet.user');

    $settingsService = new CompanySettingsService($user['organisation_id']);
    $settings = $settingsService->all();

    $allowPrivateTrips = $settingsService->allowPrivateTrips();
    $faultsEnabled = $settingsService->faultsEnabled();
    $allowFaultsDuringTrip = $settingsService->allowFaultsDuringTrip();
    $companyTimezone = $settingsService->timezone();

    $odometerRequired = $settingsService->odometerRequired();
    $odometerAllowOverride = $settingsService->odometerAllowOverride();

    $manualTripTimesRequired = $settingsService->requireManualStartEndTimes();

    $safetyCheckEnabled = $settingsService->safetyCheckEnabled();
    $safetyCheckItems = $settingsService->safetyCheckItems();

    $branchesService = new BranchService();
    $branchesEnabled = $branchesService->branchesEnabled();
    $branchAccessEnabled = $branchesEnabled
        && $branchesService->vehiclesHaveBranchSupport()
        && $branchesService->userBranchAccessEnabled();
    $accessibleBranchIds = $branchAccessEnabled
        ? $branchesService->getAccessibleBranchIdsForUser((int) $user['organisation_id'], (int) $user['id'])
        : [];
    if ($branchAccessEnabled && count($accessibleBranchIds) === 0) {
        abort(403, 'No branch access.');
    }

    $vehicles = DB::connection('sharpfleet')
        ->table('vehicles')
        ->where('organisation_id', $user['organisation_id'])
        ->where('is_active', 1)
        ->when(
            $branchAccessEnabled,
            fn ($q) => $q->whereIn('branch_id', $accessibleBranchIds)
        )
        ->when(
            Schema::connection('sharpfleet')->hasColumn('vehicles', 'assignment_type')
                && Schema::connection('sharpfleet')->hasColumn('vehicles', 'assigned_driver_id'),
            fn ($q) => $q->where(function ($qq) use ($user) {
                $qq->whereNull('assignment_type')
                    ->orWhere('assignment_type', 'none')
                    ->orWhere(function ($qq2) use ($user) {
                        $qq2
                            ->where('assignment_type', 'permanent')
                            ->where('assigned_driver_id', (int) $user['id']);
                    });
            })
        )
        ->when(
            Schema::connection('sharpfleet')->hasColumn('vehicles', 'is_in_service'),
            fn ($q) => $q->where('is_in_service', 1)
        )
        ->orderBy('name')
        ->get();

    $availableVehicleCount = $vehicles->count();
    if ($availableVehicleCount > 0) {
        $vehicleIds = $vehicles->pluck('id')->map(fn ($id) => (int) $id)->all();
        $blockedVehicleIds = DB::connection('sharpfleet')
            ->table('trips')
            ->where('organisation_id', $user['organisation_id'])
            ->whereNotNull('started_at')
            ->whereNull('ended_at')
            ->whereNotNull('vehicle_id')
            ->whereIn('vehicle_id', $vehicleIds)
            ->pluck('vehicle_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if (Schema::connection('sharpfleet')->hasTable('bookings')) {
            $nowUtc = \Carbon\Carbon::now('UTC');
            $bookingVehicleIds = DB::connection('sharpfleet')
                ->table('bookings')
                ->where('organisation_id', $user['organisation_id'])
                ->whereIn('vehicle_id', $vehicleIds)
                ->where('status', 'planned')
                ->where('planned_start', '<=', $nowUtc->toDateTimeString())
                ->where('planned_end', '>=', $nowUtc->toDateTimeString())
                ->where('user_id', '!=', $user['id'])
                ->pluck('vehicle_id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $blockedVehicleIds = array_values(array_unique(array_merge($blockedVehicleIds, $bookingVehicleIds)));
        }

        $availableVehicleCount = count(array_diff($vehicleIds, $blockedVehicleIds));
    }

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
        ->join('vehicles', 'trips.vehicle_id', '=', 'vehicles.id')
        ->select('trips.vehicle_id', 'trips.end_km')
        ->where('trips.organisation_id', $user['organisation_id'])
        ->when(
            $branchAccessEnabled,
            fn ($q) => $q->whereIn('vehicles.branch_id', $accessibleBranchIds)
        )
        ->whereNotNull('ended_at')
        ->whereNotNull('end_km')
        ->orderByDesc('ended_at')
        ->get()
        // Keep the most recent ended trip per vehicle.
        ->unique('vehicle_id')
        ->keyBy('vehicle_id');

    // Check for active trip
    $activeTrip = DB::connection('sharpfleet')
        ->table('trips')
        ->leftJoin('vehicles', 'trips.vehicle_id', '=', 'vehicles.id')
        ->select('trips.*', 'vehicles.name as vehicle_name', 'vehicles.registration_number', 'vehicles.tracking_mode')
        ->where('trips.user_id', $user['id'])
        ->where('trips.organisation_id', $user['organisation_id'])
        ->when(
            $branchAccessEnabled,
            fn ($q) => $q->where(function ($sub) use ($accessibleBranchIds) {
                $sub->whereNull('trips.vehicle_id')
                    ->orWhereIn('vehicles.branch_id', $accessibleBranchIds);
            })
        )
        ->whereNotNull('trips.started_at')
        ->whereNull('trips.ended_at')
        ->first();
@endphp

<div id="offlineTripAlert" class="alert alert-info" style="display:none;"></div>

<div class="hint-text" style="margin-bottom: 12px;">
    Offline mode: Trips (start/end + readings) can be captured offline once this Driver Dashboard has been opened online at least once.
</div>

@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if ($errors && $errors->any())
    <div class="alert alert-error">
        {{ $errors->first() }}
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
                    <strong>Vehicle:</strong>
                    @if($activeTrip->vehicle_name)
                        {{ $activeTrip->vehicle_name }} ({{ $activeTrip->registration_number }})
                    @else
                        Private vehicle
                    @endif
                </div>
                <div class="info-row">
                    @php
                        $tripTz = isset($activeTrip->timezone) && trim((string) $activeTrip->timezone) !== ''
                            ? (string) $activeTrip->timezone
                            : $companyTimezone;
                    @endphp
                    <strong>Started:</strong> {{ \Carbon\Carbon::parse($activeTrip->started_at)->timezone($tripTz)->format('M j, Y g:i A') }}
                </div>
                <div class="info-row">
                    <strong>
                        @php
                            $activeTripBranchId = isset($activeTrip->vehicle_branch_id)
                                ? (int) ($activeTrip->vehicle_branch_id ?? 0)
                                : (isset($activeTrip->branch_id) ? (int) ($activeTrip->branch_id ?? 0) : 0);
                            $activeTripDistanceUnit = $settingsService->distanceUnitForBranch($activeTripBranchId > 0 ? $activeTripBranchId : null);
                        @endphp
                        {{ ($activeTrip->tracking_mode ?? 'distance') === 'hours' ? 'Starting Hours:' : ('Starting ' . strtoupper($activeTripDistanceUnit) . ':') }}
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

                @if($manualTripTimesRequired)
                    <div class="form-group">
                        <label class="form-label">End time</label>
                        <input type="datetime-local" name="ended_at" class="form-control sharpfleet-trip-datetime" required>
                        <div class="hint-text">Enter the local time for this trip.</div>
                    </div>
                @endif

                <div class="form-group">
                    <label class="form-label">
                        {{ ($activeTrip->tracking_mode ?? 'distance') === 'hours'
                            ? 'Ending hour meter (hours)'
                            : ('Ending odometer (' . $activeTripDistanceUnit . ')')
                        }}
                    </label>
                    <input type="number" name="end_km" class="form-control" inputmode="numeric" required min="{{ (int) $activeTrip->start_km }}" placeholder="e.g. 124600">
                </div>

                <button type="submit" class="btn btn-primary btn-full">End Trip</button>
            </form>
        </div>
    </div>

    @if($faultsEnabled)
        <details class="card" id="reportFaultFromTripCard">
            <summary class="card-header">
                <div class="flex-between">
                    <h3 class="card-title mb-0">Report a Vehicle Issue / Accident</h3>
                    <span class="hint-text incident-toggle-hint-closed">Tap to open</span>
                    <span class="hint-text incident-toggle-hint-open">Tap to close</span>
                </div>
            </summary>
            <div class="card-body">
                @if(!$allowFaultsDuringTrip)
                    <div class="alert alert-info">
                        Vehicle issue/accident reporting is enabled, but reporting during an active trip is disabled.
                    </div>
                @else
                    <form method="POST" action="/app/sharpfleet/faults/from-trip">
                        @csrf
                        <input type="hidden" name="trip_id" value="{{ $activeTrip->id }}">

                        <div class="form-group">
                            <label class="form-label">Type</label>
                            <select name="report_type" class="form-control" required>
                                <option value="issue">Vehicle Issue</option>
                                <option value="accident">Vehicle Accident</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Severity</label>
                            <select name="severity" class="form-control" required>
                                <option value="minor">Minor</option>
                                <option value="major">Major</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Title (optional)</label>
                            <input type="text" name="title" class="form-control" maxlength="150" placeholder="e.g. Tyre puncture / Warning light">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="4" required placeholder="Describe what happened and any immediate action taken."></textarea>
                        </div>

                        <button type="submit" class="btn btn-secondary btn-full">Submit Report</button>
                    </form>
                @endif
            </div>
        </details>
    @endif
@else
    {{-- Start Trip Card --}}
    <div class="card" id="startTripCard">
        <div class="card-header">
            <h3 class="card-title">Start a Trip</h3>
        </div>
        <div class="card-body">
            <div
                id="sf-safety-banner"
                class="alert alert-info"
                style="border: 1px solid #d84b4b; box-shadow: 0 0 0 2px rgba(216, 75, 75, 0.18), 0 0 10px rgba(216, 75, 75, 0.25); transition: opacity 250ms ease, transform 250ms ease;"
            >
                <strong>⚠️ Safety reminder</strong><br>
                Please don't use your phone while driving. Start and end your trip when it's safe.
            </div>

            <form method="POST" action="/app/sharpfleet/trips/start" id="startTripForm">
                @csrf

                {{-- Vehicle --}}
                <div class="form-group" id="vehicleBlock">
                    <label class="form-label">Vehicle</label>
                    @if($vehicles->count() > 10)
                        <input type="text" id="vehicleSearchInput" class="form-control" placeholder="Start typing to search (e.g. black toyota / camry / ABC123)">
                        <div id="vehicleSearchHint" class="hint-text">Showing {{ $vehicles->count() }} vehicles</div>
                    @endif
                    <select id="vehicleSelect" name="vehicle_id" class="form-control" required>
                        @if($availableVehicleCount === 0 && $settingsService->privateVehicleSlotsEnabled())
                            <option value="private_vehicle" data-tracking-mode="distance" data-distance-unit="{{ $settingsService->distanceUnit() }}" data-last-km="">
                                Private vehicle
                            </option>
                        @endif
                        @foreach ($vehicles as $vehicle)
                            @php
                                $vehicleBranchId = property_exists($vehicle, 'branch_id') ? (int) ($vehicle->branch_id ?? 0) : 0;
                                $vehicleDistanceUnit = $settingsService->distanceUnitForBranch($vehicleBranchId > 0 ? $vehicleBranchId : null);
                            @endphp
                            <option value="{{ $vehicle->id }}"
                                    data-tracking-mode="{{ $vehicle->tracking_mode ?? 'distance' }}"
                                    data-distance-unit="{{ $vehicleDistanceUnit }}"
                                    data-last-km="{{ $lastTrips[$vehicle->id]->end_km ?? (property_exists($vehicle, 'starting_km') ? ($vehicle->starting_km ?? '') : '') }}">
                                {{ $vehicle->name }} ({{ $vehicle->registration_number }})
                            </option>
                        @endforeach
                    </select>
                </div>

                @if($manualTripTimesRequired)
                    <div class="form-group">
                        <label class="form-label">Start time</label>
                        <input type="datetime-local" name="started_at" class="form-control sharpfleet-trip-datetime" required>
                        <div class="hint-text">Enter the local time for this trip.</div>
                    </div>
                @endif

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

                {{-- Client presence (Business trips only) --}}
                @if($settings['client_presence']['enabled'] ?? false)
                    <div id="clientPresenceBlock">
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
                    </div>
                @endif

                {{-- Customer / Client (optional; never blocks trip start) --}}
                @if(($settings['customer']['enabled'] ?? false) && (($settings['customer']['allow_select'] ?? true) || ($settings['customer']['allow_manual'] ?? true)))
                    @php
                        $partyLabel = trim((string) $settingsService->clientLabel());
                        $partyLabelLower = mb_strtolower($partyLabel !== '' ? $partyLabel : 'customer');
                    @endphp
                    <div id="customerBlock" class="form-group">
                        <label class="form-label">{{ $partyLabel !== '' ? $partyLabel : 'Customer' }} (optional)</label>

                        @if(($settings['customer']['allow_select'] ?? true) && $customers->count() > 0)
                            <select id="customerSelect" name="customer_id" class="form-control">
                                <option value="">— Select from list —</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                            <div class="hint-text">If the {{ $partyLabelLower }} isn’t in the list, type a name below.</div>
                        @endif

                        @if($settings['customer']['allow_manual'] ?? true)
                            <input id="customerNameInput" type="text" name="customer_name" class="form-control mt-2" maxlength="150" placeholder="Or enter {{ $partyLabelLower }} name (e.g. Jannie B / Job 12345)">
                        @endif
                    </div>
                @endif

                {{-- Purpose of Travel (Business trips only; optional) --}}
                @if($settingsService->purposeOfTravelEnabled())
                    <div id="purposeOfTravelBlock" class="form-group">
                        <label class="form-label">Purpose of Travel (optional)</label>
                        <input type="text" name="purpose_of_travel" class="form-control" maxlength="255" placeholder="e.g. Materials pickup at Bunnings">
                    </div>
                @endif

                {{-- Start reading (distance/hours) --}}
                <div class="form-group">
                    @php
                        $defaultDistanceUnit = $settingsService->distanceUnit();
                    @endphp
                    <label id="startReadingLabel" class="form-label">Starting odometer ({{ $defaultDistanceUnit }})</label>
                    <div id="lastKmHint" class="hint-text d-none"></div>
                    <input type="number" id="startKmInput" name="start_km" class="form-control" inputmode="numeric"
                           {{ $odometerRequired ? 'required' : '' }}
                           {{ $odometerAllowOverride ? '' : 'readonly' }}
                           placeholder="e.g. 124500">
                    @if(!$odometerRequired)
                        <div class="hint-text">If left blank, the last recorded reading will be used.</div>
                    @endif
                </div>

                {{-- Pre-Drive Safety Check --}}
                @if($safetyCheckEnabled)
                    @php
                        $safetyCount = is_array($safetyCheckItems) ? count($safetyCheckItems) : 0;
                    @endphp

                    <div class="form-group" id="preDriveSafetyCheckBlock">
                        <label class="form-label">Pre-Drive Safety Check</label>

                        @if($safetyCount > 0)
                            <div class="hint-text" style="margin-bottom: 6px;">
                                Complete the checks below before starting your trip.
                            </div>

                            <ul class="text-muted" style="margin-left: 18px;">
                                @foreach($safetyCheckItems as $item)
                                    <li>{{ $item['label'] ?? '' }}</li>
                                @endforeach
                            </ul>

                            <label class="checkbox-label">
                                <input type="checkbox" name="safety_check_confirmed" value="1" required>
                                <strong>I have completed the safety check</strong>
                            </label>
                        @else
                            <div class="alert alert-info">
                                Safety checks are enabled, but no checklist items are configured yet.
                                Please ask an admin to configure the checklist.
                            </div>
                        @endif
                    </div>
                @endif

                <button type="submit" id="startTripBtn" class="btn btn-primary btn-full">Start Trip</button>
            </form>
        </div>
    </div>

    <div id="sfHandoverModal" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(8, 18, 28, 0.55);">
        <div class="card" style="max-width:560px; margin:10vh auto;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start gap-2">
                    <div>
                        <h3 class="mb-1">Previous trip not ended</h3>
                        <p class="text-muted mb-0">
                            This vehicle already has an active trip. If you are taking it now, end the previous trip first.
                        </p>
                    </div>
                    <button type="button"
                            class="btn btn-secondary btn-sm"
                            id="sfHandoverClose"
                            aria-label="Close"
                            title="Close"
                            style="width:38px; height:38px; display:flex; align-items:center; justify-content:center; padding:0; font-size:22px; line-height:1;">
                        &times;
                    </button>
                </div>

                <div class="mt-3"></div>

                <div class="hint-text"><strong>Vehicle:</strong> <span id="sfHandoverVehicle">-</span></div>
                <div class="hint-text" style="margin-top:6px;"><strong>Previous driver:</strong> <span id="sfHandoverDriver">-</span></div>
                <div class="hint-text" style="margin-top:6px;"><strong>Trip started:</strong> <span id="sfHandoverStarted">-</span></div>
                <div class="hint-text" style="margin-top:6px;"><strong>Starting reading:</strong> <span id="sfHandoverStartKm">-</span></div>

                <div class="alert alert-info mt-3">
                    Make sure the previous trip is not still in progress before closing it.
                </div>

                <form id="sfHandoverForm" class="mt-3">
                    <input type="hidden" name="trip_id" id="sfHandoverTripId">

                    <div class="form-group">
                        <label class="form-label" id="sfHandoverReadingLabel">Current odometer (km)</label>
                        <input type="number" name="end_km" id="sfHandoverEndKm" class="form-control" inputmode="numeric" required min="0" placeholder="e.g. 124800">
                    </div>

                    <label class="checkbox-label">
                        <input type="checkbox" name="confirm_takeover" id="sfHandoverConfirm" required>
                        <strong>I confirm I am taking <span id="sfHandoverVehicleInline">this vehicle</span>.</strong>
                    </label>

                    <div id="sfHandoverError" class="alert alert-error mt-3" style="display:none;"></div>

                    <div class="d-flex gap-2 justify-content-end mt-3">
                        <button type="button" class="btn btn-secondary" id="sfHandoverCancel">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="sfHandoverSubmit">End Previous Trip</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if($faultsEnabled)
        <details class="card" id="reportFaultStandaloneCard">
            <summary class="card-header">
                <div class="flex-between">
                    <h3 class="card-title mb-0">Report a Vehicle Issue / Accident</h3>
                    <span class="hint-text incident-toggle-hint-closed">Tap to open</span>
                    <span class="hint-text incident-toggle-hint-open">Tap to close</span>
                </div>
            </summary>
            <div class="card-body">
                <form method="POST" action="/app/sharpfleet/faults/standalone">
                    @csrf

                    <div class="form-group">
                        <label class="form-label">Vehicle</label>
                        <select name="vehicle_id" class="form-control" required>
                            @foreach ($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}">{{ $vehicle->name }} ({{ $vehicle->registration_number }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Type</label>
                        <select name="report_type" class="form-control" required>
                            <option value="issue">Vehicle Issue</option>
                            <option value="accident">Vehicle Accident</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Severity</label>
                        <select name="severity" class="form-control" required>
                            <option value="minor">Minor</option>
                            <option value="major">Major</option>
                            <option value="critical">Critical</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Title (optional)</label>
                        <input type="text" name="title" class="form-control" maxlength="150" placeholder="e.g. Service due / Panel damage">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4" required placeholder="Describe the fault/incident."></textarea>
                    </div>

                    <button type="submit" class="btn btn-secondary btn-full">Submit Report</button>
                </form>
            </div>
        </details>
    @endif

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
                @if($manualTripTimesRequired)
                    <div class="form-group">
                        <label class="form-label">End time</label>
                        <input type="datetime-local" id="offlineEndedAt" class="form-control sharpfleet-trip-datetime" required>
                        <div class="hint-text">Enter the local time for this trip.</div>
                    </div>
                @endif
                <div class="form-group">
                    <label class="form-label">Ending reading</label>
                    <input type="number" id="offlineEndKm" class="form-control" inputmode="numeric" required min="0" placeholder="e.g. 124600">
                </div>
                <button type="submit" class="btn btn-primary btn-full">End Trip (Offline)</button>
            </form>
        </div>
    </div>

    @php
        $serverActiveTripPayload = $activeTrip ? [
            'trip_id' => (int) $activeTrip->id,
            'vehicle_id' => $activeTrip->vehicle_id ? (int) $activeTrip->vehicle_id : null,
            'vehicle_text' => $activeTrip->vehicle_name
                ? trim(($activeTrip->vehicle_name ?? '') . ' (' . ($activeTrip->registration_number ?? '') . ')')
                : 'Private vehicle',
            'started_at' => $activeTrip->started_at ?? null,
            'start_km' => isset($activeTrip->start_km) ? (int) $activeTrip->start_km : null,
            'trip_mode' => $activeTrip->trip_mode ?? 'business',
            'private_vehicle' => (int) ($activeTrip->is_private_vehicle ?? 0),
            'customer_id' => $activeTrip->customer_id ?? null,
            'customer_name' => $activeTrip->customer_name ?? null,
            'client_present' => $activeTrip->client_present ?? null,
            'client_address' => $activeTrip->client_address ?? null,
            'purpose_of_travel' => $activeTrip->purpose_of_travel ?? null,
        ] : null;
    @endphp

    {{-- Minimal JS for start trip form --}}
    <script>
        const COMPANY_TIMEZONE = @json($companyTimezone ?? 'UTC');
        const COMPANY_DISTANCE_UNIT = @json($settingsService->distanceUnit());
        const MANUAL_TRIP_TIMES_REQUIRED = @json((bool) $manualTripTimesRequired);

        const offlineTripAlert = document.getElementById('offlineTripAlert');
        const safetyBanner = document.getElementById('sf-safety-banner');
        if (safetyBanner) {
            setTimeout(() => {
                safetyBanner.style.opacity = '0';
                safetyBanner.style.transform = 'translateY(-8px)';
                setTimeout(() => {
                    safetyBanner.style.display = 'none';
                }, 260);
            }, 4000);
        }

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
        const OFFLINE_END_UPDATES_KEY = 'sharpfleet_offline_end_updates_v1';

        const SERVER_ACTIVE_TRIP = @json($serverActiveTripPayload);

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

        function getOfflineEndUpdates() {
            return getLocalJson(OFFLINE_END_UPDATES_KEY, []);
        }

        function setOfflineEndUpdates(updates) {
            setLocalJson(OFFLINE_END_UPDATES_KEY, updates);
        }

        function seedServerActiveTrip() {
            const existing = getOfflineActiveTrip();
            if (!SERVER_ACTIVE_TRIP) {
                if (existing && existing.source === 'server') {
                    setOfflineActiveTrip(null);
                }
                return;
            }
            if (existing) return;
            setOfflineActiveTrip({
                ...SERVER_ACTIVE_TRIP,
                source: 'server',
            });
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

            if (t.source === 'server' && navigator.onLine) {
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
            if (t.private_vehicle && v && (!t.vehicle_text || t.vehicle_text === '-' || t.vehicle_text === '—')) {
                v.textContent = 'Private vehicle';
            }
            if (s) {
                try {
                    s.textContent = new Date(t.started_at).toLocaleString(undefined, { timeZone: COMPANY_TIMEZONE });
                } catch (e) {
                    try { s.textContent = new Date(t.started_at).toLocaleString(); } catch (e2) { s.textContent = t.started_at; }
                }
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

        async function syncOfflineEndUpdatesIfPossible() {
            if (!navigator.onLine) return;
            const updates = getOfflineEndUpdates();
            if (!Array.isArray(updates) || updates.length === 0) return;

            const remaining = [];
            let syncedCount = 0;

            for (const update of updates) {
                try {
                    const formData = new FormData();
                    formData.append('trip_id', update.trip_id);
                    formData.append('end_km', update.end_km);
                    if (update.ended_at) {
                        formData.append('ended_at', update.ended_at);
                    }

                    const res = await fetch('/app/sharpfleet/trips/end', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'X-CSRF-TOKEN': getCsrfToken(),
                            'Accept': 'text/html',
                        },
                        body: formData,
                    });

                    if (!res.ok) {
                        remaining.push(update);
                    } else {
                        syncedCount += 1;
                    }
                } catch (e) {
                    remaining.push(update);
                }
            }

            setOfflineEndUpdates(remaining);
            if (syncedCount > 0) {
                showOfflineMessage(`Offline trip endings synced (${syncedCount} sent).`);
                setTimeout(() => window.location.reload(), 800);
            }
        }

        const vehicleSelect = document.getElementById('vehicleSelect');
        const vehicleSearchInput = document.getElementById('vehicleSearchInput');
        const vehicleSearchHint = document.getElementById('vehicleSearchHint');
        const startKmInput  = document.getElementById('startKmInput');
        const lastKmHint    = document.getElementById('lastKmHint');
        const startReadingLabel = document.getElementById('startReadingLabel');
        const startTripForm = document.getElementById('startTripForm');
        const startTripBtn = document.getElementById('startTripBtn');
        const handoverModal = document.getElementById('sfHandoverModal');
        const handoverForm = document.getElementById('sfHandoverForm');
        const handoverTripId = document.getElementById('sfHandoverTripId');
        const handoverEndKm = document.getElementById('sfHandoverEndKm');
        const handoverConfirm = document.getElementById('sfHandoverConfirm');
        const handoverError = document.getElementById('sfHandoverError');
        const handoverVehicle = document.getElementById('sfHandoverVehicle');
        const handoverVehicleInline = document.getElementById('sfHandoverVehicleInline');
        const handoverDriver = document.getElementById('sfHandoverDriver');
        const handoverStarted = document.getElementById('sfHandoverStarted');
        const handoverStartKm = document.getElementById('sfHandoverStartKm');
        const handoverReadingLabel = document.getElementById('sfHandoverReadingLabel');
        const handoverClose = document.getElementById('sfHandoverClose');
        const handoverCancel = document.getElementById('sfHandoverCancel');

        let lastAutoFilledReading = null;
        let handoverRequired = false;
        let handoverTrip = null;
        let handoverVehicleId = null;
        let handoverCheckToken = 0;
        let startTripSubmitting = false;

        const customerBlock = document.getElementById('customerBlock');
        const clientPresenceBlock = document.getElementById('clientPresenceBlock');
        const customerSelect = document.getElementById('customerSelect');
        const customerNameInput = document.getElementById('customerNameInput');
        const purposeOfTravelBlock = document.getElementById('purposeOfTravelBlock');

        const tripModeRadios = document.querySelectorAll('input[name="trip_mode"][type="radio"]');
        const tripModeHidden = document.querySelector('input[name="trip_mode"][type="hidden"]');

        let allVehicleOptions = Array.from(vehicleSelect.options).map(opt => ({
            value: opt.value,
            text: opt.text,
            trackingMode: opt.dataset.trackingMode || 'distance',
            distanceUnit: opt.dataset.distanceUnit || 'km',
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
            opt.dataset.distanceUnit = v.distanceUnit || 'km';
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

        function setVehicleOptionsFromServer(items, includePrivateVehicleOption) {
            const mapped = items.map(v => ({
                value: String(v.id),
                text: `${v.name} (${v.registration_number})`,
                trackingMode: v.tracking_mode || 'distance',
                distanceUnit: v.distance_unit || 'km',
                lastKm: v.last_km || ''
            }));

            if (includePrivateVehicleOption) {
                mapped.unshift({
                    value: 'private_vehicle',
                    text: 'Private vehicle',
                    trackingMode: 'distance',
                    distanceUnit: COMPANY_DISTANCE_UNIT,
                    lastKm: ''
                });
            }

            if (mapped.length === 0) {
                mapped.push({
                    value: '',
                    text: 'No vehicles available',
                    trackingMode: 'distance',
                    distanceUnit: 'km',
                    lastKm: ''
                });
            }

            allVehicleOptions = mapped;
            rebuildVehicleOptions(allVehicleOptions);
        }

        async function refreshVehicleOptionsFromServer() {
            if (!navigator.onLine) return;
            if (!vehicleSelect) return;
            if (startTripSubmitting) return;

            try {
                const res = await fetch('/app/sharpfleet/trips/available-vehicles', {
                    credentials: 'same-origin',
                    headers: { 'Accept': 'application/json' },
                    cache: 'no-store',
                });
                if (!res.ok) return;
                const data = await res.json();
                if (!data || !Array.isArray(data.vehicles)) return;
                setVehicleOptionsFromServer(data.vehicles, !!data.private_vehicle_option);
                checkActiveTripForVehicle(vehicleSelect.value);
            } catch (e) {
                // ignore
            }
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
            const distanceUnit = selected.dataset.distanceUnit || 'km';

            if (startReadingLabel) {
                if (mode === 'hours') {
                    startReadingLabel.textContent = 'Starting hour meter (hours)';
                    startKmInput.placeholder = 'e.g. 1250';
                } else {
                    startReadingLabel.textContent = `Starting odometer (${distanceUnit})`;
                    startKmInput.placeholder = 'e.g. 124500';
                }
            }

        if (selected.value === 'private_vehicle') {
            if (startReadingLabel) {
                startReadingLabel.textContent = `Starting odometer (${distanceUnit})`;
            }
            if (lastKmHint) {
                lastKmHint.classList.add('d-none');
                lastKmHint.textContent = '';
                lastKmHint.style.display = 'none';
            }
            if (startKmInput) {
                startKmInput.value = '';
            }
            lastAutoFilledReading = null;
            return;
        }

            const currentVal = (startKmInput.value || '').trim();
            const canAutofill = currentVal === '' || (lastAutoFilledReading !== null && currentVal === String(lastAutoFilledReading));

            if (lastKm) {
                if (canAutofill) {
                    startKmInput.value = lastKm;
                    lastAutoFilledReading = lastKm;
                }
                lastKmHint.textContent = (mode === 'hours')
                    ? `Last recorded hour meter: ${Number(lastKm).toLocaleString()} hours`
                    : `Last recorded odometer: ${Number(lastKm).toLocaleString()} ${distanceUnit}`;
                lastKmHint.classList.remove('d-none');
                lastKmHint.style.display = '';
            } else {
                if (canAutofill) {
                    startKmInput.value = '';
                    lastAutoFilledReading = null;
                }
                lastKmHint.classList.add('d-none');
                lastKmHint.textContent = '';
                lastKmHint.style.display = 'none';
            }
        }

        function formatTripStart(iso, timezone) {
            if (!iso) return '-';
            const tz = timezone && String(timezone).trim() !== '' ? timezone : COMPANY_TIMEZONE;
            try {
                return new Date(iso).toLocaleString('en-AU', {
                    timeZone: tz,
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: 'numeric',
                    minute: '2-digit'
                });
            } catch (e) {
                return String(iso);
            }
        }

        function openHandoverModal() {
            if (!handoverModal) return;
            handoverModal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function closeHandoverModal(resetVehicle) {
            if (!handoverModal) return;
            handoverModal.style.display = 'none';
            document.body.style.overflow = '';
            if (resetVehicle && vehicleSelect) {
                vehicleSelect.value = '';
                updateStartKm();
            }
        }

        function setHandoverRequired(required) {
            handoverRequired = required;
            if (startTripBtn) {
                startTripBtn.disabled = required;
            }
        }

        function populateHandoverModal(trip) {
            if (!trip) return;

            const selected = vehicleSelect && vehicleSelect.options[vehicleSelect.selectedIndex];
            const vehicleLabel = selected ? selected.textContent : 'this vehicle';
            const trackingMode = selected?.dataset?.trackingMode || 'distance';
            const distanceUnit = selected?.dataset?.distanceUnit || COMPANY_DISTANCE_UNIT;

            if (handoverVehicle) handoverVehicle.textContent = vehicleLabel;
            if (handoverVehicleInline) handoverVehicleInline.textContent = vehicleLabel;
            if (handoverDriver) handoverDriver.textContent = trip.driver_name || 'Unknown';
            if (handoverStarted) handoverStarted.textContent = formatTripStart(trip.started_at, trip.timezone);
            if (handoverStartKm) {
                handoverStartKm.textContent = trip.start_km !== null ? Number(trip.start_km).toLocaleString() : 'Unknown';
            }

            if (handoverReadingLabel) {
                handoverReadingLabel.textContent = trackingMode === 'hours'
                    ? 'Current hour meter (hours)'
                    : `Current odometer (${distanceUnit})`;
            }

            if (handoverTripId) handoverTripId.value = String(trip.trip_id || '');
            if (handoverEndKm) {
                const minVal = trip.start_km !== null ? Number(trip.start_km) : 0;
                handoverEndKm.min = String(minVal);
                handoverEndKm.value = '';
            }
            if (handoverConfirm) handoverConfirm.checked = false;
            if (handoverError) {
                handoverError.style.display = 'none';
                handoverError.textContent = '';
            }
        }

        async function checkActiveTripForVehicle(vehicleId) {
            if (!vehicleId || vehicleId === 'private_vehicle') {
                setHandoverRequired(false);
                handoverTrip = null;
                handoverVehicleId = null;
                return;
            }
            if (!navigator.onLine) return;
            if (!handoverModal) return;
            if (startTripSubmitting) return;

            const token = ++handoverCheckToken;
            try {
                const res = await fetch(`/app/sharpfleet/trips/active-for-vehicle?vehicle_id=${encodeURIComponent(vehicleId)}`, {
                    credentials: 'same-origin',
                    headers: { 'Accept': 'application/json' },
                    cache: 'no-store',
                });
                if (!res.ok) return;
                const data = await res.json();
                if (token !== handoverCheckToken) return;
                if (startTripSubmitting) return;

                if (!data || !data.active) {
                    handoverTrip = null;
                    handoverVehicleId = null;
                    setHandoverRequired(false);
                    if (vehicleSelect) vehicleSelect.disabled = false;
                    return;
                }

                handoverTrip = data.trip || null;
                handoverVehicleId = vehicleId;
                setHandoverRequired(true);
                populateHandoverModal(handoverTrip);
                if (vehicleSelect) vehicleSelect.disabled = true;
                openHandoverModal();
            } catch (e) {
                // ignore
            }
        }

        async function refreshSelectedVehicleLastKm() {
            if (!navigator.onLine) return;
            if (!vehicleSelect || !vehicleSelect.value) return;

            const vehicleId = vehicleSelect.value;

            try {
                const res = await fetch(`/app/sharpfleet/trips/last-reading?vehicle_id=${encodeURIComponent(vehicleId)}` , {
                    credentials: 'same-origin',
                    headers: { 'Accept': 'application/json' },
                    cache: 'no-store',
                });

                if (!res.ok) return;
                const data = await res.json();
                if (!data || String(data.vehicle_id) !== String(vehicleId)) return;

                const selected = vehicleSelect.options[vehicleSelect.selectedIndex];
                if (selected) {
                    const newLastKm = (data.last_km === null || data.last_km === undefined) ? '' : String(data.last_km);
                    selected.dataset.lastKm = newLastKm;
                    if (data.tracking_mode) {
                        selected.dataset.trackingMode = String(data.tracking_mode);
                    }
                }

                // Keep the backing list in sync so search rebuilds don't revert to old values.
                const item = allVehicleOptions.find(v => String(v.value) === String(vehicleId));
                if (item) {
                    item.lastKm = (data.last_km === null || data.last_km === undefined) ? '' : String(data.last_km);
                    if (data.tracking_mode) item.trackingMode = String(data.tracking_mode);
                }

                updateStartKm();
            } catch (e) {
                // ignore
            }
        }

        vehicleSelect.addEventListener('change', () => {
            updateStartKm();
            refreshSelectedVehicleLastKm();
            checkActiveTripForVehicle(vehicleSelect.value);
        });
        vehicleSelect.addEventListener('focus', refreshVehicleOptionsFromServer);
        vehicleSelect.addEventListener('click', refreshVehicleOptionsFromServer);
        if (vehicleSearchInput) {
            vehicleSearchInput.addEventListener('input', filterVehicles);
        }

        // When the PWA is brought back to the foreground, refresh the selected vehicle's last reading.
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') {
                refreshSelectedVehicleLastKm();
                refreshVehicleOptionsFromServer();
            }
        });
        window.addEventListener('focus', () => {
            refreshSelectedVehicleLastKm();
            refreshVehicleOptionsFromServer();
        });
        window.addEventListener('online', () => {
            refreshVehicleOptionsFromServer();
        });
        setInterval(() => {
            if (document.visibilityState !== 'visible') return;
            refreshVehicleOptionsFromServer();
        }, 30000);

        function updateBusinessOnlyBlocksVisibility() {
            const selected = document.querySelector('input[name="trip_mode"][type="radio"]:checked');
            const mode = selected ? selected.value : (tripModeHidden ? tripModeHidden.value : 'business');

            if (vehicleBlock) {
                vehicleBlock.style.display = '';
            }
            if (vehicleSelect) {
                vehicleSelect.required = true;
                vehicleSelect.disabled = false;
            }

            updateStartKm();

            const isBusinessTrip = mode !== 'private';
            if (customerBlock) customerBlock.style.display = isBusinessTrip ? '' : 'none';
            if (clientPresenceBlock) clientPresenceBlock.style.display = isBusinessTrip ? '' : 'none';
            if (purposeOfTravelBlock) purposeOfTravelBlock.style.display = isBusinessTrip ? '' : 'none';
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

        tripModeRadios.forEach(r => r.addEventListener('change', updateBusinessOnlyBlocksVisibility));

        // Initial load
        updateStartKm();
        refreshSelectedVehicleLastKm();
        refreshVehicleOptionsFromServer();
        updateBusinessOnlyBlocksVisibility();

        // Offline trip capture (start/end + readings)
        const offlineEndTripForm = document.getElementById('offlineEndTripForm');
        const offlineEndKm = document.getElementById('offlineEndKm');
        const offlineEndedAt = document.getElementById('offlineEndedAt');

        function buildOfflineStartPayload(form) {
            const fd = new FormData(form);
            const payload = Object.fromEntries(fd.entries());

            const mode = payload.trip_mode ? String(payload.trip_mode) : 'business';
            // Keep only what we need to create a trip later.
            const isPrivateVehicle = payload.vehicle_id === 'private_vehicle';
            return {
                vehicle_id: isPrivateVehicle ? null : Number(payload.vehicle_id),
                private_vehicle: isPrivateVehicle ? 1 : 0,
                trip_mode: mode,
                start_km: Number(payload.start_km),
                started_at: payload.started_at ? String(payload.started_at) : null,
                customer_id: payload.customer_id ? Number(payload.customer_id) : null,
                customer_name: payload.customer_name ? String(payload.customer_name) : null,
                client_present: payload.client_present !== undefined && payload.client_present !== '' ? payload.client_present : null,
                client_address: payload.client_address ? String(payload.client_address) : null,
                purpose_of_travel: payload.purpose_of_travel ? String(payload.purpose_of_travel) : null,
            };
        }

        if (startTripForm) {
            startTripForm.addEventListener('focusin', () => {
                refreshVehicleOptionsFromServer();
            }, { once: true });

            if (startTripBtn) {
                startTripBtn.addEventListener('click', () => {
                    startTripSubmitting = true;
                    refreshVehicleOptionsFromServer();
                });
            }

            startTripForm.addEventListener('submit', (e) => {
                if (handoverRequired) {
                    e.preventDefault();
                    openHandoverModal();
                    startTripSubmitting = false;
                    return;
                }

                if (navigator.onLine) return; // let server handle it

                e.preventDefault();

                if (getOfflineActiveTrip()) {
                    showOfflineMessage('A trip is already in progress offline. End it before starting another.');
                    startTripSubmitting = false;
                    return;
                }

                const payload = buildOfflineStartPayload(startTripForm);
                if (!payload.private_vehicle && (!payload.vehicle_id || Number.isNaN(payload.vehicle_id))) {
                    showOfflineMessage('Select a vehicle before starting.');
                    startTripSubmitting = false;
                    return;
                }
                if (Number.isNaN(payload.start_km)) {
                    showOfflineMessage('Enter a valid starting reading.');
                    startTripSubmitting = false;
                    return;
                }
                if (MANUAL_TRIP_TIMES_REQUIRED && (!payload.started_at || String(payload.started_at).trim() === '')) {
                    showOfflineMessage('Enter a start time before starting.');
                    startTripSubmitting = false;
                    return;
                }

                const selectedOpt = vehicleSelect && vehicleSelect.options[vehicleSelect.selectedIndex];
                const vehicleText = payload.private_vehicle
                    ? 'Private vehicle'
                    : (selectedOpt ? selectedOpt.textContent : '');

                setOfflineActiveTrip({
                    ...payload,
                    started_at: MANUAL_TRIP_TIMES_REQUIRED ? new Date(String(payload.started_at)).toISOString() : new Date().toISOString(),
                    vehicle_text: vehicleText,
                    private_vehicle: payload.private_vehicle ? 1 : 0,
                    source: 'offline',
                });

                showOfflineMessage('No signal: trip started offline. End it to sync later.');
                renderOfflineActiveTrip();
                startTripSubmitting = false;
            });
        }

        if (handoverForm) {
            handoverForm.addEventListener('submit', async (e) => {
                e.preventDefault();

                if (!handoverTrip || !handoverTrip.trip_id) {
                    return;
                }

                if (!handoverConfirm || !handoverConfirm.checked) {
                    if (handoverError) {
                        handoverError.textContent = 'Please confirm you are taking the vehicle.';
                        handoverError.style.display = '';
                    }
                    return;
                }

                const endKmVal = Number(handoverEndKm ? handoverEndKm.value : '');
                if (Number.isNaN(endKmVal)) {
                    if (handoverError) {
                        handoverError.textContent = 'Enter a valid current reading.';
                        handoverError.style.display = '';
                    }
                    return;
                }
                if (handoverTrip.start_km !== null && endKmVal < Number(handoverTrip.start_km)) {
                    if (handoverError) {
                        handoverError.textContent = 'Ending reading must be the same as or greater than the starting reading.';
                        handoverError.style.display = '';
                    }
                    return;
                }

                const formData = new FormData();
                formData.append('trip_id', String(handoverTrip.trip_id));
                formData.append('end_km', String(endKmVal));
                formData.append('confirm_takeover', '1');

                try {
                    const res = await fetch('/app/sharpfleet/trips/end-handover', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': getCsrfToken(),
                        },
                        body: formData,
                    });

                    if (!res.ok) {
                        let msg = 'Unable to close the trip. Please try again.';
                        try {
                            const data = await res.json();
                            if (data && data.message) msg = data.message;
                        } catch (e) {}
                        if (handoverError) {
                            handoverError.textContent = msg;
                            handoverError.style.display = '';
                        }
                        return;
                    }

                    handoverTrip = null;
                    handoverVehicleId = null;
                    setHandoverRequired(false);
                    if (vehicleSelect) vehicleSelect.disabled = false;
                    closeHandoverModal(false);
                    refreshSelectedVehicleLastKm();
                    refreshVehicleOptionsFromServer();
                } catch (e) {
                    if (handoverError) {
                        handoverError.textContent = 'Network error. Please try again.';
                        handoverError.style.display = '';
                    }
                }
            });
        }

        function closeHandoverFlow(resetVehicle) {
            setHandoverRequired(false);
            handoverTrip = null;
            handoverVehicleId = null;
            if (vehicleSelect) vehicleSelect.disabled = false;
            closeHandoverModal(resetVehicle);
        }

        if (handoverClose) {
            handoverClose.addEventListener('click', () => closeHandoverFlow(true));
        }
        if (handoverCancel) {
            handoverCancel.addEventListener('click', () => closeHandoverFlow(true));
        }
        if (handoverModal) {
            handoverModal.addEventListener('click', (e) => {
                if (e.target === handoverModal) closeHandoverFlow(true);
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

                const endedAtVal = MANUAL_TRIP_TIMES_REQUIRED && offlineEndedAt ? String(offlineEndedAt.value || '').trim() : '';
                if (MANUAL_TRIP_TIMES_REQUIRED && endedAtVal === '') {
                    showOfflineMessage('Enter an end time before ending.');
                    return;
                }

                const endedAtLocal = endedAtVal;
                let endedAtIso = '';
                if (MANUAL_TRIP_TIMES_REQUIRED) {
                    try {
                        endedAtIso = new Date(endedAtVal).toISOString();
                    } catch (e) {
                        showOfflineMessage('Enter a valid end time.');
                        return;
                    }
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

                if (active.source === 'server' && active.trip_id) {
                    const updates = getOfflineEndUpdates();
                    updates.push({
                        trip_id: active.trip_id,
                        end_km: endKmVal,
                        ended_at: MANUAL_TRIP_TIMES_REQUIRED ? endedAtLocal : new Date().toISOString(),
                    });
                    setOfflineEndUpdates(updates);
                    setOfflineActiveTrip(null);

                    showOfflineMessage('Trip ended offline. Will sync when signal returns.');
                    renderOfflineActiveTrip();
                    await syncOfflineEndUpdatesIfPossible();
                    return;
                }

                const completedTrip = {
                    vehicle_id: active.vehicle_id,
                    trip_mode: active.trip_mode,
                    start_km: active.start_km,
                    end_km: endKmVal,
                    started_at: active.started_at,
                    ended_at: MANUAL_TRIP_TIMES_REQUIRED ? endedAtIso : new Date().toISOString(),
                    customer_id: active.customer_id,
                    customer_name: active.customer_name,
                    client_present: active.client_present,
                    client_address: active.client_address,
                    purpose_of_travel: active.purpose_of_travel,
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
            syncOfflineEndUpdatesIfPossible();
        });

        window.addEventListener('offline', renderOfflineActiveTrip);

        // Initial render/sync
        seedServerActiveTrip();
        renderOfflineActiveTrip();
        syncOfflineTripsIfPossible();
        syncOfflineEndUpdatesIfPossible();
    </script>
@endif
@endsection
