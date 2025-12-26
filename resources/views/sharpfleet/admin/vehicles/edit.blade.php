@extends('layouts.sharpfleet')

@section('title', 'Edit Vehicle')

@section('sharpfleet-content')

<div class="max-w-800 mx-auto mt-4">

    <h1 class="mb-1">Edit Vehicle</h1>
    <p class="mb-3 text-muted">
        Registration number is locked for safety.
    </p>

    @if ($errors->any())
        <div class="alert alert-error">
            <strong>Please fix the errors below.</strong>
        </div>
    @endif

    <form method="POST" action="{{ url('/app/sharpfleet/admin/vehicles/'.$vehicle->id) }}">
        @csrf

        <div class="card">

            <label class="form-label">Vehicle name</label>
            <input type="text" name="name" value="{{ old('name', $vehicle->name) }}" required
                   class="form-control">
            @error('name') <div class="text-error mb-2">{{ $message }}</div> @enderror

            <label class="form-label">Registration number (locked)</label>
            <input type="text" value="{{ $vehicle->registration_number }}" disabled
                   class="form-control">
            <div class="form-hint">
                If the rego is wrong, archive this vehicle and add it again with the correct rego.
            </div>

            {{-- Starting reading (optional; km or hours depending on tracking mode) --}}
            <label id="starting_reading_label" class="form-label mt-2">Starting odometer (km) (optional)</label>
            <input type="number"
                   name="starting_km"
                   value="{{ old('starting_km', $vehicle->starting_km ?? '') }}"
                   id="starting_km"
                   class="form-control"
                   inputmode="numeric"
                   min="0"
                   placeholder="e.g. 124500">
            <div class="form-hint">
                If set, this will be used to prefill the first trip's starting reading for this vehicle.
            </div>
            @error('starting_km') <div class="text-error mb-2">{{ $message }}</div> @enderror

            <div class="form-row">
                <div>
                    <label class="form-label">Make</label>
                    <input type="text" name="make" value="{{ old('make', $vehicle->make) }}"
                           class="form-control">
                    @error('make') <div class="text-error mb-2">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="form-label">Model</label>
                    <input type="text" name="model" value="{{ old('model', $vehicle->model) }}"
                           class="form-control">
                    @error('model') <div class="text-error mb-2">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="form-row">
                <div>
                    <label class="form-label">Vehicle type</label>
                    @php $vt = old('vehicle_type', $vehicle->vehicle_type); @endphp
                    <select name="vehicle_type" required class="form-control">
                        <option value="sedan" {{ $vt === 'sedan' ? 'selected' : '' }}>Sedan</option>
                        <option value="hatch" {{ $vt === 'hatch' ? 'selected' : '' }}>Hatch</option>
                        <option value="suv" {{ $vt === 'suv' ? 'selected' : '' }}>SUV</option>
                        <option value="van" {{ $vt === 'van' ? 'selected' : '' }}>Van</option>
                        <option value="bus" {{ $vt === 'bus' ? 'selected' : '' }}>Bus</option>
                        <option value="other" {{ $vt === 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('vehicle_type') <div class="text-error mb-2">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="form-label">Vehicle classification (optional)</label>
                    <input type="text" name="vehicle_class" value="{{ old('vehicle_class', $vehicle->vehicle_class) }}"
                           class="form-control">
                    @error('vehicle_class') <div class="text-error mb-2">{{ $message }}</div> @enderror
                </div>
            </div>

            <label class="checkbox-label mb-2">
                <input type="checkbox" name="wheelchair_accessible" value="1"
                    {{ old('wheelchair_accessible', (int)$vehicle->wheelchair_accessible) ? 'checked' : '' }}>
                <strong>Wheelchair accessible</strong>
            </label>

            <label class="form-label">Notes (optional)</label>
            <textarea name="notes" rows="3" class="form-control">{{ old('notes', $vehicle->notes) }}</textarea>
            @error('notes') <div class="text-error mt-1">{{ $message }}</div> @enderror

        </div>

        <div class="btn-group">
            <button type="submit" class="btn btn-primary">
                Save Changes
            </button>

            <a href="{{ url('/app/sharpfleet/admin/vehicles') }}"
               class="btn btn-secondary">
                Cancel
            </a>
        </div>

    </form>

</div>

<script>
    (function () {
        const trackingMode = @json($vehicle->tracking_mode ?? 'distance');
        const startingLabel = document.getElementById('starting_reading_label');
        const startingInput = document.getElementById('starting_km');

        if (!startingLabel || !startingInput) return;

        if (trackingMode === 'hours') {
            startingLabel.textContent = 'Starting hour meter (hours) (optional)';
            startingInput.placeholder = 'e.g. 1250';
        } else if (trackingMode === 'none') {
            startingLabel.textContent = 'Starting reading (optional)';
            startingInput.placeholder = '';
        } else {
            startingLabel.textContent = 'Starting odometer (km) (optional)';
            startingInput.placeholder = 'e.g. 124500';
        }
    })();
</script>

@endsection
