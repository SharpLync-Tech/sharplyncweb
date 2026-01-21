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
    $companyDistanceUnit = (string) ($companyDistanceUnit ?? 'km');
@endphp

<div class="container mt-4 sf-vehicle-edit">
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

        <div class="card sf-vehicle-tabs mb-3 is-js">
            <div class="sf-tabs" role="tablist" aria-label="Vehicle form sections">
                <button type="button" class="sf-tab is-active" id="sf-vehicle-tab-basics-button" data-sf-tab="basics" role="tab" aria-controls="sf-vehicle-tab-basics" aria-selected="true">
                    Basics
                </button>
                <button type="button" class="sf-tab" id="sf-vehicle-tab-details-button" data-sf-tab="details" role="tab" aria-controls="sf-vehicle-tab-details" aria-selected="false">
                    Vehicle Details
                </button>
                @if($vehicleRegistrationTrackingEnabled)
                    <button type="button" class="sf-tab" id="sf-vehicle-tab-registration-button" data-sf-tab="registration" role="tab" aria-controls="sf-vehicle-tab-registration" aria-selected="false">
                        Registration
                    </button>
                @endif
                @if($vehicleServicingTrackingEnabled)
                    <button type="button" class="sf-tab" id="sf-vehicle-tab-servicing-button" data-sf-tab="servicing" role="tab" aria-controls="sf-vehicle-tab-servicing" aria-selected="false">
                        Servicing
                    </button>
                @endif
                <button type="button" class="sf-tab" id="sf-vehicle-tab-status-button" data-sf-tab="status" role="tab" aria-controls="sf-vehicle-tab-status" aria-selected="false">
                    Status
                </button>
                <button type="button" class="sf-tab" id="sf-vehicle-tab-allocation-button" data-sf-tab="allocation" role="tab" aria-controls="sf-vehicle-tab-allocation" aria-selected="false">
                    Allocation
                </button>
                <button type="button" class="sf-tab" id="sf-vehicle-tab-faults-button" data-sf-tab="faults" role="tab" aria-controls="sf-vehicle-tab-faults" aria-selected="false">
                    Faults
                </button>
            </div>

            <div class="sf-tab-panels">
                <section class="sf-tab-panel is-active" id="sf-vehicle-tab-basics" data-sf-panel="basics" role="tabpanel" aria-labelledby="sf-vehicle-tab-basics-button">
                    <div class="grid gap-4">
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

                        <div class="form-group">
                            <label class="form-label">First registration year</label>
                            <input type="number"
                                   name="first_registration_year"
                                   value="{{ old('first_registration_year', $vehicle->first_registration_year ?? '') }}"
                                   class="form-control"
                                   min="1900"
                                   max="2100"
                                   placeholder="e.g. 2018">
                            @error('first_registration_year') <div class="text-error mb-2">{{ $message }}</div> @enderror
                        </div>

                        @if($branchesEnabled)
                            <div class="form-group">
                                <label class="form-label">Branch</label>
                                <select name="branch_id" id="branch_id" class="form-control">
                                    @foreach($branches as $b)
                                        <option value="{{ (int) $b->id }}"
                                                data-timezone="{{ (string) ($b->timezone ?? '') }}"
                                                {{ (int) old('branch_id', $vehicle->branch_id ?? $defaultBranchId) === (int) $b->id ? 'selected' : '' }}>
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
                            <label id="starting_reading_label" class="form-label">Starting odometer ({{ $companyDistanceUnit }}) (optional)</label>
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
            </div>
        </div>
    </section>

    <section class="sf-tab-panel" id="sf-vehicle-tab-details" data-sf-panel="details" role="tabpanel" aria-labelledby="sf-vehicle-tab-details-button">
        <div class="grid gap-4">
            <div class="grid grid-2 gap-4">
                <div class="card">
                    <div class="card-body">
                        <h3 class="section-title">Vehicle details</h3>
                        <div class="form-hint mb-2">
                            Tip: Start typing a make and model, then pick a variant to refine suggestions.
                        </div>
                        <div class="form-hint mb-3">
                            AI suggests details only; you can edit any field before saving.
                        </div>

                        <div class="form-row">
                            <div>
                                <label class="form-label">Make</label>
                                <div class="ai-input-wrap">
                                    <input id="aiMakeInput"
                                           type="text"
                                           name="make"
                                           value="{{ old('make', $vehicle->make) }}"
                                           class="form-control">
                                    <button type="button" class="ai-clear-btn" data-clear="make" aria-label="Clear make">x</button>
                                </div>
                                <div id="aiMakeStatus" class="form-hint"></div>
                                <div id="aiMakeList" class="ai-list"></div>
                                @error('make') <div class="text-error mb-2">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="form-label">Model</label>
                                <div class="ai-input-wrap">
                                    <input id="aiModelInput"
                                           type="text"
                                           name="model"
                                           value="{{ old('model', $vehicle->model) }}"
                                           class="form-control">
                                    <button type="button" class="ai-clear-btn" data-clear="model" aria-label="Clear model">x</button>
                                </div>
                                <div id="aiModelStatus" class="form-hint"></div>
                                <div id="aiModelList" class="ai-list"></div>
                                @error('model') <div class="text-error mb-2">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Variant</label>
                            <div class="ai-input-wrap">
                                <input id="aiTrimInput"
                                       type="text"
                                       name="variant"
                                       value="{{ old('variant', $vehicle->variant ?? '') }}"
                                       class="form-control">
                                <button type="button" class="ai-clear-btn" data-clear="trim" aria-label="Clear variant">x</button>
                            </div>
                            <div id="aiTrimStatus" class="form-hint"></div>
                            <div id="aiTrimList" class="ai-list"></div>
                        </div>

                        <div class="form-row">
                            <div>
                                <label class="form-label">Vehicle type</label>
                                @php $vt = old('vehicle_type', $vehicle->vehicle_type); @endphp
                                <select name="vehicle_type" id="vehicle_type" required class="form-control">
                                    <option value="sedan" {{ $vt === 'sedan' ? 'selected' : '' }}>Sedan</option>
                                    <option value="ute" {{ $vt === 'ute' ? 'selected' : '' }}>Pickup / Light Truck</option>
                                    <option value="hatch" {{ $vt === 'hatch' ? 'selected' : '' }}>Hatch</option>
                                    <option value="suv" {{ $vt === 'suv' ? 'selected' : '' }}>SUV</option>
                                    <option value="van" {{ $vt === 'van' ? 'selected' : '' }}>Van</option>
                                    <option value="bus" {{ $vt === 'bus' ? 'selected' : '' }}>Bus</option>
                                    <option value="ex" {{ $vt === 'ex' ? 'selected' : '' }}>Excavator</option>
                                    <option value="dozer" {{ $vt === 'dozer' ? 'selected' : '' }}>Bulldozer</option>
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
                        <div id="vehicleTypeStatus" class="form-hint">Auto-suggested from make, model, and variant.</div>

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
                    </div>
                </section>

                @if($vehicleRegistrationTrackingEnabled)
                    <section class="sf-tab-panel" id="sf-vehicle-tab-registration" data-sf-panel="registration" role="tabpanel" aria-labelledby="sf-vehicle-tab-registration-button">
                        <div class="grid gap-4">
                            <div class="grid grid-2 gap-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h3 class="section-title">Registration details</h3>
                                        <div class="form-row">
                                            <div>
                                                <label class="form-label">Registration expiry date (optional)</label>
                                                <input type="text"
                                                       class="form-control sf-date"
                                                       placeholder="dd / mm / yyyy"
                                                       name="registration_expiry"
                                                       value="{{ old('registration_expiry', $vehicle->registration_expiry ?? '') }}"
                                                       inputmode="numeric">
                                                @error('registration_expiry') <div class="text-error mb-2">{{ $message }}</div> @enderror
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Reminder email (optional)</label>
                                            <input type="email"
                                                   name="registration_reminder_email"
                                                   value="{{ old('registration_reminder_email', $vehicle->registration_reminder_email ?? '') }}"
                                                   class="form-control"
                                                   placeholder="e.g. ops@company.com">
                                        </div>
                                        <div class="form-hint">
                                            Reminder window is managed in Company Settings.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                @endif

                @if($vehicleServicingTrackingEnabled)
                    <section class="sf-tab-panel" id="sf-vehicle-tab-servicing" data-sf-panel="servicing" role="tabpanel" aria-labelledby="sf-vehicle-tab-servicing-button">
                        <div class="grid gap-4">
                            <div class="grid grid-2 gap-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h3 class="section-title">Service details</h3>
                                        <div class="form-row">
                                            <div>
                                                <label class="form-label">Last service date (optional)</label>
                                                <input type="text"
                                                       class="form-control sf-date"
                                                       placeholder="dd / mm / yyyy"
                                                       name="last_service_date"
                                                       value="{{ old('last_service_date', $vehicle->last_service_date ?? '') }}"
                                                       inputmode="numeric">
                                                @error('last_service_date') <div class="text-error mb-2">{{ $message }}</div> @enderror
                                            </div>

                                            <div>
                                                <label id="last_service_km_label" class="form-label">Last service reading ({{ $companyDistanceUnit }}) (optional)</label>
                                                <input type="number"
                                                       name="last_service_km"
                                                       value="{{ old('last_service_km', $vehicle->last_service_km ?? '') }}"
                                                       class="form-control"
                                                       inputmode="numeric"
                                                       min="0"
                                                       placeholder="e.g. 120000">
                                                @error('last_service_km') <div class="text-error mb-2">{{ $message }}</div> @enderror
                                            </div>
                                        </div>

                                        <div class="form-row">
                                            <div>
                                                <label class="form-label">Next service due date (optional)</label>
                                                <input type="text"
                                                       class="form-control sf-date"
                                                       placeholder="dd / mm / yyyy"
                                                       name="service_due_date"
                                                       value="{{ old('service_due_date', $vehicle->service_due_date ?? '') }}"
                                                       inputmode="numeric">
                                                @error('service_due_date') <div class="text-error mb-2">{{ $message }}</div> @enderror
                                            </div>

                                            <div>
                                                <label id="service_due_km_label" class="form-label">Next service due reading ({{ $companyDistanceUnit }}) (optional)</label>
                                                <input type="number"
                                                       name="service_due_km"
                                                       value="{{ old('service_due_km', $vehicle->service_due_km ?? '') }}"
                                                       class="form-control"
                                                       inputmode="numeric"
                                                       min="0">
                                                @error('service_due_km') <div class="text-error mb-2">{{ $message }}</div> @enderror
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="form-label">Reminder email (optional)</label>
                                            <input type="email"
                                                   name="service_reminder_email"
                                                   value="{{ old('service_reminder_email', $vehicle->service_reminder_email ?? '') }}"
                                                   class="form-control"
                                                   placeholder="e.g. ops@company.com">
                                        </div>
                                        <div class="form-hint">
                                            Reminder window is managed in Company Settings.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                @endif

                <section class="sf-tab-panel" id="sf-vehicle-tab-status" data-sf-panel="status" role="tabpanel" aria-labelledby="sf-vehicle-tab-status-button">
                    <div class="grid gap-4">
                        {{-- Row 3: Service status --}}
                        <div class="grid grid-2 gap-4">
                <div class="card">
                    <div class="card-body">
                        <h3 class="section-title">Service status</h3>
                        <p class="text-muted mb-3">
                            Out-of-service vehicles cannot be booked or used for trips.
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
            </div>
        </div>
    </section>

    <section class="sf-tab-panel" id="sf-vehicle-tab-allocation" data-sf-panel="allocation" role="tabpanel" aria-labelledby="sf-vehicle-tab-allocation-button">
        <div class="grid gap-4">
            <div class="grid grid-2 gap-4">
                <div class="card">
                    <div class="card-body">
                        <h3 class="section-title">Permanent allocation</h3>
                        <p class="text-muted mb-3">
                            Permanently assigned vehicles can only be used by the assigned driver.
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
                </section>

                <section class="sf-tab-panel" id="sf-vehicle-tab-faults" data-sf-panel="faults" role="tabpanel" aria-labelledby="sf-vehicle-tab-faults-button">
                    @php
                        $vehicleFaults = $vehicleFaults ?? [];
                    @endphp
                    <div class="grid gap-4">
                        <div class="card">
                            <div class="card-body">
                                <h3 class="section-title">Recent faults</h3>
                                @if(count($vehicleFaults))
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Fault</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($vehicleFaults as $fault)
                                                <tr>
                                                    <td>{{ $fault->reported_at ?? $fault->created_at ?? '' }}</td>
                                                    <td>{{ $fault->title ?? $fault->summary ?? $fault->fault_type ?? 'Fault' }}</td>
                                                    <td>{{ $fault->status ?? 'Unknown' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <p class="text-muted mb-0">No faults recorded for this vehicle yet.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <div class="btn-group mb-4">
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ url('/app/sharpfleet/admin/vehicles') }}" class="btn btn-secondary">Cancel</a>
        </div>

    </form>
</div>

<style>
.sf-vehicle-tabs {
    padding: 0;
    overflow: visible;
    border: 1px solid rgba(10, 42, 77, 0.08);
    border-top: 1px solid rgba(10, 42, 77, 0.08);
    box-shadow: 0 18px 30px rgba(10, 42, 77, 0.12);
}

.sf-tabs {
    display: flex;
    flex-wrap: nowrap;
    gap: 0;
    padding: 16px 18px 0;
    border-bottom: none;
    background: transparent;
    align-items: flex-end;
    position: relative;
}

.sf-tab {
    --sf-tab-radius: 14px;
    --sf-tab-border: #d9e2ec;
    appearance: none;
    border: 1px solid var(--sf-tab-border);
    border-bottom: 1px solid var(--sf-tab-border);
    background: #ffffff;
    color: #52657a;
    font-weight: 600;
    letter-spacing: 0.01em;
    text-transform: none;
    font-size: 13px;
    padding: 9px 18px 11px;
    border-top-left-radius: var(--sf-tab-radius);
    border-top-right-radius: var(--sf-tab-radius);
    cursor: pointer;
    position: relative;
    transition: color 150ms ease, border-color 150ms ease, transform 150ms ease;
    margin-left: -1px;
    margin-bottom: -1px;
    z-index: 1;
}

.sf-tab:hover {
    border-color: #9fb0c4;
    color: #0a2a4d;
    box-shadow: 0 0 0 2px rgba(57, 183, 170, 0.12);
}

.sf-tab.is-active {
    color: #0a2a4d;
    border-color: #39b7aa;
    border-bottom: none;
    border-top-color: #39b7aa;
    transform: translateY(-1px);
    box-shadow: 0 0 0 3px rgba(57, 183, 170, 0.2);
    z-index: 2;
}

.sf-tab.is-active::after {
    content: "";
    position: absolute;
    left: 0;
    right: 0;
    bottom: -1px;
    height: 2px;
    background: #ffffff;
}

.sf-tab.is-active + .sf-tab {
    border-left-color: transparent;
}

.sf-tab:first-child {
    margin-left: 0;
}

.sf-tab:focus-visible {
    outline: 2px solid rgba(44, 191, 174, 0.45);
    outline-offset: 2px;
}

.sf-tab-panels {
    padding: 24px 28px 28px;
    position: relative;
    overflow: visible;
}

.sf-tab-panel + .sf-tab-panel {
    margin-top: 24px;
}

.sf-vehicle-tabs .sf-tab-panel {
    display: none;
}

.sf-vehicle-tabs .sf-tab-panel.is-active {
    display: block;
}

.sf-tab-panels .grid.grid-2 {
    grid-template-columns: minmax(0, 1fr);
}

.sf-tab-panels .card {
    box-shadow: 0 12px 24px rgba(57, 183, 170, 0.18);
}

@media (max-width: 900px) {
    .sf-tabs {
        padding: 14px 16px 0;
    }

    .sf-tab {
        font-size: 11px;
        padding: 9px 12px 11px;
    }

    .sf-tab-panels {
        padding: 20px 20px 24px;
    }
}

@media (max-width: 640px) {
    .sf-tabs {
        overflow-x: auto;
        padding-bottom: 12px;
    }

    .sf-tab {
        flex: 0 0 auto;
    }
}

.sf-vehicle-edit .card-body {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.sf-vehicle-edit .form-group {
    margin-bottom: 12px;
}

.sf-vehicle-edit .form-row {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
    gap: 16px;
    align-items: start;
}

.sf-vehicle-edit .form-row > div {
    min-width: 0;
}

.sf-vehicle-edit .section-title {
    margin-bottom: 6px;
}

.sf-vehicle-edit .form-hint {
    margin-top: 4px;
}

.ai-input-wrap {
    position: relative;
}

.ai-input-wrap .form-control {
    padding-right: 38px;
}

.ai-clear-btn {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    border: none;
    background: rgba(10, 42, 77, 0.08);
    color: #0A2A4D;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 16px;
    line-height: 1;
    font-weight: 700;
}

.ai-clear-btn:hover {
    background: rgba(10, 42, 77, 0.18);
}

.ai-list {
    margin-top: 10px;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.ai-chip {
    border: 1px solid rgba(44, 191, 174, 0.35);
    background: rgba(44, 191, 174, 0.08);
    color: #0A2A4D;
    padding: 6px 10px;
    border-radius: 999px;
    cursor: pointer;
    font-size: 14px;
}

.ai-chip:hover {
    background: rgba(44, 191, 174, 0.18);
}
</style>

<link rel="stylesheet" href="https://unpkg.com/flatpickr/dist/flatpickr.min.css">

<script src="https://unpkg.com/flatpickr"></script>
<script>
    (function () {
        const container = document.querySelector('.sf-vehicle-tabs');
        if (!container) return;

        const tabs = Array.from(container.querySelectorAll('[data-sf-tab]'));
        const panels = Array.from(container.querySelectorAll('[data-sf-panel]'));
        if (!tabs.length || !panels.length) return;

        container.classList.add('is-js');

        const activate = (name) => {
            tabs.forEach((tab) => {
                const isActive = tab.getAttribute('data-sf-tab') === name;
                tab.classList.toggle('is-active', isActive);
                tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });

            panels.forEach((panel) => {
                const isActive = panel.getAttribute('data-sf-panel') === name;
                panel.classList.toggle('is-active', isActive);
            });
        };

        const errorPanel = panels.find((panel) => panel.querySelector('.text-error, .alert-error'));
        const initialTab = errorPanel
            ? tabs.find((tab) => tab.getAttribute('data-sf-tab') === errorPanel.getAttribute('data-sf-panel'))
            : tabs[0];

        if (initialTab) {
            activate(initialTab.getAttribute('data-sf-tab'));
        }

        tabs.forEach((tab) => {
            tab.addEventListener('click', () => {
                activate(tab.getAttribute('data-sf-tab'));
            });
        });
    })();

    (function () {
        if (typeof flatpickr === 'undefined') return;
        flatpickr('.sf-date', {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'd/m/Y',
            allowInput: true,
        });
    })();
    (function () {
        const companyDistanceUnit = @json($companyDistanceUnit);
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
            startingLabel.textContent = `Starting odometer (${companyDistanceUnit}) (optional)`;
            startingInput.placeholder = 'e.g. 124500';
        }

        if (serviceDueKmLabel) {
            if (trackingMode === 'hours') {
                serviceDueKmLabel.textContent = 'Next service due reading (hours) (optional)';
            } else if (trackingMode === 'none') {
                serviceDueKmLabel.textContent = 'Next service due reading (optional)';
            } else {
                serviceDueKmLabel.textContent = `Next service due reading (${companyDistanceUnit}) (optional)`;
            }
        }
    })();

    (function () {
        const makeInput = document.getElementById('aiMakeInput');
        const modelInput = document.getElementById('aiModelInput');
        const trimInput = document.getElementById('aiTrimInput');
        const makeList = document.getElementById('aiMakeList');
        const modelList = document.getElementById('aiModelList');
        const trimList = document.getElementById('aiTrimList');
        const makeStatus = document.getElementById('aiMakeStatus');
        const modelStatus = document.getElementById('aiModelStatus');
        const trimStatus = document.getElementById('aiTrimStatus');
        const branchSelect = document.getElementById('branch_id');
        const vehicleTypeSelect = document.getElementById('vehicle_type');
        const vehicleTypeStatus = document.getElementById('vehicleTypeStatus');
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        if (!makeInput || !modelInput || !trimInput) {
            return;
        }

        let currentMake = makeInput.value.trim();

        function setStatus(el, text) {
            if (!el) return;
            el.textContent = text;
        }

        function clearList(el) {
            if (el) el.innerHTML = '';
        }

        function clearModels() {
            modelInput.value = '';
            trimInput.value = '';
            clearList(modelList);
            clearList(trimList);
            setStatus(modelStatus, '');
            setStatus(trimStatus, '');
        }

        function clearAll() {
            makeInput.value = '';
            currentMake = '';
            clearList(makeList);
            setStatus(makeStatus, '');
            clearModels();
            if (vehicleTypeStatus) {
                vehicleTypeStatus.textContent = 'Auto-suggested from make, model, and variant.';
            }
        }

        function renderList(el, items, onPick) {
            clearList(el);
            items.forEach(item => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'ai-chip';
                btn.textContent = item;
                btn.addEventListener('click', () => onPick(item));
                el.appendChild(btn);
            });
        }

        async function postJson(url, payload) {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload),
            });
            if (!res.ok) return { items: [] };
            return res.json();
        }

        function getBranchId() {
            if (!branchSelect) {
                return 0;
            }
            const value = parseInt(branchSelect.value || '0', 10);
            return Number.isFinite(value) ? value : 0;
        }

        async function fetchMakes() {
            const query = (makeInput.value || '').trim();
            if (query.length < 2) {
                clearList(makeList);
                setStatus(makeStatus, 'Type at least 2 characters.');
                return;
            }
            setStatus(makeStatus, 'Loading makes...');
            const data = await postJson('/app/sharpfleet/admin/vehicles-ai-test/makes', {
                query,
                branch_id: getBranchId(),
            });
            setStatus(makeStatus, data.items.length ? 'Pick a make.' : 'No makes found.');
            renderList(makeList, data.items, (item) => {
                currentMake = item;
                makeInput.value = item;
                clearList(makeList);
                setStatus(makeStatus, 'Make selected.');
                clearModels();
                modelInput.focus();
                fetchModels();
            });
        }

        async function fetchModels() {
            const query = (modelInput.value || '').trim();
            if (!currentMake) {
                clearList(modelList);
                setStatus(modelStatus, 'Select a make first.');
                return;
            }
            setStatus(modelStatus, 'Loading models...');
            const data = await postJson('/app/sharpfleet/admin/vehicles-ai-test/models', {
                make: currentMake,
                query,
                branch_id: getBranchId(),
            });
            setStatus(modelStatus, data.items.length ? 'Pick a model.' : 'No models found.');
            renderList(modelList, data.items, (item) => {
                modelInput.value = item;
                clearList(modelList);
                setStatus(modelStatus, 'Model selected.');
                trimInput.focus();
                fetchTrims();
                fetchVehicleType();
            });
        }

        async function fetchTrims() {
            const query = (trimInput.value || '').trim();
            if (!currentMake || !modelInput.value.trim()) {
                clearList(trimList);
                setStatus(trimStatus, 'Select a make and model first.');
                return;
            }
            setStatus(trimStatus, 'Loading variants...');
            const data = await postJson('/app/sharpfleet/admin/vehicles-ai-test/trims', {
                make: currentMake,
                model: modelInput.value.trim(),
                query,
                branch_id: getBranchId(),
            });
            setStatus(trimStatus, data.items.length ? 'Pick a variant.' : 'No variants found.');
            renderList(trimList, data.items, (item) => {
                trimInput.value = item;
                clearList(trimList);
                setStatus(trimStatus, 'Variant selected.');
                fetchVehicleType();
            });
        }

        function normalizeVehicleType(type) {
            const raw = (type || '').toLowerCase().trim();
            if (raw === 'pickup' || raw === 'truck' || raw === 'light_truck') {
                return 'ute';
            }
            if (raw === 'excavator') {
                return 'ex';
            }
            if (raw === 'bulldozer') {
                return 'dozer';
            }
            const allowed = new Set(['sedan', 'hatch', 'suv', 'van', 'bus', 'ute', 'ex', 'dozer', 'other']);
            return allowed.has(raw) ? raw : 'other';
        }

        async function fetchVehicleType() {
            const make = currentMake.trim();
            const model = modelInput.value.trim();
            if (!make || !model || !vehicleTypeSelect) {
                return;
            }
            if (vehicleTypeStatus) {
                vehicleTypeStatus.textContent = 'Suggesting vehicle type...';
            }
            const data = await postJson('/app/sharpfleet/admin/vehicles-ai-test/type', {
                make,
                model,
                variant: trimInput.value.trim(),
                branch_id: getBranchId(),
            });
            const selected = normalizeVehicleType(data.type);
            vehicleTypeSelect.value = selected;
            if (vehicleTypeStatus) {
                vehicleTypeStatus.textContent = 'Auto-suggested from make, model, and variant.';
            }
        }

        function debounce(fn, delay, timerRef) {
            return function () {
                clearTimeout(timerRef.value);
                timerRef.value = setTimeout(fn, delay);
            };
        }

        const makeTimerRef = { value: null };
        const modelTimerRef = { value: null };
        const trimTimerRef = { value: null };
        const typeTimerRef = { value: null };

        makeInput.addEventListener('input', debounce(fetchMakes, 300, makeTimerRef));
        modelInput.addEventListener('input', debounce(fetchModels, 300, modelTimerRef));
        trimInput.addEventListener('input', debounce(fetchTrims, 300, trimTimerRef));
        trimInput.addEventListener('input', debounce(fetchVehicleType, 500, typeTimerRef));

        makeInput.addEventListener('change', () => {
            currentMake = makeInput.value.trim();
            clearModels();
        });
        modelInput.addEventListener('change', () => {
            fetchVehicleType();
        });

        if (branchSelect) {
            branchSelect.addEventListener('change', () => {
                clearAll();
            });
        }

        document.querySelectorAll('.ai-clear-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const target = btn.getAttribute('data-clear');
                if (target === 'make') {
                    clearAll();
                } else if (target === 'model') {
                    clearModels();
                } else if (target === 'trim') {
                    trimInput.value = '';
                    clearList(trimList);
                    setStatus(trimStatus, '');
                    fetchVehicleType();
                }
            });
        });
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

    (function () {
        const branchSelect = document.getElementById('branch_id');
        const vehicleTypeSelect = document.getElementById('vehicle_type');

        function resolvePickupLabelFromTimezone(tz) {
            const value = (tz || '').toLowerCase();
            if (value.startsWith('australia/') || value.startsWith('pacific/auckland')) {
                return 'Ute';
            }
            if (value.startsWith('africa/') || value.includes('johannesburg')) {
                return 'Bakkie';
            }
            if (value.startsWith('america/') || value.startsWith('us/')) {
                return 'Pickup / Light Truck';
            }
            return 'Pickup / Light Truck';
        }

        function updatePickupLabel() {
            if (!vehicleTypeSelect) return;
            const option = vehicleTypeSelect.querySelector('option[value="ute"]');
            if (!option) return;
            if (!branchSelect) {
                option.textContent = 'Pickup / Light Truck';
                return;
            }
            const tz = branchSelect.selectedOptions[0]?.getAttribute('data-timezone') || '';
            option.textContent = resolvePickupLabelFromTimezone(tz);
        }

        if (branchSelect) {
            branchSelect.addEventListener('change', updatePickupLabel);
        }

        updatePickupLabel();
    })();
</script>

@endsection
