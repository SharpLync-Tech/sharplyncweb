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
            Hi {{ $driverFirstName !== '' ? $driverFirstName : 'Driver' }} 👋
        </h1>

        <div class="sf-mobile-subtitle">
            SharpFleet - {{ $organisationName !== '' ? $organisationName : 'Organisation' }}
        </div>
    </div>

    {{-- ===============================
         Drive Status
    ================================ --}}
    @if($activeTrip)
        <div class="sf-mobile-card sf-drive-active" style="margin-bottom: 20px;">
            <div class="sf-mobile-card-title">Drive in Progress</div>

            <div class="hint-text" style="margin-top: 8px;">
                <strong>Vehicle:</strong>
                {{ $activeTrip->vehicle_name }} ({{ $activeTrip->registration_number }})
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
        </div>
    @else
        <div class="sf-mobile-card" style="margin-bottom: 20px;">
            <div class="sf-mobile-card-title">No Active Trip</div>
            <div class="hint-text" style="margin-top: 6px;">
                Ready when you are.
            </div>
        </div>
    @endif

    {{-- ===============================
         Trip Requirements
    ================================ --}}
    <div class="sf-mobile-card" style="margin-bottom: 20px;">
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
                <li>Client presence (when applicable)</li>
            @endif
        </ul>
    </div>

    {{-- ===============================
         Secondary Action
    ================================ --}}
    <button
        class="sf-mobile-secondary-btn"
        type="button"
        style="margin-top: 12px;"
    >
        Report Vehicle Issue
    </button>

</section>

{{-- Start Trip Sheet --}}
@include('sharpfleet.mobile.sheets.start-trip')

{{-- End Trip Sheet --}}
@include('sharpfleet.mobile.sheets.end-trip')
@endsection
