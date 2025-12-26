@extends('layouts.sharpfleet')

@section('title', 'Add Vehicle / Asset')

@section('sharpfleet-content')

<div class="max-w-800 mx-auto mt-4">

    <h1 class="mb-1">Add Vehicle / Asset</h1>
    <p class="mb-3 text-muted">
        Assets are identified by name. If an asset is road registered, its registration number
        will automatically be shown to drivers alongside the asset name.
    </p>

    @if ($errors->any())
        <div class="alert alert-error">
            <strong>Please fix the errors below.</strong>
        </div>
    @endif

    <form method="POST" action="{{ url('/app/sharpfleet/admin/vehicles') }}">
        @csrf

        <div class="card">

            {{-- Asset name --}}
            <label class="form-label">
                Asset name / identifier
            </label>
            <input type="text"
                   name="name"
                   value="{{ old('name') }}"
                   required
                   placeholder="e.g. White Camry, Tractor 3, Forklift A"
                   class="form-control">

            <div class="form-hint">
                This is how drivers and reports will identify this asset.
            </div>

            @error('name')
                <div class="text-error mb-2">{{ $message }}</div>
            @enderror

            {{-- Road registered --}}
            @php $road = old('is_road_registered', 1); @endphp

            {{-- IMPORTANT: always submit a value --}}
            <input type="hidden" name="is_road_registered" value="0">

            <label class="checkbox-label mb-2">
                <input type="checkbox"
                       id="is_road_registered"
                       name="is_road_registered"
                       value="1"
                       {{ $road == 1 ? 'checked' : '' }}>
                <strong>This asset is road registered</strong>
            </label>

            <div class="form-hint">
                Road-registered assets require a registration number and will display it to drivers.
            </div>

            {{-- Registration number --}}
            <div id="rego-wrapper">
                <label class="form-label">
                    Registration number
                </label>
                <input type="text"
                       name="registration_number"
                       value="{{ old('registration_number') }}"
                       placeholder="e.g. ABC-123"
                       class="form-control">

                @error('registration_number')
                    <div class="text-error mb-2">{{ $message }}</div>
                @enderror
            </div>

            {{-- Usage tracking --}}
            @php $tm = old('tracking_mode', 'distance'); @endphp
                <label class="form-label">
                Usage tracking
            </label>

            <select name="tracking_mode"
                    id="tracking_mode"
                    class="form-control">
                <option value="distance" {{ $tm === 'distance' ? 'selected' : '' }}>
                    Distance (kilometres)
                </option>
                <option value="hours" {{ $tm === 'hours' ? 'selected' : '' }}>
                    Hours (machine hour meter)
                </option>
                <option value="none" {{ $tm === 'none' ? 'selected' : '' }}>
                    No usage tracking
                </option>
            </select>

            <div class="form-hint">
                This controls what drivers are required to record when using this asset.
            </div>

            {{-- Starting reading (optional; km or hours depending on tracking mode) --}}
            <label id="starting_reading_label" class="form-label mt-2">Starting odometer (km) (optional)</label>
            <input type="number"
                   name="starting_km"
                   value="{{ old('starting_km') }}"
                   id="starting_km"
                   class="form-control"
                   inputmode="numeric"
                   min="0"
                   placeholder="e.g. 124500">
            <div class="form-hint">
                If set, this will be used to prefill the first trip's starting reading for this vehicle.
            </div>

            @error('starting_km')
                <div class="text-error mb-2">{{ $message }}</div>
            @enderror

            {{-- Make / Model --}}
                 <div class="form-row">
                <div>
                      <label class="form-label">Make</label>
                    <input type="text"
                           name="make"
                           value="{{ old('make') }}"
                          class="form-control">
                </div>

                <div>
                      <label class="form-label">Model</label>
                    <input type="text"
                           name="model"
                           value="{{ old('model') }}"
                          class="form-control">
                </div>
            </div>

            {{-- Vehicle type / classification --}}
                <div class="form-row">
                <div>
                    <label class="form-label">Vehicle type</label>
                    @php $vt = old('vehicle_type', 'sedan'); @endphp
                    <select name="vehicle_type"
                        class="form-control">
                        <option value="sedan" {{ $vt === 'sedan' ? 'selected' : '' }}>Sedan</option>
                        <option value="hatch" {{ $vt === 'hatch' ? 'selected' : '' }}>Hatch</option>
                        <option value="suv" {{ $vt === 'suv' ? 'selected' : '' }}>SUV</option>
                        <option value="van" {{ $vt === 'van' ? 'selected' : '' }}>Van</option>
                        <option value="bus" {{ $vt === 'bus' ? 'selected' : '' }}>Bus</option>
                        <option value="other" {{ $vt === 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <div>
                    <label class="form-label">
                        Vehicle classification (optional)
                    </label>
                    <input type="text"
                           name="vehicle_class"
                           value="{{ old('vehicle_class') }}"
                           class="form-control">
                    <div class="form-hint">
                        Examples: Light Vehicle, Heavy Vehicle, Machinery, Asset
                    </div>
                </div>
            </div>

            {{-- Accessibility --}}
            <label class="checkbox-label mb-2">
                <input type="checkbox"
                       name="wheelchair_accessible"
                       value="1"
                       {{ old('wheelchair_accessible') ? 'checked' : '' }}>
                <strong>Wheelchair accessible</strong>
            </label>

            {{-- Notes --}}
            <label class="form-label">Notes (optional)</label>
            <textarea name="notes"
                      rows="3"
                      class="form-control">{{ old('notes') }}</textarea>

        </div>

        <div class="btn-group">
            <button type="submit" class="btn btn-primary">
                Save Asset
            </button>

            <a href="{{ url('/app/sharpfleet/admin/vehicles') }}"
               class="btn btn-secondary">
                Cancel
            </a>
        </div>

    </form>

</div>

<script>
    const roadCheckbox = document.getElementById('is_road_registered');
    const regoWrapper  = document.getElementById('rego-wrapper');

    function toggleRego() {
        regoWrapper.style.display = roadCheckbox.checked ? 'block' : 'none';
    }

    toggleRego();
    roadCheckbox.addEventListener('change', toggleRego);

    // Tracking mode toggles the label between KM and hours
    const trackingMode = document.getElementById('tracking_mode');
    const startingLabel = document.getElementById('starting_reading_label');
    const startingInput = document.getElementById('starting_km');

    function updateStartingReadingLabel() {
        if (!trackingMode || !startingLabel || !startingInput) return;

        if (trackingMode.value === 'hours') {
            startingLabel.textContent = 'Starting hour meter (hours) (optional)';
            startingInput.placeholder = 'e.g. 1250';
        } else if (trackingMode.value === 'none') {
            startingLabel.textContent = 'Starting reading (optional)';
            startingInput.placeholder = '';
        } else {
            startingLabel.textContent = 'Starting odometer (km) (optional)';
            startingInput.placeholder = 'e.g. 124500';
        }
    }

    trackingMode.addEventListener('change', updateStartingReadingLabel);
    updateStartingReadingLabel();
</script>

@endsection
