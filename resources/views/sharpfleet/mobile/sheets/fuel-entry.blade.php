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
                <label class="form-label">Litres</label>
                <input type="number" step="0.01" name="litres" class="form-control" inputmode="decimal" placeholder="e.g. 45.2">
            </div>

            <div class="form-group">
                <label class="form-label">Total cost</label>
                <input type="number" step="0.01" name="total_cost" class="form-control" inputmode="decimal" placeholder="e.g. 98.50">
            </div>

            <div class="form-group">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="3" placeholder="Optional notes"></textarea>
            </div>
        </form>
    </div>
</div>
