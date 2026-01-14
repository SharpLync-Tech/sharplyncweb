@extends('sharpfleet.mobile.layouts.app')

@section('title', 'Driver')

@section('content')
<section class="sf-mobile-dashboard">
    @if($activeTrip)
        <h1 class="sf-mobile-title">Drive in Progress</h1>

        <div class="sf-mobile-card sf-drive-active" style="margin-bottom: 16px;">
            <div class="sf-mobile-card-title">Trip in Progress</div>
            <div class="hint-text" style="margin-top: 8px;">
                <strong>Vehicle:</strong> {{ $activeTrip->vehicle_name }} ({{ $activeTrip->registration_number }})
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

        <button
            type="button"
            class="sf-mobile-primary-btn"
            data-sheet-open="end-trip"
        >
            End Drive
        </button>
    @else
        <h1 class="sf-mobile-title">Ready to Drive</h1>

        <p class="sf-mobile-subtitle">
            No active trip
        </p>

        <button
            type="button"
            class="sf-mobile-primary-btn"
            data-sheet-open="start-trip"
        >
            Start Drive
        </button>
    @endif

    <button class="sf-mobile-secondary-btn" type="button" style="margin-top: 12px;">
        Report Vehicle Issue
    </button>
</section>

{{-- Start Trip Sheet --}}
@include('sharpfleet.mobile.sheets.start-trip')

{{-- End Trip Sheet --}}
@include('sharpfleet.mobile.sheets.end-trip')
@endsection
