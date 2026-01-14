{{-- =========================================================
     SharpFleet Mobile – Start Trip Sheet
     Action Pill UX (Modal Sections)
     Backend logic unchanged
========================================================= --}}

<div
    id="sf-sheet-start-trip"
    class="sf-sheet"
    role="dialog"
    aria-modal="true"
    aria-hidden="true"
    aria-labelledby="sf-start-trip-title"
>

    {{-- ===============================
         Sheet Header
    ================================ --}}
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

    {{-- ===============================
         Sheet Body
    ================================ --}}
    <div class="sf-sheet-body">

        <form method="POST" action="/app/sharpfleet/trips/start" id="startTripForm">
            @csrf

            {{-- ===============================
                 Vehicle
            ================================ --}}
            <div class="form-group">
                <label class="form-label">Vehicle</label>

                <select
                    id="vehicleSelect"
                    name="vehicle_id"
                    class="form-control"
                    required
                >
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
                            data-last-km="{{ $lastTrips[$vehicle->id]->end_km ?? ($vehicle->starting_km ?? '') }}"
                        >
                            {{ $vehicle->name }} ({{ $vehicle->registration_number }})
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- ===============================
                 Start Time
            ================================ --}}
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

            {{-- ===============================
                 Starting Reading
            ================================ --}}
            <div class="form-group">
                @php $defaultDistanceUnit = $settingsService->distanceUnit(); @endphp

                <label id="startReadingLabel" class="form-label">
                    Starting odometer ({{ $defaultDistanceUnit }})
                </label>

                <div id="lastKmHint" class="hint-text d-none"></div>

                <input
                    type="number"
                    id="startKmInput"
                    name="start_km"
                    class="form-control"
                    inputmode="numeric"
                    {{ $odometerRequired ? 'required' : '' }}
                >
            </div>

            {{-- ===============================
                 Before You Start
            ================================ --}}
            <div class="form-group" style="margin-top:20px;">
                <div class="form-label" style="margin-bottom:8px;">
                    Before you start
                </div>

                {{-- Trip Details --}}
                <button
                    type="button"
                    class="sf-mobile-primary-btn"
                    data-sheet-open="trip-details"
                    style="
                        width:100%;
                        justify-content:space-between;
                        margin-bottom:10px;
                    "
                >
                    <span>🧾 Trip Details</span>
                    <span id="tripDetailsStatus">⭕</span>
                </button>

                {{-- Client / Customer --}}
                <button
                    type="button"
                    class="sf-mobile-primary-btn"
                    data-sheet-open="client-details"
                    style="
                        width:100%;
                        justify-content:space-between;
                        margin-bottom:10px;
                    "
                >
                    <span>👤 Client / Customer</span>
                    <span id="clientStatus">⭕</span>
                </button>

                {{-- Safety Check --}}
                @if($safetyCheckEnabled)
                    <button
                        type="button"
                        class="sf-mobile-primary-btn"
                        data-sheet-open="safety-check"
                        style="
                            width:100%;
                            justify-content:space-between;
                        "
                    >
                        <span>🛡 Safety Check</span>
                        <span id="safetyStatus">⚠️</span>
                    </button>
                @endif
            </div>

            {{-- ===============================
                 Submit
            ================================ --}}
            <button
                type="submit"
                class="sf-mobile-primary-btn"
                style="margin-top:18px;"
            >
                Start Trip
            </button>

        </form>
    </div>
</div>

{{-- =========================================================
     TRIP DETAILS SHEET
========================================================= --}}
<div id="sf-sheet-trip-details" class="sf-sheet" aria-hidden="true">
    <div class="sf-sheet-header">
        <h2>Trip Details</h2>
        <button type="button" class="sf-sheet-close" data-sheet-close>
            <ion-icon name="close-outline"></ion-icon>
        </button>
    </div>

    <div class="sf-sheet-body">
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
                <input type="hidden" name="trip_mode" value="business">
                <div class="hint-text">Business</div>
            @endif
        </div>

        @if($settingsService->purposeOfTravelEnabled())
            <div class="form-group">
                <label class="form-label">Purpose of Travel</label>
                <input
                    type="text"
                    name="purpose_of_travel"
                    class="form-control"
                >
            </div>
        @endif

        <button type="button" class="sf-mobile-primary-btn" data-sheet-close>
            Save
        </button>
    </div>
</div>

{{-- =========================================================
     CLIENT / CUSTOMER SHEET
========================================================= --}}
<div id="sf-sheet-client-details" class="sf-sheet" aria-hidden="true">
    <div class="sf-sheet-header">
        <h2>Client / Customer</h2>
        <button type="button" class="sf-sheet-close" data-sheet-close>
            <ion-icon name="close-outline"></ion-icon>
        </button>
    </div>

    <div class="sf-sheet-body">
        @if(($settings['customer']['enabled'] ?? false))
            <div class="form-group">
                <label class="form-label">Customer</label>

                @if($customers->count() > 0)
                    <select name="customer_id" class="form-control">
                        <option value="">- Select -</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                @endif

                <input
                    type="text"
                    name="customer_name"
                    class="form-control mt-2"
                    placeholder="Or enter name"
                >
            </div>
        @endif

        <button type="button" class="sf-mobile-primary-btn" data-sheet-close>
            Save
        </button>
    </div>
</div>

{{-- =========================================================
     SAFETY CHECK SHEET
========================================================= --}}
@if($safetyCheckEnabled)
<div id="sf-sheet-safety-check" class="sf-sheet" aria-hidden="true">
    <div class="sf-sheet-header">
        <h2>Safety Check</h2>
        <button type="button" class="sf-sheet-close" data-sheet-close>
            <ion-icon name="close-outline"></ion-icon>
        </button>
    </div>

    <div class="sf-sheet-body">
        <label class="checkbox-label">
            <input type="checkbox" name="safety_check_confirmed" required>
            I have completed the safety check
        </label>

        <button type="button" class="sf-mobile-primary-btn" data-sheet-close>
            Confirm
        </button>
    </div>
</div>
@endif
