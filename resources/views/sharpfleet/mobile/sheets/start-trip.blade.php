{{-- =========================================================
     SharpFleet Mobile – Start Trip Sheet
     Full Desktop Form Reused (Mobile Safe)
     Backend logic unchanged
========================================================= --}}

<div
    id="sf-sheet-start-trip"
    class="sf-sheet"
    role="dialog"
    aria-modal="true"
    aria-hidden="true"
    aria-labelledby="sf-start-trip-title"
>

    {{-- ===============================
         Sheet Header
    ================================ --}}
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

    {{-- ===============================
         Sheet Body
    ================================ --}}
    <div class="sf-sheet-body">

        <form method="POST" action="/app/sharpfleet/trips/start" id="startTripForm">
            @csrf

            {{-- ===============================
                 Vehicle
            ================================ --}}
            <div class="form-group">
                <label class="form-label">Vehicle</label>

                @if($vehicles->count() > 10)
                    <input
                        type="text"
                        id="vehicleSearchInput"
                        class="form-control"
                        placeholder="Start typing to search (e.g. black toyota / camry / ABC123)"
                    >
                    <div id="vehicleSearchHint" class="hint-text">Showing {{ $vehicles->count() }} vehicles</div>
                @endif

                <select
                    id="vehicleSelect"
                    name="vehicle_id"
                    class="form-control"
                    required
                >
                    @foreach ($vehicles as $vehicle)
                        @php
                            $vehicleBranchId = property_exists($vehicle, 'branch_id')
                                ? (int) ($vehicle->branch_id ?? 0)
                                : 0;

                            $vehicleDistanceUnit = $settingsService->distanceUnitForBranch(
                                $vehicleBranchId > 0 ? $vehicleBranchId : null
                            );
                        @endphp

                        <option
                            value="{{ $vehicle->id }}"
                            data-tracking-mode="{{ $vehicle->tracking_mode ?? 'distance' }}"
                            data-distance-unit="{{ $vehicleDistanceUnit }}"
                            data-last-km="{{ $lastTrips[$vehicle->id]->end_km ?? (property_exists($vehicle, 'starting_km') ? ($vehicle->starting_km ?? '') : '') }}"
                        >
                            {{ $vehicle->name }} ({{ $vehicle->registration_number }})
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- ===============================
                 Manual Start Time
            ================================ --}}
            @if($manualTripTimesRequired)
                <div class="form-group">
                    <label class="form-label">Start time</label>

                    <input
                        type="datetime-local"
                        name="started_at"
                        class="form-control sharpfleet-trip-datetime"
                        required
                    >

                    <div class="hint-text">
                        Enter the local time for this trip.
                    </div>
                </div>
            @endif

            {{-- ===============================
                 Trip Type
            ================================ --}}
            <div class="form-group">
                <label class="form-label">Trip Type</label>

                @if($allowPrivateTrips)
                    <div class="radio-group">
                        <label class="radio-label">
                            <input
                                type="radio"
                                name="trip_mode"
                                value="business"
                                checked
                            >
                            Business
                        </label>

                        <label class="radio-label">
                            <input
                                type="radio"
                                name="trip_mode"
                                value="private"
                            >
                            Private
                        </label>
                    </div>
                @else
                    <div class="hint-text">Business</div>
                    <input type="hidden" name="trip_mode" value="business">
                @endif
            </div>

            {{-- ===============================
                 Client Presence
            ================================ --}}
            @if(($settings['client_presence']['enabled'] ?? false) === true)
                <div id="clientPresenceBlock">
                    <div class="form-group">
                    <label class="form-label">
                        {{ $settings['client_presence']['label'] ?? 'Client' }} Present?
                        {{ ($settings['client_presence']['required'] ?? false) ? '(Required)' : '' }}
                    </label>

                    <select
                        name="client_present"
                        class="form-control"
                        {{ ($settings['client_presence']['required'] ?? false) ? 'required' : '' }}
                    >
                        <option value="">— Select —</option>
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </div>

                @if(($settings['client_presence']['enable_addresses'] ?? false) === true)
                    <div class="form-group">
                        <label class="form-label">Client Address (for billing/job tracking)</label>

                        <input
                            type="text"
                            name="client_address"
                            class="form-control"
                            placeholder="e.g. 123 Main St, Suburb"
                        >
                    </div>
                @endif
                </div>
            @endif

            {{-- ===============================
                 Customer / Client
            ================================ --}}
            @if(($settings['customer']['enabled'] ?? false) && (($settings['customer']['allow_select'] ?? true) || ($settings['customer']['allow_manual'] ?? true)))
                @php
                    $partyLabel = trim((string) $settingsService->clientLabel());
                    $partyLabelLower = mb_strtolower($partyLabel !== '' ? $partyLabel : 'customer');
                @endphp
                <div id="customerBlock" class="form-group">
                    <label class="form-label">{{ $partyLabel !== '' ? $partyLabel : 'Customer' }} (optional)</label>

                    @if(($settings['customer']['allow_select'] ?? true) && $customers->count() > 0)
                        <select id="customerSelect" name="customer_id" class="form-control">
                            <option value="">- Select from list -</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                        <div class="hint-text">If the {{ $partyLabelLower }} isn't in the list, type a name below.</div>
                    @endif

                    @if($settings['customer']['allow_manual'] ?? true)
                        <input
                            id="customerNameInput"
                            type="text"
                            name="customer_name"
                            class="form-control mt-2"
                            maxlength="150"
                            placeholder="Or enter {{ $partyLabelLower }} name (e.g. Jannie B / Job 12345)"
                        >
                    @endif
                </div>
            @endif

            {{-- ===============================
                 Purpose of Travel
            ================================ --}}
            @if($settingsService->purposeOfTravelEnabled())
                <div id="purposeOfTravelBlock" class="form-group">
                    <label class="form-label">Purpose of Travel (optional)</label>
                    <input
                        type="text"
                        name="purpose_of_travel"
                        class="form-control"
                        maxlength="255"
                        placeholder="e.g. Materials pickup at Bunnings"
                    >
                </div>
            @endif

            {{-- ===============================
                 Starting Reading
            ================================ --}}
            <div class="form-group">
                @php $defaultDistanceUnit = $settingsService->distanceUnit(); @endphp

                <label id="startReadingLabel" class="form-label">
                    Starting odometer ({{ $defaultDistanceUnit }})
                </label>

                <div id="lastKmHint" class="hint-text d-none"></div>

                <input
                    type="number"
                    id="startKmInput"
                    name="start_km"
                    class="form-control"
                    inputmode="numeric"
                    placeholder="e.g. 124500"
                    {{ $odometerRequired ? 'required' : '' }}
                    {{ $odometerAllowOverride ? '' : 'readonly' }}
                >

                @if(!$odometerRequired)
                    <div class="hint-text">
                        If left blank, the last recorded reading will be used.
                    </div>
                @endif
            </div>

            {{-- ===============================
                 Safety Check
            ================================ --}}
            @if($safetyCheckEnabled)
                @php
                    $safetyCount = is_array($safetyCheckItems) ? count($safetyCheckItems) : 0;
                @endphp

                <div class="form-group" id="preDriveSafetyCheckBlock">
                    <label class="form-label">Pre-Drive Safety Check</label>

                    @if($safetyCount > 0)
                        <div class="hint-text" style="margin-bottom: 6px;">
                            Complete the checks below before starting your trip.
                        </div>

                        <ul class="text-muted" style="margin-left:16px;">
                            @foreach($safetyCheckItems as $item)
                                <li>{{ $item['label'] ?? '' }}</li>
                            @endforeach
                        </ul>

                        <label class="checkbox-label">
                            <input
                                type="checkbox"
                                name="safety_check_confirmed"
                                value="1"
                                required
                            >
                            <strong>I have completed the safety check</strong>
                        </label>
                    @else
                        <div class="alert alert-info">
                            Safety checks are enabled, but no checklist items are configured yet.
                            Please ask an admin to configure the checklist.
                        </div>
                    @endif
                </div>
            @endif

            {{-- ===============================
                 Submit
            ================================ --}}
            <button
                type="submit"
                class="sf-mobile-primary-btn"
                style="margin-top: 12px;"
            >
                Start Trip
            </button>

        </form>

        <script>
        (function () {
            const vehicleSelect = document.getElementById('vehicleSelect');
            if (!vehicleSelect) return;

            const vehicleSearchInput = document.getElementById('vehicleSearchInput');
            const vehicleSearchHint = document.getElementById('vehicleSearchHint');
            const startKmInput = document.getElementById('startKmInput');
            const lastKmHint = document.getElementById('lastKmHint');
            const startReadingLabel = document.getElementById('startReadingLabel');

            let lastAutoFilledReading = null;

            const allVehicleOptions = Array.from(vehicleSelect.options).map(opt => ({
                value: opt.value,
                text: opt.text,
                trackingMode: opt.dataset.trackingMode || 'distance',
                distanceUnit: opt.dataset.distanceUnit || 'km',
                lastKm: opt.dataset.lastKm || ''
            }));

            function rebuildVehicleOptions(filtered) {
                const currentValue = vehicleSelect.value;
                vehicleSelect.innerHTML = '';

                filtered.forEach(v => {
                    const opt = document.createElement('option');
                    opt.value = v.value;
                    opt.textContent = v.text;
                    opt.dataset.trackingMode = v.trackingMode;
                    opt.dataset.distanceUnit = v.distanceUnit;
                    opt.dataset.lastKm = v.lastKm;
                    vehicleSelect.appendChild(opt);
                });

                if (filtered.length === 0) {
                    const opt = document.createElement('option');
                    opt.value = '';
                    opt.textContent = 'No vehicles match your search';
                    vehicleSelect.appendChild(opt);
                    vehicleSelect.value = '';
                    return;
                }

                const stillExists = filtered.some(v => v.value === currentValue);
                vehicleSelect.value = stillExists ? currentValue : filtered[0].value;
                updateStartKm();
            }

            function filterVehicles() {
                const q = (vehicleSearchInput?.value || '').trim().toLowerCase();
                const filtered = q
                    ? allVehicleOptions.filter(v => v.text.toLowerCase().includes(q))
                    : allVehicleOptions;

                if (vehicleSearchHint) {
                    vehicleSearchHint.textContent = `Showing ${filtered.length} of ${allVehicleOptions.length} vehicles`;
                }

                rebuildVehicleOptions(filtered);
            }

            function updateStartKm() {
                const selected = vehicleSelect.options[vehicleSelect.selectedIndex];
                if (!selected) return;

                const lastKm = selected.dataset.lastKm || '';
                const mode = selected.dataset.trackingMode || 'distance';
                const distanceUnit = selected.dataset.distanceUnit || 'km';

                if (startReadingLabel && startKmInput) {
                    if (mode === 'hours') {
                        startReadingLabel.textContent = 'Starting hour meter (hours)';
                        startKmInput.placeholder = 'e.g. 1250';
                    } else {
                        startReadingLabel.textContent = `Starting odometer (${distanceUnit})`;
                        startKmInput.placeholder = 'e.g. 124500';
                    }
                }

                if (startKmInput) {
                    const currentVal = (startKmInput.value || '').trim();
                    const canAutofill = currentVal === '' ||
                        (lastAutoFilledReading !== null && currentVal === String(lastAutoFilledReading));

                    if (lastKm) {
                        if (canAutofill) {
                            startKmInput.value = lastKm;
                            lastAutoFilledReading = lastKm;
                        }
                        if (lastKmHint) {
                            lastKmHint.textContent = (mode === 'hours')
                                ? `Last recorded hour meter: ${Number(lastKm).toLocaleString()} hours`
                                : `Last recorded odometer: ${Number(lastKm).toLocaleString()} ${distanceUnit}`;
                            lastKmHint.classList.remove('d-none');
                        }
                    } else if (lastKmHint) {
                        if (canAutofill) {
                            startKmInput.value = '';
                            lastAutoFilledReading = null;
                        }
                        lastKmHint.classList.add('d-none');
                    }
                }
            }

            vehicleSelect.addEventListener('change', updateStartKm);
            if (vehicleSearchInput) {
                vehicleSearchInput.addEventListener('input', filterVehicles);
            }

            updateStartKm();
        })();
        </script>

    </div>
</div>
