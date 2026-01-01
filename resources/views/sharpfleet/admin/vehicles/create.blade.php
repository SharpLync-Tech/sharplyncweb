@extends('layouts.sharpfleet')

@section('title', 'Add Vehicle / Asset')

@section('sharpfleet-content')

@php
    $vehicleRegistrationTrackingEnabled = (bool) ($vehicleRegistrationTrackingEnabled ?? false);
    $vehicleServicingTrackingEnabled = (bool) ($vehicleServicingTrackingEnabled ?? false);
    $isSubscribed = (bool) ($isSubscribed ?? false);
@endphp

<div class="max-w-800 mx-auto mt-4">

    <h1 class="mb-1">Add Vehicle / Asset</h1>
    <p class="mb-3 text-muted">
        Assets are identified by name. If an asset is road registered, its registration number
        will automatically be shown to drivers alongside the asset name.
    </p>

    @if ($errors->any())
        <div class="alert alert-error">
            <div>
                <strong>Please fix the errors below.</strong>
                <ul class="mb-0" style="margin-top: 8px; padding-left: 18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
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

            {{-- Registration Tracking (Company Setting) --}}
            @if($vehicleRegistrationTrackingEnabled)
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

                <div class="form-row">
                    <div>
                        <label class="form-label">Registration expiry date (optional)</label>
                        <input type="date" name="registration_expiry" value="{{ old('registration_expiry') }}" class="form-control">
                        @error('registration_expiry')
                            <div class="text-error mb-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="form-label">&nbsp;</label>
                        <div class="form-hint">
                            Tip: use the vehicle Notes field for reminders.
                        </div>
                    </div>
                </div>
            @endif

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

            <hr class="my-3">
            <h3 class="mb-2">Service Status</h3>
            <p class="text-muted mb-2">
                If a vehicle is out of service, drivers cannot book it or use it for trips.
            </p>

            @php
                $isInService = old('is_in_service', 1);
                $reason = old('out_of_service_reason', '');
                $note = old('out_of_service_note', '');
            @endphp

            <input type="hidden" name="is_in_service" value="1">
            <label class="checkbox-label mb-2">
                <input type="checkbox" name="is_in_service" value="0" {{ (int) $isInService === 0 ? 'checked' : '' }}>
                <strong>Mark vehicle as out of service</strong>
            </label>
            @error('is_in_service')
                <div class="text-error mb-2">{{ $message }}</div>
            @enderror

            <div class="form-row">
                <div>
                    <label class="form-label">Reason</label>
                    <select name="out_of_service_reason" class="form-control">
                        <option value="" {{ $reason === '' ? 'selected' : '' }}>Select a reason</option>
                        <option value="Service" {{ $reason === 'Service' ? 'selected' : '' }}>Service</option>
                        <option value="Repair" {{ $reason === 'Repair' ? 'selected' : '' }}>Repair</option>
                        <option value="Accident" {{ $reason === 'Accident' ? 'selected' : '' }}>Accident</option>
                        <option value="Inspection" {{ $reason === 'Inspection' ? 'selected' : '' }}>Inspection</option>
                        <option value="Other" {{ $reason === 'Other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('out_of_service_reason')
                        <div class="text-error mb-2">{{ $message }}</div>
                    @enderror
                </div>
                <div>
                    <label class="form-label">Location / note (optional)</label>
                    <input type="text" name="out_of_service_note" value="{{ $note }}" class="form-control" maxlength="255" placeholder="e.g. This vehicle is with Da's Auto for service">
                    @error('out_of_service_note')
                        <div class="text-error mb-2">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Servicing Tracking (Company Setting) --}}
            @if($vehicleServicingTrackingEnabled)
                <hr class="my-3">
                <h3 class="mb-2">Servicing Details</h3>
                <p class="text-muted mb-3">
                    These fields are admin-managed.
                </p>

                <div class="form-row">
                    <div>
                        <label class="form-label">Next service due date (optional)</label>
                        <input type="date" name="service_due_date" value="{{ old('service_due_date') }}" class="form-control">
                        @error('service_due_date')
                            <div class="text-error mb-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label id="service_due_km_label" class="form-label">Next service due reading (km) (optional)</label>
                        <input type="number" name="service_due_km" value="{{ old('service_due_km') }}" class="form-control" inputmode="numeric" min="0" placeholder="e.g. 150000">
                        @error('service_due_km')
                            <div class="text-error mb-2">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            @endif

        </div>

        @if($isSubscribed)
            <div class="alert alert-info" style="align-items:flex-start;">
                <div>
                    <div class="fw-bold mb-1">Subscription cost confirmation</div>
                    <div class="small">
                        Adding this vehicle will increase your estimated monthly cost to
                        <strong>${{ number_format((float) ($newMonthlyPrice ?? 0), 2) }}</strong>
                        ({{ $newMonthlyPriceBreakdown ?? '' }}).
                        This increase will be added to your next monthly bill regardless of the time of the month you add the vehicle.
                        @if(($requiresContactForPricing ?? false))
                            <div class="mt-1">Over 20 vehicles: please <a href="mailto:info@sharplync.com.au">contact us</a> for pricing.</div>
                        @endif
                    </div>

                    <label class="d-flex align-items-center gap-2 mt-2" style="cursor:pointer;">
                        <input type="checkbox" name="ack_subscription_price_increase" id="ack_subscription_price_increase" value="1" {{ old('ack_subscription_price_increase') ? 'checked' : '' }}>
                        <span class="small">I acknowledge the increase in monthly cost.</span>
                    </label>

                    @error('ack_subscription_price_increase')
                        <div class="text-error mt-2">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        @endif

        <div class="btn-group">
            <button type="submit" class="btn btn-primary" id="save_asset_btn">
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
        if (!roadCheckbox || !regoWrapper) return;
        regoWrapper.style.display = roadCheckbox.checked ? 'block' : 'none';
    }

    toggleRego();
    if (roadCheckbox) {
        roadCheckbox.addEventListener('change', toggleRego);
    }

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

    // Service due reading label matches tracking mode
    const serviceDueKmLabel = document.getElementById('service_due_km_label');
    function updateServiceDueKmLabel() {
        if (!trackingMode || !serviceDueKmLabel) return;

        if (trackingMode.value === 'hours') {
            serviceDueKmLabel.textContent = 'Next service due reading (hours) (optional)';
        } else if (trackingMode.value === 'none') {
            serviceDueKmLabel.textContent = 'Next service due reading (optional)';
        } else {
            serviceDueKmLabel.textContent = 'Next service due reading (km) (optional)';
        }
    }
    if (trackingMode) {
        trackingMode.addEventListener('change', updateServiceDueKmLabel);
    }
    updateServiceDueKmLabel();

    // Subscription acknowledgement gate (server-enforced too)
    const ack = document.getElementById('ack_subscription_price_increase');
    const saveBtn = document.getElementById('save_asset_btn');
    if (ack && saveBtn) {
        function updateSaveEnabled() {
            saveBtn.disabled = !ack.checked;
        }
        ack.addEventListener('change', updateSaveEnabled);
        updateSaveEnabled();
    }
</script>

@endsection
