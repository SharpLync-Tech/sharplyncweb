{{-- =========================================================
     SharpFleet Mobile - Add Fuel (Testing)
========================================================= --}}

<div
    id="sf-sheet-fuel-entry"
    class="sf-sheet"
    role="dialog"
    aria-modal="true"
    aria-hidden="true"
    aria-labelledby="sf-fuel-entry-title"
>
    <div class="sf-sheet-header">
        <h2 id="sf-fuel-entry-title">Add Fuel</h2>

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
        <form id="fuelEntryForm" action="#" onsubmit="return false;">
            <div class="form-group">
                <label class="form-label">Vehicle</label>
                <select name="vehicle_id" class="form-control" required>
                    @foreach ($vehicles as $vehicle)
                        @php
                            $selectedVehicleId = (int) ($activeTrip->vehicle_id ?? 0);
                            $vehicleId = (int) ($vehicle->id ?? 0);
                        @endphp
                        <option value="{{ $vehicle->id }}" {{ $vehicleId === $selectedVehicleId ? 'selected' : '' }}>
                            {{ $vehicle->name }} ({{ $vehicle->registration_number }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Odometer reading</label>
                <input type="number" name="odometer_reading" class="form-control" inputmode="numeric" placeholder="e.g. 124600">
            </div>

            <div class="form-group">
                <label class="form-label">Receipt (camera)</label>
                <input type="file" name="receipt" class="form-control" accept="image/*" capture="environment">
            </div>
        </form>
    </div>
</div>
