@extends('layouts.sharpfleet')

@section('title', 'Add Vehicle / Asset')

@section('sharpfleet-content')

@php
    $vehicleRegistrationTrackingEnabled = (bool) ($vehicleRegistrationTrackingEnabled ?? false);
    $vehicleServicingTrackingEnabled = (bool) ($vehicleServicingTrackingEnabled ?? false);
    $branchesEnabled = (bool) ($branchesEnabled ?? false);
    $branches = $branches ?? collect();
    $defaultBranchId = $defaultBranchId ?? null;
    $companyDistanceUnit = (string) ($companyDistanceUnit ?? 'km');
@endphp

<div class="max-w-800 mx-auto mt-4">

    <h1 class="page-title mb-1">Add Vehicle / Asset</h1>
    <p class="page-description mb-3">
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

            @if($branchesEnabled)
                <label class="form-label mt-2">Branch</label>
                <select name="branch_id" id="branch_id" class="form-control">
                    @foreach($branches as $b)
                        <option value="{{ (int) $b->id }}" {{ (int) old('branch_id', $defaultBranchId) === (int) $b->id ? 'selected' : '' }}>
                            {{ (string) ($b->name ?? '') }} ({{ (string) ($b->timezone ?? '') }})
                        </option>
                    @endforeach
                </select>
                <div class="form-hint">Booking and trip times will use this branch timezone.</div>
                @error('branch_id')
                    <div class="text-error mb-2">{{ $message }}</div>
                @enderror
            @endif

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
                    Distance ({{ $companyDistanceUnit }})
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

                 {{-- Starting reading (optional; distance unit or hours depending on tracking mode) --}}
                 <label id="starting_reading_label" class="form-label mt-2">Starting odometer ({{ $companyDistanceUnit }}) (optional)</label>
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

            <hr class="my-3">
            <h3 class="section-title">AI assist</h3>
            <div class="form-hint mb-2">Tip: Start typing and we'll do the rest.</div>

            {{-- Make / Model (AI-assisted) --}}
            <div class="form-row">
                <div>
                    <label class="form-label">Make</label>
                    <div class="ai-input-wrap">
                        <input id="aiMakeInput"
                               type="text"
                               name="make"
                               value="{{ old('make') }}"
                               placeholder="Start typing a make"
                               class="form-control">
                        <button type="button" class="ai-clear-btn" data-clear="make" aria-label="Clear make">x</button>
                    </div>
                    <div id="aiMakeStatus" class="form-hint"></div>
                    <div id="aiMakeList" class="ai-list"></div>
                </div>

                <div>
                    <label class="form-label">Model</label>
                    <div class="ai-input-wrap">
                        <input id="aiModelInput"
                               type="text"
                               name="model"
                               value="{{ old('model') }}"
                               placeholder="Start typing a model"
                               class="form-control">
                        <button type="button" class="ai-clear-btn" data-clear="model" aria-label="Clear model">x</button>
                    </div>
                    <div id="aiModelStatus" class="form-hint"></div>
                    <div id="aiModelList" class="ai-list"></div>
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
                        <option value="ute" {{ $vt === 'ute' ? 'selected' : '' }}>Ute</option>
                        <option value="hatch" {{ $vt === 'hatch' ? 'selected' : '' }}>Hatch</option>
                        <option value="suv" {{ $vt === 'suv' ? 'selected' : '' }}>SUV</option>
                        <option value="van" {{ $vt === 'van' ? 'selected' : '' }}>Van</option>
                        <option value="bus" {{ $vt === 'bus' ? 'selected' : '' }}>Bus</option>
                        <option value="ex" {{ $vt === 'ex' ? 'selected' : '' }}>Excavator</option>
                        <option value="dozer" {{ $vt === 'dozer' ? 'selected' : '' }}>Bulldozer</option>
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
                        <label id="service_due_km_label" class="form-label">Next service due reading ({{ $companyDistanceUnit }}) (optional)</label>
                        <input type="number" name="service_due_km" value="{{ old('service_due_km') }}" class="form-control" inputmode="numeric" min="0" placeholder="e.g. 150000">
                        @error('service_due_km')
                            <div class="text-error mb-2">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            @endif

        </div>

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

<style>
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

<script>
    const companyDistanceUnit = @json($companyDistanceUnit);

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

    // Tracking mode toggles the label between distance-unit and hours
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
            startingLabel.textContent = `Starting odometer (${companyDistanceUnit}) (optional)`;
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
            serviceDueKmLabel.textContent = `Next service due reading (${companyDistanceUnit}) (optional)`;
        }
    }
    if (trackingMode) {
        trackingMode.addEventListener('change', updateServiceDueKmLabel);
    }
    updateServiceDueKmLabel();

    const makeInput = document.getElementById('aiMakeInput');
    const modelInput = document.getElementById('aiModelInput');
    const makeList = document.getElementById('aiMakeList');
    const modelList = document.getElementById('aiModelList');
    const makeStatus = document.getElementById('aiMakeStatus');
    const modelStatus = document.getElementById('aiModelStatus');
    const branchSelect = document.getElementById('branch_id');
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    if (makeInput && modelInput) {
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
            clearList(modelList);
            setStatus(modelStatus, '');
        }

        function clearAll() {
            makeInput.value = '';
            currentMake = '';
            clearList(makeList);
            setStatus(makeStatus, '');
            clearModels();
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
            });
        }

        function debounce(fn, delay, timerRef) {
            return function () {
                clearTimeout(timerRef.value);
                timerRef.value = setTimeout(fn, delay);
            };
        }

        const makeTimerRef = { value: null };
        const modelTimerRef = { value: null };

        makeInput.addEventListener('input', debounce(fetchMakes, 300, makeTimerRef));
        modelInput.addEventListener('input', debounce(fetchModels, 300, modelTimerRef));

        makeInput.addEventListener('change', () => {
            currentMake = makeInput.value.trim();
            clearModels();
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
                }
            });
        });
    }

</script>

@endsection
