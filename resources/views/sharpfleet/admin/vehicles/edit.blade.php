@extends('layouts.sharpfleet')

@section('title', 'Edit Vehicle')

@section('sharpfleet-content')

@php
    $vehicleRegistrationTrackingEnabled = (bool) ($vehicleRegistrationTrackingEnabled ?? false);
    $vehicleServicingTrackingEnabled = (bool) ($vehicleServicingTrackingEnabled ?? false);
    $drivers = $drivers ?? collect();
    $branchesEnabled = (bool) ($branchesEnabled ?? false);
    $branches = $branches ?? collect();
    $defaultBranchId = $defaultBranchId ?? null;
@endphp

<div class="container mt-4">
    <form method="POST" action="{{ url('/app/sharpfleet/admin/vehicles/'.$vehicle->id) }}">
        @csrf

        <div class="flex-between" style="margin-bottom: 10px;">
            <div>
                <h1 class="mb-1 text-white">Edit Vehicle</h1>
                <p class="text-white-50 mb-0">Registration number is locked to prevent accidental changes.</p>
            </div>
            <div class="btn-group">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ url('/app/sharpfleet/admin/vehicles') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-error">
                <strong>Please fix the errors below.</strong>
            </div>
        @endif

        <div class="grid gap-4 mb-3">
            {{-- Row 1: Vehicle info | Vehicle details --}}
            <div class="grid grid-2 gap-4">
                <div class="card">
                    <div class="card-body">
                        <h3 class="section-title">Vehicle info</h3>

                        <div class="form-group">
                            <label class="form-label">Vehicle name</label>
                            <input type="text" name="name" value="{{ old('name', $vehicle->name) }}" required class="form-control">
                            @error('name') <div class="text-error mb-2">{{ $message }}</div> @enderror
                        </div>

                        @if($branchesEnabled)
                            <div class="form-group">
                                <label class="form-label">Branch</label>
                                <select name="branch_id" class="form-control">
                                    @foreach($branches as $b)
                                        <option value="{{ (int) $b->id }}" {{ (int) old('branch_id', $vehicle->branch_id ?? $defaultBranchId) === (int) $b->id ? 'selected' : '' }}>
                                            {{ (string) ($b->name ?? '') }} ({{ (string) ($b->timezone ?? '') }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('branch_id') <div class="text-error mb-2">{{ $message }}</div> @enderror
                            </div>
                        @endif

                        <div class="form-group">
                            <label class="form-label">Registration number (locked)</label>
                            <input type="text" value="{{ $vehicle->registration_number }}" disabled class="form-control">
                            <div class="form-hint">
                                If the rego is incorrect, archive this vehicle and add it again.
                            </div>
                        </div>

                        <div class="form-group">
                            <label id="starting_reading_label" class="form-label">Starting odometer (km) (optional)</label>
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
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h3 class="section-title">Vehicle details</h3>

                        <div class="form-row">
                            <div>
                                <label class="form-label">Make</label>
                                <input type="text" name="make" value="{{ old('make', $vehicle->make) }}" class="form-control">
                                @error('make') <div class="text-error mb-2">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="form-label">Model</label>
                                <input type="text" name="model" value="{{ old('model', $vehicle->model) }}" class="form-control">
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
                                <label class="form-label">Vehicle classification</label>
                                <input type="text" name="vehicle_class" value="{{ old('vehicle_class', $vehicle->vehicle_class) }}" class="form-control" placeholder="Optional">
                                @error('vehicle_class') <div class="text-error mb-2">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="checkbox-label mb-2">
                                <input type="checkbox" name="wheelchair_accessible" value="1" {{ old('wheelchair_accessible', (int)$vehicle->wheelchair_accessible) ? 'checked' : '' }}>
                                <strong>Wheelchair accessible</strong>
                            </label>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" rows="3" class="form-control" placeholder="Optional">{{ old('notes', $vehicle->notes) }}</textarea>
                            @error('notes') <div class="text-error mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Row 2: Registration | Servicing --}}
            <div class="grid grid-2 gap-4">
                @if($vehicleRegistrationTrackingEnabled)
                    <div class="card">
                        <div class="card-body">
                            <h3 class="section-title">Registration</h3>
                            <div class="form-row">
                                <div>
                                    <label class="form-label">Registration expiry date (optional)</label>
                                    <input type="date"
                                           name="registration_expiry"
                                           value="{{ old('registration_expiry', $vehicle->registration_expiry ?? '') }}"
                                           class="form-control">
                                    @error('registration_expiry') <div class="text-error mb-2">{{ $message }}</div> @enderror
                                </div>

                                <div>
                                    <label class="form-label">&nbsp;</label>
                                    <div class="form-hint">
                                        Tip: use Notes for reminder details.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if($vehicleServicingTrackingEnabled)
                    <div class="card">
                        <div class="card-body">
                            <h3 class="section-title">Servicing</h3>
                            <div class="form-row">
                                <div>
                                    <label class="form-label">Next service due date (optional)</label>
                                    <input type="date"
                                           name="service_due_date"
                                           value="{{ old('service_due_date', $vehicle->service_due_date ?? '') }}"
                                           class="form-control">
                                    @error('service_due_date') <div class="text-error mb-2">{{ $message }}</div> @enderror
                                </div>

                                <div>
                                    <label id="service_due_km_label" class="form-label">Next service due reading (km) (optional)</label>
                                    <input type="number"
                                           name="service_due_km"
                                           value="{{ old('service_due_km', $vehicle->service_due_km ?? '') }}"
                                           class="form-control"
                                           inputmode="numeric"
                                           min="0">
                                    @error('service_due_km') <div class="text-error mb-2">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Row 3: Service status | Permanent allocation --}}
            <div class="grid grid-2 gap-4">
                <div class="card">
                    <div class="card-body">
                        <h3 class="section-title">Service status</h3>
                        <p class="text-muted mb-3">
                            If a vehicle is out of service, drivers cannot book it or use it for trips.
                        </p>

                        @php
                            $isInService = old('is_in_service', isset($vehicle->is_in_service) ? (int) $vehicle->is_in_service : 1);
                            $reason = old('out_of_service_reason', $vehicle->out_of_service_reason ?? '');
                            $note = old('out_of_service_note', $vehicle->out_of_service_note ?? '');
                        @endphp

                        <input type="hidden" name="is_in_service" value="1">
                        <label class="checkbox-label mb-2">
                            <input type="checkbox" name="is_in_service" value="0" {{ (int) $isInService === 0 ? 'checked' : '' }}>
                            <strong>Mark vehicle as out of service</strong>
                        </label>
                        @error('is_in_service') <div class="text-error mb-2">{{ $message }}</div> @enderror

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
                                @error('out_of_service_reason') <div class="text-error mb-2">{{ $message }}</div> @enderror
                            </div>
                            <div>
                                <label class="form-label">Location / note (optional)</label>
                                <input type="text" name="out_of_service_note" value="{{ $note }}" class="form-control" maxlength="255" placeholder="e.g. This vehicle is with Da's Auto for service">
                                @error('out_of_service_note') <div class="text-error mb-2">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h3 class="section-title">Permanent allocation</h3>
                        <p class="text-muted mb-3">
                            Permanently assigned vehicles cannot be booked and can only be used by the assigned driver.
                        </p>

                        @php
                            $currentAssignmentType = property_exists($vehicle, 'assignment_type') ? strtolower((string) ($vehicle->assignment_type ?? 'none')) : 'none';
                            $currentAssignedDriverId = property_exists($vehicle, 'assigned_driver_id') ? ($vehicle->assigned_driver_id ?? null) : null;

                            $permanentEnabled = (int) old('permanent_assignment', $currentAssignmentType === 'permanent' ? 1 : 0) === 1;
                            $selectedDriverId = old('assigned_driver_id', $currentAssignedDriverId ?? '');
                        @endphp

                        <input type="hidden" name="permanent_assignment" value="0">
                        <label class="checkbox-label mb-2">
                            <input type="checkbox" name="permanent_assignment" value="1" {{ $permanentEnabled ? 'checked' : '' }}>
                            <strong>Enable permanent allocation</strong>
                        </label>
                        @error('permanent_assignment') <div class="text-error mb-2">{{ $message }}</div> @enderror

                        <div class="form-group">
                            <label class="form-label">Assigned driver</label>
                            <select name="assigned_driver_id" class="form-control" {{ $permanentEnabled ? '' : 'disabled' }}>
                                <option value="">Select a driver</option>
                                @foreach($drivers as $d)
                                    @php
                                        $driverName = trim((string) ($d->first_name ?? '') . ' ' . (string) ($d->last_name ?? ''));
                                        if ($driverName === '') {
                                            $driverName = 'User #' . (int) ($d->id ?? 0);
                                        }
                                    @endphp
                                    <option value="{{ (int) $d->id }}" {{ (string) $selectedDriverId === (string) $d->id ? 'selected' : '' }}>
                                        {{ $driverName }}
                                    </option>
                                @endforeach
                            </select>
                            @if(!$permanentEnabled)
                                <div class="form-hint">Enable permanent allocation to choose a driver.</div>
                            @endif
                            @error('assigned_driver_id') <div class="text-error mb-2">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="btn-group mb-4">
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ url('/app/sharpfleet/admin/vehicles') }}" class="btn btn-secondary">Cancel</a>
        </div>

    </form>
</div>

<script>
    (function () {
        const trackingMode = @json($vehicle->tracking_mode ?? 'distance');
        const startingLabel = document.getElementById('starting_reading_label');
        const startingInput = document.getElementById('starting_km');
        const serviceDueKmLabel = document.getElementById('service_due_km_label');

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

        if (serviceDueKmLabel) {
            if (trackingMode === 'hours') {
                serviceDueKmLabel.textContent = 'Next service due reading (hours) (optional)';
            } else if (trackingMode === 'none') {
                serviceDueKmLabel.textContent = 'Next service due reading (optional)';
            } else {
                serviceDueKmLabel.textContent = 'Next service due reading (km) (optional)';
            }
        }
    })();

    (function () {
        const toggle = document.querySelector('input[name="permanent_assignment"][type="checkbox"]');
        const select = document.querySelector('select[name="assigned_driver_id"]');
        if (!toggle || !select) return;

        function sync() {
            select.disabled = !toggle.checked;
            if (!toggle.checked) {
                select.value = '';
            }
        }

        toggle.addEventListener('change', sync);
        sync();
    })();
</script>

@endsection
