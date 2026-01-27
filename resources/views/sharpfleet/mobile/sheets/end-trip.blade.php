<div
    id="sf-sheet-end-trip"
    class="sf-sheet"
    role="dialog"
    aria-modal="true"
    aria-hidden="true"
    aria-labelledby="sf-end-trip-title"
>
    <div class="sf-sheet-header">
        <h2 id="sf-end-trip-title">End Trip</h2>

        <button
            type="button"
            class="sf-sheet-close"
            data-sheet-close
            aria-label="Close"
        >
            <ion-icon name="close-outline"></ion-icon>
        </button>
    </div>

    <div class="sf-sheet-body">
        <form method="POST" action="/app/sharpfleet/trips/end" id="endTripForm" data-mobile-token-form>
            @csrf
            @if($activeTrip)
                <input type="hidden" name="trip_id" value="{{ $activeTrip->id }}">
            @endif

            @if($manualTripTimesRequired)
                <div class="form-group">
                    <label class="form-label">End time</label>
                    <div class="sf-datetime-wrap">
                        <input
                            type="datetime-local"
                            name="ended_at"
                            class="form-control sharpfleet-trip-datetime"
                            required
                        >
                    </div>
                    <div class="hint-text">Enter the local time for this trip.</div>
                </div>
            @endif

            @php
                $activeTripBranchId = isset($activeTrip) && isset($activeTrip->vehicle_branch_id)
                    ? (int) ($activeTrip->vehicle_branch_id ?? 0)
                    : 0;
                $activeTripDistanceUnit = $settingsService->distanceUnitForBranch(
                    $activeTripBranchId > 0 ? $activeTripBranchId : null
                );
                $minEndKm = isset($activeTrip) ? (int) ($activeTrip->start_km ?? 0) : 0;
                $trackingMode = isset($activeTrip) ? ($activeTrip->tracking_mode ?? 'distance') : 'distance';
            @endphp

            <div class="form-group">
                <label class="form-label">
                    {{ $trackingMode === 'hours'
                        ? 'Ending hour meter (hours)'
                        : ('Ending odometer (' . $activeTripDistanceUnit . ')')
                    }}
                </label>
                <input
                    type="number"
                    name="end_km"
                    class="form-control"
                    inputmode="numeric"
                    required
                    min="{{ $minEndKm }}"
                    placeholder="e.g. 124600"
                >
            </div>

            <button type="submit" class="sf-mobile-primary-btn">
                End Trip
            </button>
        </form>
    </div>
</div>
