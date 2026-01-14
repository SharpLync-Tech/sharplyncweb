{{-- Mobile Start Trip Sheet --}}
{{-- Reuses the existing desktop Start Trip form logic --}}
{{-- No backend or route changes --}}

<div
    id="sf-start-trip-sheet"
    class="sf-sheet sf-sheet-hidden"
    role="dialog"
    aria-modal="true"
    aria-labelledby="sf-start-trip-title"
>

    {{-- Sheet Header --}}
    <div class="sf-sheet-header">
        <h2 id="sf-start-trip-title">Start a Trip</h2>

        <button
            type="button"
            class="sf-sheet-close"
            data-sheet-close
            aria-label="Close"
        >
            <ion-icon name="close-outline"></ion-icon>
        </button>
    </div>

    {{-- Sheet Body --}}
    <div class="sf-sheet-body">

        <form method="POST" action="/app/sharpfleet/trips/start" id="startTripForm">
            @csrf

            {{-- Vehicle --}}
            <div class="form-group">
                <label class="form-label">Vehicle</label>

                @if($vehicles->count() > 10)
                    <input
                        type="text"
                        id="vehicleSearchInput"
                        class="form-control"
                        placeholder="Start typing to search"
                    >
                    <div id="vehicleSearchHint" class="hint-text">
                        Showing {{ $vehicles->count() }} vehicles
                    </div>
                @endif

                <select id="vehicleSelect" name="vehicle_id" class="form-control" required>
                    @foreach ($vehicles as $vehicle)
                        @php
                            $vehicleBranchId = property_exists($vehicle, 'branch_id')
                                ? (int) ($vehicle->branch_id ?? 0)
                                : 0;

                            $vehicleDistanceUnit = $settingsService->distanceUnitForBranch(
                                $vehicleBranchId > 0 ? $vehicleBranchId : null
                            );
                        @endphp

                        <option
                            value="{{ $vehicle->id }}"
                            data-tracking-mode="{{ $vehicle->tracking_mode ?? 'distance' }}"
                            data-distance-unit="{{ $vehicleDistanceUnit }}"
                            data-last-km="{{ $lastTrips[$vehicle->id]->end_km ?? (property_exists($vehicle, 'starting_km') ? ($vehicle->starting_km ?? '') : '') }}"
                        >
                            {{ $vehicle->name }} ({{ $vehicle->registration_number }})
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Manual start time --}}
            @if($manualTripTimesRequired)
                <div class="form-group">
                    <label class="form-label">Start time</label>
                    <input
                        type="datetime-local"
                        name="started_at"
                        class="form-control sharpfleet-trip-datetime"
                        required
                    >
                    <div class="hint-text">
                        Enter the local time for this trip.
                    </div>
                </div>
            @endif

            {{-- Trip Type --}}
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

            {{-- Client Presence --}}
            @if($settings['client_presence']['enabled'] ?? false)
                <div id="clientPresenceBlock">
                    <div class="form-group">
                        <label class="form-label">
                            {{ $settings['client_presence']['label'] ?? 'Client' }} Present?
                            {{ $settings['client_presence']['required'] ? '(Required)' : '' }}
                        </label>

                        <select
                            name="client_present"
                            class="form-control"
                            {{ $settings['client_presence']['required'] ? 'required' : '' }}
                        >
                            <option value="">— Select —</option>
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </div>

                    @if($settings['client_presence']['enable_addresses'] ?? false)
                        <div class="form-group">
                            <label class="form-label">Client Address</label>
                            <input
                                type="text"
                                name="client_address"
                                class="form-control"
                                placeholder="e.g. 123 Main St"
                            >
                        </div>
                    @endif
                </div>
            @endif

            {{-- Start reading --}}
            <div class="form-group">
                <label class="form-label">Start reading</label>

                <input
                    type="number"
                    id="startKmInput"
                    name="start_km"
                    class="form-control"
                    inputmode="numeric"
                    {{ $odometerRequired ? 'required' : '' }}
                    {{ $odometerAllowOverride ? '' : 'readonly' }}
                    placeholder="Enter reading"
                >

                <div class="hint-text">
                    Override if the display doesn’t match
                </div>
            </div>

            {{-- Safety Check --}}
            @if($safetyCheckEnabled)
                <div class="form-group" id="preDriveSafetyCheckBlock">
                    <label class="form-label">Pre-Drive Safety Check</label>

                    @if(is_array($safetyCheckItems) && count($safetyCheckItems))
                        <ul>
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
                            Safety checks are enabled but not configured.
                        </div>
                    @endif
                </div>
            @endif

            <button type="submit" class="sf-mobile-primary-btn">
                Start Trip
            </button>

        </form>

    </div>
</div>
