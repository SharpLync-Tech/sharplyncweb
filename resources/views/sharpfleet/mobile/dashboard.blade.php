@extends('sharpfleet.mobile.layouts.app')

@section('title', 'Driver')

@section('content')
<section class="sf-mobile-dashboard">

    {{-- ===============================
         Greeting / Identity
    ================================ --}}
    @php
        $driverFirstName = trim((string) ($user['first_name'] ?? ''));
        $organisationName = trim((string) ($organisationName ?? ''));
    @endphp

    <div style="margin-bottom: 16px;">
        <h1 class="sf-mobile-title">
            Hi {{ $driverFirstName !== '' ? $driverFirstName : 'Driver' }}<span style="font-size:0.65em; vertical-align:middle;"> 👋</span>
        </h1>

        <div class="sf-mobile-subtitle">
            {{ $organisationName !== '' ? $organisationName : 'Organisation' }}
        </div>
    </div>

    {{-- ===============================
         Drive Status
    ================================ --}}
    @if($activeTrip)
        <div id="activeTripCard" class="sf-mobile-card sf-drive-active" style="margin-bottom: 20px; position:relative;">
            <div class="sf-mobile-card-title">Drive in Progress</div>

            <div class="hint-text" style="margin-top: 8px;">
                <strong>Vehicle:</strong>
                @if($activeTrip->vehicle_name)
                    {{ $activeTrip->vehicle_name }} ({{ $activeTrip->registration_number }})
                @else
                    Private vehicle
                @endif
            </div>

            <div class="hint-text" style="margin-top: 6px;">
                @php
                    $tripTz = isset($activeTrip->timezone) && trim((string) $activeTrip->timezone) !== ''
                        ? (string) $activeTrip->timezone
                        : $companyTimezone;
                @endphp
                <strong>Started:</strong>
                {{ \Carbon\Carbon::parse($activeTrip->started_at)->timezone($tripTz)->format('M j, Y g:i A') }}
            </div>

            <div class="hint-text" style="margin-top: 6px;">
                @php
                    $activeTripBranchId = isset($activeTrip->vehicle_branch_id)
                        ? (int) ($activeTrip->vehicle_branch_id ?? 0)
                        : 0;
                    $activeTripDistanceUnit = $settingsService->distanceUnitForBranch(
                        $activeTripBranchId > 0 ? $activeTripBranchId : null
                    );
                @endphp
                <strong>
                    {{ ($activeTrip->tracking_mode ?? 'distance') === 'hours'
                        ? 'Starting Hours:'
                        : ('Starting ' . strtoupper($activeTripDistanceUnit) . ':')
                    }}
                </strong>
                {{ number_format($activeTrip->start_km) }}
            </div>

            <button type="button"
                    title="Refresh"
                    aria-label="Refresh"
                    onclick="window.location.reload()"
                    style="position:absolute; right:12px; bottom:12px; background:transparent; border:0; color:#2CBFAE; font-size:18px; line-height:1; cursor:pointer;">
                ↻
            </button>
        </div>

        <button
            id="endDriveBtn"
            type="button"
            class="sf-mobile-primary-btn"
            data-sheet-open="end-trip"
            style="margin-bottom: 20px;"
        >
            End Drive
        </button>
    @else
        <button
            id="noActiveTripCard"
            type="button"
            class="sf-mobile-card"
            data-sheet-open="start-trip"
            style="margin-bottom: 20px; text-align: left; width: 100%; color: #EAF7F4;"
        >
            <div class="sf-mobile-card-title" style="color: #EAF7F4;">
                <img src="/images/sharpfleet/boots_small.png"
                     alt=""
                     style="width:26px;height:26px;vertical-align:-2px;margin-right:6px;">
                No Active Trip
            </div>

            <div class="hint-text" style="margin-top: 6px;">
                Ready when you are.
            </div>
            <div class="hint-text" style="margin-top: 6px;">
                Tap to start a trip.
            </div>
        </button>

        <div id="offlineTripAlert" class="sf-mobile-card" style="display:none; margin-bottom: 20px;"></div>
        <div id="supportSentAlert" class="sf-mobile-card" style="display:none; margin-bottom: 20px;"></div>
    @endif

    <div id="offlineActiveTripCard" class="sf-mobile-card" style="margin-bottom: 20px; display:none;">
        <div class="sf-mobile-card-title">Trip in Progress (Offline)</div>
        <div class="hint-text" style="margin-top: 6px;">
            <strong>Vehicle:</strong> <span id="offlineTripVehicle">-</span>
        </div>
        <div class="hint-text" style="margin-top: 6px;">
            <strong>Started:</strong> <span id="offlineTripStarted">-</span>
        </div>
        <div class="hint-text" style="margin-top: 6px;">
            <strong>Starting reading:</strong> <span id="offlineTripStartKm">-</span>
        </div>
    </div>

    <button
        id="offlineEndDriveBtn"
        type="button"
        class="sf-mobile-primary-btn"
        data-sheet-open="end-trip"
        style="margin-bottom: 20px; display:none;"
    >
        End Drive
    </button>

    @if(($settings['vehicles']['fuel_receipts_enabled'] ?? false) === true)
        <button
            type="button"
            class="sf-mobile-card"
            data-sheet-open="fuel-entry"
            style="text-align: left; width: 100%; color: #EAF7F4; margin-bottom: 8px;"
        >
            <div class="sf-mobile-card-title" style="color: #EAF7F4;">⛽ Add Fuel</div>
            <div class="sf-mobile-card-text" style="color: #EAF7F4;">Log a fuel receipt for a vehicle.</div>
        </button>
    @endif

    {{-- ===============================
         Trip Requirements
    ================================ --}}
    @php
        $clientPresenceLabel = trim((string) ($settings['client_presence']['label'] ?? 'Client'));
        $clientPresenceLabel = $clientPresenceLabel !== '' ? $clientPresenceLabel : 'Client';
    @endphp
    <div class="sf-mobile-card" style="margin-top: 12px; margin-bottom: 20px;">
        <div class="sf-mobile-card-title">
            Before each trip, your company requires:
        </div>

        <ul class="hint-text" style="margin: 8px 0 0 16px;">
            <li>Vehicle selection</li>
            <li>Starting odometer</li>
            @if($safetyCheckEnabled)
                <li>Safety check</li>
            @endif
            @if(($settings['client_presence']['enabled'] ?? false) === true)
                <li>{{ $clientPresenceLabel }} presence (when applicable)</li>
            @endif
        </ul>
    </div>

    {{-- ===============================
         Secondary Action
    ================================ --}}
    @if($faultsEnabled)
        <button
            class="sf-mobile-secondary-btn"
            type="button"
            data-sheet-open="report-fault"
            style="margin-top: 12px;"
        >
            Report Vehicle Issue
        </button>
    @endif

</section>

{{-- Start Trip Sheet --}}
@include('sharpfleet.mobile.sheets.start-trip')

{{-- End Trip Sheet --}}
@include('sharpfleet.mobile.sheets.end-trip')

{{-- Report Fault Sheet --}}
@if($faultsEnabled)
    @include('sharpfleet.mobile.sheets.report-fault')
@endif

{{-- Fuel Entry Sheet --}}
@if(($settings['vehicles']['fuel_receipts_enabled'] ?? false) === true)
    @include('sharpfleet.mobile.sheets.fuel-entry')
@endif

@endsection
