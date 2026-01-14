{{-- Mobile Start Trip Sheet --}}
{{-- Reuses existing Start Trip logic --}}
{{-- No backend or route changes --}}

<div
    id="sf-sheet-start-trip"
    class="sf-sheet"
    role="dialog"
    aria-modal="true"
    aria-hidden="true"
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

        {{-- TEMP VISIBILITY CHECK --}}
        <p style="opacity:.8; margin-bottom:12px;">
            Select vehicle and start your trip.
        </p>

        {{-- START TRIP FORM --}}
        <form method="POST" action="/app/sharpfleet/trips/start" id="startTripForm">
            @csrf

            <div class="form-group">
                <label class="form-label">Vehicle</label>

                <select name="vehicle_id" class="form-control" required>
                    @foreach ($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}">
                            {{ $vehicle->name }} ({{ $vehicle->registration_number }})
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="sf-mobile-primary-btn">
                Start Trip
            </button>
        </form>

    </div>
</div>
