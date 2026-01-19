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

            <div
                id="sf-safety-banner"
                class="sf-mobile-card"
                style="margin-bottom: 16px; border: 1px solid #d84b4b; box-shadow: 0 0 0 2px rgba(216, 75, 75, 0.18), 0 0 10px rgba(216, 75, 75, 0.25); transition: opacity 250ms ease, transform 250ms ease;"
            >
                <div class="sf-mobile-card-title">⚠️ Safety reminder</div>
                <div class="hint-text" style="margin-top: 6px;">
                    Please don't use your phone while driving. Start and end your trip when it's safe.
                </div>
            </div>

            {{-- ===============================
                 Vehicle
            ================================ --}}
            <div class="form-group" id="vehicleBlock" style="margin-bottom: 24px;">
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
                    @if($availableVehicleCount === 0 && $settingsService->privateVehicleSlotsEnabled())
                        <option value="private_vehicle" data-tracking-mode="distance" data-distance-unit="{{ $settingsService->distanceUnit() }}" data-last-km="">
                            Private vehicle
                        </option>
                    @endif
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
                <div class="form-group" style="margin-bottom: 1.75rem;">
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
            <div class="form-group" style="margin-bottom: 1.75rem;">

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
            <div class="form-group" style="margin-top:28px;">
                <div class="form-label" style="margin-bottom:12px;">
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
                        margin-bottom:14px;
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
                        margin-bottom:14px;
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
                style="margin-top:24px;"
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

<div
    id="sf-mobile-handover-modal"
    class="sf-mobile-modal"
    role="dialog"
    aria-modal="true"
    aria-hidden="true"
    hidden
>
    <div class="sf-mobile-modal-backdrop" data-handover-close></div>
    <div class="sf-mobile-modal-card" role="document">
        <div class="sf-mobile-modal-header">
            <div class="sf-mobile-modal-title">Previous trip not ended</div>
            <button type="button" class="sf-sheet-close" data-handover-close aria-label="Close">
                <ion-icon name="close-outline"></ion-icon>
            </button>
        </div>

        <div class="hint-text" style="margin-bottom: 10px;">
            This vehicle already has an active trip. If you are taking it now, end the previous trip first.
        </div>

        <div class="hint-text"><strong>Vehicle:</strong> <span id="sfMobileHandoverVehicle">-</span></div>
        <div class="hint-text" style="margin-top:6px;"><strong>Previous driver:</strong> <span id="sfMobileHandoverDriver">-</span></div>
        <div class="hint-text" style="margin-top:6px;"><strong>Trip started:</strong> <span id="sfMobileHandoverStarted">-</span></div>
        <div class="hint-text" style="margin-top:6px;"><strong>Starting reading:</strong> <span id="sfMobileHandoverStartKm">-</span></div>

        <div class="alert alert-info" style="margin-top: 12px;">
            Make sure the previous trip is not still in progress before closing it.
        </div>

        <form id="sfMobileHandoverForm" style="margin-top: 12px;">
            <input type="hidden" name="trip_id" id="sfMobileHandoverTripId">

            <div class="form-group" style="margin-bottom: 12px;">
                <label class="form-label" id="sfMobileHandoverReadingLabel">Current odometer (km)</label>
                <input type="number" name="end_km" id="sfMobileHandoverEndKm" class="form-control" inputmode="numeric" required min="0" placeholder="e.g. 124800">
            </div>

            <label class="checkbox-label" style="margin-bottom: 12px;">
                <input type="checkbox" name="confirm_takeover" id="sfMobileHandoverConfirm" required>
                I confirm I am taking <strong id="sfMobileHandoverVehicleInline">this vehicle</strong>.
            </label>

            <div id="sfMobileHandoverError" class="alert alert-error" style="display:none; margin-bottom:12px;"></div>

            <div style="display:flex; gap:12px; justify-content:flex-end;">
                <button type="button" class="sf-mobile-secondary-btn" data-handover-close>Cancel</button>
                <button type="submit" class="sf-mobile-primary-btn">End Previous Trip</button>
            </div>
        </form>
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
            <div class="radio-group" style="margin-top: 14px; margin-bottom: 18px;">
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
            <div id="purposeOfTravelBlock" class="form-group" style="margin-top: 12px;">
                <label class="form-label">Purpose of Travel (optional)</label>
                <input
                    type="text"
                    name="purpose_of_travel"
                    class="form-control"
                    maxlength="255"
                    placeholder="e.g. Site visit, delivery, client meeting"
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
            <div id="customerBlock" class="form-group" style="margin-bottom: 20px;">
                <label class="form-label">{{ $partyLabel !== '' ? $partyLabel : 'Customer' }} (optional)</label>

                @if(($settings['customer']['allow_select'] ?? true) && $customers->count() > 0)
                    <select id="customerSelect" name="customer_id" class="form-control" form="startTripForm">
                        <option value="">- Select from list -</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                    <div class="hint-text" style="margin-top: 10px; margin-bottom: 18px;">
                        If the {{ $partyLabelLower }} isn't in the list, type a name below.
                    </div>
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
                <div class="form-group" style="margin-top: 18px;">
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
                    <div class="form-group" style="margin-top: 18px;">
                        <label class="form-label">{{ $clientPresenceLabel }} Address (for billing/job tracking)</label>

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
            <div class="hint-text" style="margin-bottom: 12px;">
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
    const COMPANY_DISTANCE_UNIT = @json($settingsService->distanceUnit());
    const COMPANY_TIMEZONE = @json($companyTimezone ?? 'UTC');

    const vehicleSelect = document.getElementById('vehicleSelect');
    if (!vehicleSelect) return;

    const safetyBanner = document.getElementById('sf-safety-banner');
    if (safetyBanner) {
        setTimeout(() => {
            safetyBanner.style.opacity = '0';
            safetyBanner.style.transform = 'translateY(-8px)';
            setTimeout(() => {
                safetyBanner.style.display = 'none';
            }, 260);
        }, 4000);
    }

    const vehicleSearchInput = document.getElementById('vehicleSearchInput');
    const vehicleSearchHint = document.getElementById('vehicleSearchHint');
    const startKmInput = document.getElementById('startKmInput');
    const lastKmHint = document.getElementById('lastKmHint');
    const startReadingLabel = document.getElementById('startReadingLabel');
    const handoverModal = document.getElementById('sf-mobile-handover-modal');
    const handoverForm = document.getElementById('sfMobileHandoverForm');
    const handoverTripId = document.getElementById('sfMobileHandoverTripId');
    const handoverEndKm = document.getElementById('sfMobileHandoverEndKm');
    const handoverConfirm = document.getElementById('sfMobileHandoverConfirm');
    const handoverError = document.getElementById('sfMobileHandoverError');
    const handoverVehicle = document.getElementById('sfMobileHandoverVehicle');
    const handoverVehicleInline = document.getElementById('sfMobileHandoverVehicleInline');
    const handoverDriver = document.getElementById('sfMobileHandoverDriver');
    const handoverStarted = document.getElementById('sfMobileHandoverStarted');
    const handoverStartKm = document.getElementById('sfMobileHandoverStartKm');
    const handoverReadingLabel = document.getElementById('sfMobileHandoverReadingLabel');

    let lastAutoFilledReading = null;
    let handoverRequired = false;
    let handoverTrip = null;
    let handoverCheckToken = 0;
    const globalStartTripState = window.sfStartTripState || { submitting: false };
    window.sfStartTripState = globalStartTripState;
    const globalHandoverState = {
        required: false,
        open: () => openHandoverModal(),
    };
    window.sfHandoverState = globalHandoverState;

    let allVehicleOptions = Array.from(vehicleSelect.options).map(opt => ({
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

    function setVehicleOptionsFromServer(items, includePrivateVehicleOption) {
        const mapped = items.map(v => ({
            value: String(v.id),
            text: `${v.name} (${v.registration_number})`,
            trackingMode: v.tracking_mode || 'distance',
            distanceUnit: v.distance_unit || 'km',
            lastKm: v.last_km || ''
        }));

        if (includePrivateVehicleOption) {
            mapped.unshift({
                value: 'private_vehicle',
                text: 'Private vehicle',
                trackingMode: 'distance',
                distanceUnit: COMPANY_DISTANCE_UNIT,
                lastKm: ''
            });
        }

        if (mapped.length === 0) {
            mapped.push({
                value: '',
                text: 'No vehicles available',
                trackingMode: 'distance',
                distanceUnit: 'km',
                lastKm: ''
            });
        }

        allVehicleOptions = mapped;
        rebuildVehicleOptions(allVehicleOptions);
    }

    async function refreshVehicleOptionsFromServer() {
        if (!navigator.onLine) return;
        if (!vehicleSelect) return;
        if (globalStartTripState.submitting) return;

        try {
            const res = await fetch('/app/sharpfleet/trips/available-vehicles', {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' },
                cache: 'no-store',
            });
            if (!res.ok) return;
            const data = await res.json();
            if (!data || !Array.isArray(data.vehicles)) return;
            setVehicleOptionsFromServer(data.vehicles, !!data.private_vehicle_option);
        } catch (e) {
            // ignore
        }
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

        if (selected.value === 'private_vehicle') {
            if (lastKmHint) {
                lastKmHint.classList.add('d-none');
                lastKmHint.textContent = '';
                lastKmHint.style.display = 'none';
            }
            if (startKmInput) {
                startKmInput.value = '';
            }
            lastAutoFilledReading = null;
            return;
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
                lastKmHint.style.display = '';
            }
        } else if (lastKmHint) {
            if (canAutofill) {
                startKmInput.value = '';
                lastAutoFilledReading = null;
            }
            lastKmHint.classList.add('d-none');
            lastKmHint.textContent = '';
            lastKmHint.style.display = 'none';
        }
    }

    function formatTripStart(iso, timezone) {
        if (!iso) return '-';
        const tz = timezone && String(timezone).trim() !== '' ? timezone : COMPANY_TIMEZONE;
        try {
            return new Date(iso).toLocaleString('en-AU', {
                timeZone: tz,
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: 'numeric',
                minute: '2-digit'
            });
        } catch (e) {
            return String(iso);
        }
    }

    function setHandoverRequired(required) {
        handoverRequired = required;
        globalHandoverState.required = required;
    }

    function openHandoverModal() {
        if (!handoverModal) return;
        handoverModal.classList.add('is-open');
        handoverModal.setAttribute('aria-hidden', 'false');
        handoverModal.hidden = false;
        document.body.style.overflow = 'hidden';
    }

    function closeHandoverModal(resetVehicle) {
        if (!handoverModal) return;
        handoverModal.classList.remove('is-open');
        handoverModal.setAttribute('aria-hidden', 'true');
        handoverModal.hidden = true;
        document.body.style.overflow = '';
        if (resetVehicle && vehicleSelect) {
            vehicleSelect.value = '';
            updateStartKm();
        }
    }

    function populateHandoverModal(trip) {
        if (!trip) return;
        const selected = vehicleSelect && vehicleSelect.options[vehicleSelect.selectedIndex];
        const vehicleLabel = selected ? selected.textContent : 'this vehicle';
        const trackingMode = selected?.dataset?.trackingMode || 'distance';
        const distanceUnit = selected?.dataset?.distanceUnit || COMPANY_DISTANCE_UNIT;

        if (handoverVehicle) handoverVehicle.textContent = vehicleLabel;
        if (handoverVehicleInline) handoverVehicleInline.textContent = vehicleLabel;
        if (handoverDriver) handoverDriver.textContent = trip.driver_name || 'Unknown';
        if (handoverStarted) handoverStarted.textContent = formatTripStart(trip.started_at, trip.timezone);
        if (handoverStartKm) {
            handoverStartKm.textContent = trip.start_km !== null ? Number(trip.start_km).toLocaleString() : 'Unknown';
        }

        if (handoverReadingLabel) {
            handoverReadingLabel.textContent = trackingMode === 'hours'
                ? 'Current hour meter (hours)'
                : `Current odometer (${distanceUnit})`;
        }

        if (handoverTripId) handoverTripId.value = String(trip.trip_id || '');
        if (handoverEndKm) {
            const minVal = trip.start_km !== null ? Number(trip.start_km) : 0;
            handoverEndKm.min = String(minVal);
            handoverEndKm.value = '';
        }
        if (handoverConfirm) handoverConfirm.checked = false;
        if (handoverError) {
            handoverError.style.display = 'none';
            handoverError.textContent = '';
        }
    }

    async function checkActiveTripForVehicle(vehicleId) {
        if (!vehicleId || vehicleId === 'private_vehicle') {
            setHandoverRequired(false);
            handoverTrip = null;
            return false;
        }
        if (!navigator.onLine) return;
        if (!handoverModal) return;
        if (globalStartTripState.submitting) return;

        const token = ++handoverCheckToken;
        try {
            const res = await fetch(`/app/sharpfleet/trips/active-for-vehicle?vehicle_id=${encodeURIComponent(vehicleId)}`, {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' },
                cache: 'no-store',
            });
            if (!res.ok) return;
            const data = await res.json();
            if (token !== handoverCheckToken) return;
            if (globalStartTripState.submitting) return;

            if (!data || !data.active) {
                handoverTrip = null;
                setHandoverRequired(false);
                vehicleSelect.disabled = false;
                return false;
            }

            handoverTrip = data.trip || null;
            setHandoverRequired(true);
            populateHandoverModal(handoverTrip);
            vehicleSelect.disabled = true;
            openHandoverModal();
            return true;
        } catch (e) {
            // ignore
            return false;
        }
    }

    window.sfCheckVehicleActiveForStart = checkActiveTripForVehicle;

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

        if (vehicleSelect) {
            vehicleSelect.required = true;
            vehicleSelect.disabled = false;
        }

        updateStartKm();

        if (customerBlock) customerBlock.style.display = isBusinessTrip ? '' : 'none';
        if (clientPresenceBlock) clientPresenceBlock.style.display = isBusinessTrip ? '' : 'none';
        if (purposeOfTravelBlock) purposeOfTravelBlock.style.display = isBusinessTrip ? '' : 'none';
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

    vehicleSelect.addEventListener('change', () => {
        updateStartKm();
        checkActiveTripForVehicle(vehicleSelect.value);
    });
    vehicleSelect.addEventListener('focus', refreshVehicleOptionsFromServer);
    vehicleSelect.addEventListener('click', refreshVehicleOptionsFromServer);
    if (vehicleSearchInput) {
        vehicleSearchInput.addEventListener('input', filterVehicles);
    }
    tripModeRadios.forEach(r => r.addEventListener('change', updateBusinessOnlyBlocksVisibility));
    document.querySelectorAll('[data-sheet-open="start-trip"]').forEach((trigger) => {
        trigger.addEventListener('click', refreshVehicleOptionsFromServer);
    });
    window.addEventListener('online', refreshVehicleOptionsFromServer);

    document.querySelectorAll('[data-status-target]').forEach(btn => {
        btn.addEventListener('click', () => {
            const targetId = btn.getAttribute('data-status-target');
            const target = targetId ? document.getElementById(targetId) : null;
            if (!target) return;
            if (window.sfSwapIcon) {
                window.sfSwapIcon(target, 'checkmark');
            } else {
                target.setAttribute('name', 'checkmark');
            }
            target.classList.add('sf-status-complete');
            target.classList.remove('sf-status-pulse');
            void target.offsetWidth;
            target.classList.add('sf-status-pulse');
        });
    });

    // Initial load
    refreshVehicleOptionsFromServer();

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
    document.querySelectorAll('[data-handover-close]').forEach(btn => {
        btn.addEventListener('click', () => {
            setHandoverRequired(false);
            handoverTrip = null;
            vehicleSelect.disabled = false;
            closeHandoverModal(true);
        });
    });

    if (handoverForm) {
        handoverForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            if (!handoverTrip || !handoverTrip.trip_id) {
                return;
            }

            if (!handoverConfirm || !handoverConfirm.checked) {
                if (handoverError) {
                    handoverError.textContent = 'Please confirm you are taking the vehicle.';
                    handoverError.style.display = '';
                }
                return;
            }

            const endKmVal = Number(handoverEndKm ? handoverEndKm.value : '');
            if (Number.isNaN(endKmVal)) {
                if (handoverError) {
                    handoverError.textContent = 'Enter a valid current reading.';
                    handoverError.style.display = '';
                }
                return;
            }
            if (handoverTrip.start_km !== null && endKmVal < Number(handoverTrip.start_km)) {
                if (handoverError) {
                    handoverError.textContent = 'Ending reading must be the same as or greater than the starting reading.';
                    handoverError.style.display = '';
                }
                return;
            }

            const formData = new FormData();
            formData.append('trip_id', String(handoverTrip.trip_id));
            formData.append('end_km', String(endKmVal));
            formData.append('confirm_takeover', '1');

            try {
                const res = await fetch('/app/sharpfleet/trips/end-handover', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]').getAttribute('content'),
                    },
                    body: formData,
                });

                if (!res.ok) {
                    let msg = 'Unable to close the trip. Please try again.';
                    try {
                        const data = await res.json();
                        if (data && data.message) msg = data.message;
                    } catch (e) {}
                    if (handoverError) {
                        handoverError.textContent = msg;
                        handoverError.style.display = '';
                    }
                    return;
                }

                handoverTrip = null;
                setHandoverRequired(false);
                vehicleSelect.disabled = false;
                closeHandoverModal(false);
                refreshVehicleOptionsFromServer();
            } catch (e) {
                if (handoverError) {
                    handoverError.textContent = 'Network error. Please try again.';
                    handoverError.style.display = '';
                }
            }
        });
    }

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
