{{-- =========================================================
     SharpFleet Mobile - Start Trip Sheet     
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

        <form method="POST" action="/app/sharpfleet/trips/start" id="startTripForm" novalidate>
            @csrf

            {{-- ===============================
                 Vehicle
            ================================ --}}
            <div class="form-group" style="margin-bottom: 16px;">
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
                Start Time
            =============================== --}}
            @if($manualTripTimesRequired)
                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label class="form-label">Start time</label>

                    <div class="sf-datetime-wrap">
                        <input
                            type="datetime-local"
                            name="started_at"
                            class="form-control sharpfleet-trip-datetime"
                            required
                        >
                    </div>

                    <div class="hint-text">
                        Enter the local time for this trip.
                    </div>
                </div>
            @endif


            {{-- ===============================
                 Starting Reading
            ================================ --}}
            <div class="form-group" style="margin-bottom: 1.25rem;">

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

            @php
                $clientPresenceLabel = trim((string) ($settings['client_presence']['label'] ?? 'Client'));
                $clientPresenceLabel = $clientPresenceLabel !== '' ? $clientPresenceLabel : 'Client';
            @endphp

            {{-- ===============================
                 Before You Start
            ================================ --}}
            <div class="form-group" style="margin-top:20px;">
                <div class="form-label" style="margin-bottom:8px;">
                    Before you start
                </div>

                {{-- Trip Details --}}
                <button
                    type="button"
                    class="sf-mobile-secondary-btn sf-mobile-action-pill"
                    data-sheet-open="trip-details"
                    style="
                        width:100%;
                        justify-content:space-between;
                        margin-bottom:10px;
                    "
                >
                    <span>Trip Details</span>
                    <ion-icon id="tripDetailsStatus" class="sf-status-icon" name="ellipse-outline"></ion-icon>
                </button>

                {{-- Client / Customer --}}
                <button
                    type="button"
                    class="sf-mobile-secondary-btn sf-mobile-action-pill"
                    data-sheet-open="client-details"
                    style="
                        width:100%;
                        justify-content:space-between;
                        margin-bottom:10px;
                    "
                >
                    <span>{{ $clientPresenceLabel }}</span>
                    <ion-icon id="clientStatus" class="sf-status-icon" name="ellipse-outline"></ion-icon>
                </button>

                {{-- Safety Check --}}
                @if($safetyCheckEnabled)
                    <button
                        type="button"
                        class="sf-mobile-secondary-btn sf-mobile-action-pill"
                        data-sheet-open="safety-check"
                        style="
                            width:100%;
                            justify-content:space-between;
                        "
                    >
                        <span>Safety Check</span>
                        <ion-icon id="safetyStatus" class="sf-status-icon" name="ellipse-outline"></ion-icon>
                    </button>
                @endif
            </div>

            {{-- ===============================
                 Submit
            ================================ --}}
            <button
                type="submit"
                class="sf-mobile-primary-btn"
                style="margin-top:18px;"
            >
                Start Trip
            </button>

        </form>
    </div>
</div>

<div
    id="sf-mobile-validation-modal"
    class="sf-mobile-modal"
    role="dialog"
    aria-modal="true"
    aria-hidden="true"
    hidden
>
    <div class="sf-mobile-modal-backdrop" data-modal-close></div>
    <div class="sf-mobile-modal-card" role="document">
        <div class="sf-mobile-modal-header">
            <div class="sf-mobile-modal-title">Complete required sections</div>
            <button type="button" class="sf-sheet-close" data-modal-close aria-label="Close">
                <ion-icon name="close-outline"></ion-icon>
            </button>
        </div>
        <div class="hint-text">Please complete:</div>
        <ul id="sf-mobile-validation-list" class="sf-mobile-modal-list"></ul>
        <button type="button" class="sf-mobile-primary-btn" data-modal-close>
            OK
        </button>
    </div>
</div>

{{-- =========================================================
     TRIP DETAILS SHEET
========================================================= --}}
<div id="sf-sheet-trip-details" class="sf-sheet" aria-hidden="true">
    <div class="sf-sheet-header">
        <h2>Trip Details</h2>
        <button type="button" class="sf-sheet-close" data-sheet-close="self">
            <ion-icon name="close-outline"></ion-icon>
        </button>
    </div>

    <div class="sf-sheet-body">
        <div class="form-group">
            <label class="form-label">Trip Type</label>

        @if($allowPrivateTrips)
            <div class="radio-group">
                <label class="radio-label">
                    <input type="radio" name="trip_mode" value="business" checked form="startTripForm">
                    Business
                </label>
                <label class="radio-label">
                    <input type="radio" name="trip_mode" value="private" form="startTripForm">
                    Private
                </label>
            </div>
        @else
            <input type="hidden" name="trip_mode" value="business" form="startTripForm">
            <div class="hint-text">Business</div>
        @endif
        </div>

        @if($settingsService->purposeOfTravelEnabled())
            <div id="purposeOfTravelBlock" class="form-group">
                <label class="form-label">Purpose of Travel (optional)</label>
                <input
                    type="text"
                    name="purpose_of_travel"
                    class="form-control"
                    maxlength="255"
                    placeholder="e.g. Materials pickup at Bunnings"
                    form="startTripForm"
                >
            </div>
        @endif

        <button type="button" class="sf-mobile-primary-btn" data-sheet-close="self" data-status-target="tripDetailsStatus">
            Save
        </button>
    </div>
</div>

{{-- =========================================================
     CLIENT / CUSTOMER SHEET
========================================================= --}}
<div id="sf-sheet-client-details" class="sf-sheet" aria-hidden="true">
    <div class="sf-sheet-header">
        @php
            $partySheetLabel = trim((string) $settingsService->clientLabel());
        @endphp
        <h2>{{ $partySheetLabel !== '' ? $partySheetLabel : $clientPresenceLabel }}</h2>
        <button type="button" class="sf-sheet-close" data-sheet-close="self">
            <ion-icon name="close-outline"></ion-icon>
        </button>
    </div>

    <div class="sf-sheet-body">
        @if(($settings['customer']['enabled'] ?? false) && (($settings['customer']['allow_select'] ?? true) || ($settings['customer']['allow_manual'] ?? true)))
            @php
                $partyLabel = trim((string) $settingsService->clientLabel());
                $partyLabelLower = mb_strtolower($partyLabel !== '' ? $partyLabel : 'customer');
            @endphp
            <div id="customerBlock" class="form-group" style="margin-bottom: 14px;">
                <label class="form-label">{{ $partyLabel !== '' ? $partyLabel : 'Customer' }} (optional)</label>

                @if(($settings['customer']['allow_select'] ?? true) && $customers->count() > 0)
                    <select id="customerSelect" name="customer_id" class="form-control" form="startTripForm">
                        <option value="">- Select from list -</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                    <div class="hint-text mb-3">If the {{ $partyLabelLower }} isn't in the list, type a name below.</div>
                @endif

                @if($settings['customer']['allow_manual'] ?? true)
                    <input
                        id="customerNameInput"
                        type="text"
                        name="customer_name"
                        class="form-control mt-2 mb-3"
                        maxlength="150"
                        placeholder="Or enter {{ $partyLabelLower }} name (e.g. John Doe or Job 12345)"
                        form="startTripForm"
                    >
                @endif
            </div>
        @endif

        @if(($settings['client_presence']['enabled'] ?? false) === true)
            <div id="clientPresenceBlock">
                <div class="form-group" style="margin-top: 12px;">
                    <label class="form-label">
                        {{ $settings['client_presence']['label'] ?? 'Client' }} Present?
                        {{ ($settings['client_presence']['required'] ?? false) ? '(Required)' : '' }}
                    </label>

                    <select
                        name="client_present"
                        class="form-control"
                        {{ ($settings['client_presence']['required'] ?? false) ? 'required' : '' }}
                        form="startTripForm"
                    >
                        <option value="">- Select -</option>
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
                            form="startTripForm"
                        >
                    </div>
                @endif
            </div>
        @endif

        <button type="button" class="sf-mobile-primary-btn" data-sheet-close="self" data-status-target="clientStatus">
            Save
        </button>
    </div>
</div>

{{-- =========================================================
     SAFETY CHECK SHEET
========================================================= --}}
@if($safetyCheckEnabled)
<div id="sf-sheet-safety-check" class="sf-sheet" aria-hidden="true">
    <div class="sf-sheet-header">
        <h2>Safety Check</h2>
        <button type="button" class="sf-sheet-close" data-sheet-close="self">
            <ion-icon name="close-outline"></ion-icon>
        </button>
    </div>

    <div class="sf-sheet-body">
        @php
            $safetyCount = is_array($safetyCheckItems) ? count($safetyCheckItems) : 0;
        @endphp

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
                <input type="checkbox" name="safety_check_confirmed" value="1" required form="startTripForm">
                I have completed the safety check
            </label>
        @else
            <div class="alert alert-info">
                Safety checks are enabled, but no checklist items are configured yet.
                Please ask an admin to configure the checklist.
            </div>
        @endif

        <button type="button" class="sf-mobile-primary-btn" data-sheet-close="self" data-status-target="safetyStatus">
            Confirm
        </button>
    </div>
</div>
@endif

<script>
(function () {
    const CLIENT_PRESENCE_LABEL = @json($clientPresenceLabel);

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
        if (!selected || !startKmInput) return;

        const lastKm = selected.dataset.lastKm || '';
        const mode = selected.dataset.trackingMode || 'distance';
        const distanceUnit = selected.dataset.distanceUnit || 'km';

        if (startReadingLabel) {
            if (mode === 'hours') {
                startReadingLabel.textContent = 'Starting hour meter (hours)';
                startKmInput.placeholder = 'e.g. 1250';
            } else {
                startReadingLabel.textContent = `Starting odometer (${distanceUnit})`;
                startKmInput.placeholder = 'e.g. 124500';
            }
        }

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

    const customerBlock = document.getElementById('customerBlock');
    const clientPresenceBlock = document.getElementById('clientPresenceBlock');
    const customerSelect = document.getElementById('customerSelect');
    const customerNameInput = document.getElementById('customerNameInput');
    const purposeOfTravelBlock = document.getElementById('purposeOfTravelBlock');

    const tripModeRadios = document.querySelectorAll('input[name=\"trip_mode\"][type=\"radio\"]');
    const tripModeHidden = document.querySelector('input[name=\"trip_mode\"][type=\"hidden\"]');

    function updateBusinessOnlyBlocksVisibility() {
        const selected = document.querySelector('input[name=\"trip_mode\"][type=\"radio\"]:checked');
        const mode = selected ? selected.value : (tripModeHidden ? tripModeHidden.value : 'business');
        const isBusinessTrip = mode !== 'private';

        if (customerBlock) {
            customerBlock.style.display = isBusinessTrip ? '' : 'none';
        }
        if (clientPresenceBlock) {
            clientPresenceBlock.style.display = isBusinessTrip ? '' : 'none';
        }
        if (purposeOfTravelBlock) {
            purposeOfTravelBlock.style.display = isBusinessTrip ? '' : 'none';
        }
    }

    if (customerSelect && customerNameInput) {
        customerSelect.addEventListener('change', () => {
            if (customerSelect.value) {
                customerNameInput.value = '';
            }
        });

        customerNameInput.addEventListener('input', () => {
            if (customerNameInput.value.trim()) {
                customerSelect.value = '';
            }
        });
    }

    vehicleSelect.addEventListener('change', updateStartKm);
    if (vehicleSearchInput) {
        vehicleSearchInput.addEventListener('input', filterVehicles);
    }
    tripModeRadios.forEach(r => r.addEventListener('change', updateBusinessOnlyBlocksVisibility));

    document.querySelectorAll('[data-status-target]').forEach(btn => {
        btn.addEventListener('click', () => {
            const targetId = btn.getAttribute('data-status-target');
            const target = targetId ? document.getElementById(targetId) : null;
            if (!target) return;
            target.setAttribute('name', 'checkmark');
            target.classList.add('sf-status-complete');
        });
    });

    const startTripForm = document.getElementById('startTripForm');
    const validationModal = document.getElementById('sf-mobile-validation-modal');
    const validationList = document.getElementById('sf-mobile-validation-list');

    function closeValidationModal() {
        if (!validationModal) return;
        validationModal.classList.remove('is-open');
        validationModal.setAttribute('aria-hidden', 'true');
        validationModal.hidden = true;
        document.body.style.overflow = '';
    }

    function openValidationModal(items) {
        if (!validationModal || !validationList) return;
        validationList.innerHTML = '';
        items.forEach(item => {
            const li = document.createElement('li');
            li.textContent = item;
            validationList.appendChild(li);
        });
        validationModal.classList.add('is-open');
        validationModal.setAttribute('aria-hidden', 'false');
        validationModal.hidden = false;
        document.body.style.overflow = 'hidden';
    }

    document.querySelectorAll('[data-modal-close]').forEach(btn => {
        btn.addEventListener('click', closeValidationModal);
    });

    function sectionHasInvalid(selectors) {
        return selectors.some(sel => {
            const el = document.querySelector(sel);
            if (!el) return false;
            return !el.checkValidity();
        });
    }

    if (startTripForm) {
        startTripForm.addEventListener('submit', (e) => {
            if (startTripForm.checkValidity()) return;
            e.preventDefault();

            const missing = [];
            if (sectionHasInvalid(['#vehicleSelect'])) {
                missing.push('Vehicle');
            }
            if (sectionHasInvalid(['input[name=\"started_at\"][form=\"startTripForm\"]', '#startKmInput'])) {
                missing.push('Trip Details');
            }
            if (sectionHasInvalid(['select[name=\"client_present\"][form=\"startTripForm\"]'])) {
                missing.push(CLIENT_PRESENCE_LABEL);
            }
            if (sectionHasInvalid(['input[name=\"safety_check_confirmed\"][form=\"startTripForm\"]'])) {
                missing.push('Safety Check');
            }

            if (missing.length === 0) {
                missing.push('Trip form');
            }

            openValidationModal(missing);
        });
    }

    updateStartKm();
    updateBusinessOnlyBlocksVisibility();
})();
</script>
