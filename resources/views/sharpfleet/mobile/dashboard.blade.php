@extends('sharpfleet.mobile.layouts.app')

@section('title', 'Driver')

@section('content')
<section class="sf-mobile-dashboard">

    {{-- ===============================
         Greeting / Identity
    ================================ --}}
    @php
        $driverFirstName = trim((string) ($user['first_name'] ?? ''));
        $organisationName = trim((string) ($organisationName ?? ''));
    @endphp

    <div style="margin-bottom: 16px;">
        <h1 class="sf-mobile-title">
            Hi {{ $driverFirstName !== '' ? $driverFirstName : 'Driver' }}<span style="font-size:0.65em; vertical-align:middle;"> 👋</span>
        </h1>

        <div class="sf-mobile-subtitle">
            {{ $organisationName !== '' ? $organisationName : 'Organisation' }}
        </div>
    </div>

    <div id="offlineTripAlert" class="sf-mobile-card" style="display:none; margin-bottom: 20px;"></div>
    <div id="supportSentAlert" class="sf-mobile-card" style="display:none; margin-bottom: 20px;"></div>
{{-- ===============================
         Drive Status
    ================================ --}}
    @if($activeTrip)
        <div id="activeTripCard" class="sf-mobile-card sf-drive-active" style="margin-bottom: 20px; position:relative;">
            <div class="sf-mobile-card-title" style="color: #EAF7F4;">Drive in Progress</div>

            <div class="hint-text" style="margin-top: 8px;">
                <strong>Vehicle:</strong>
                @if($activeTrip->vehicle_name)
                    {{ $activeTrip->vehicle_name }} ({{ $activeTrip->registration_number }})
                @else
                    Private vehicle
                @endif
            </div>

            <div class="hint-text" style="margin-top: 6px;">
                @php
                    $tripTz = isset($activeTrip->timezone) && trim((string) $activeTrip->timezone) !== ''
                        ? (string) $activeTrip->timezone
                        : $companyTimezone;
                @endphp
                <strong>Started:</strong>
                {{ \Carbon\Carbon::parse($activeTrip->started_at)->timezone($tripTz)->format('M j, Y g:i A') }}
            </div>

            <div class="hint-text" style="margin-top: 6px;">
                @php
                    $activeTripBranchId = isset($activeTrip->vehicle_branch_id)
                        ? (int) ($activeTrip->vehicle_branch_id ?? 0)
                        : 0;
                    $activeTripDistanceUnit = $settingsService->distanceUnitForBranch(
                        $activeTripBranchId > 0 ? $activeTripBranchId : null
                    );
                @endphp
                <strong>
                    {{ ($activeTrip->tracking_mode ?? 'distance') === 'hours'
                        ? 'Starting Hours:'
                        : ('Starting ' . strtoupper($activeTripDistanceUnit) . ':')
                    }}
                </strong>
                {{ number_format($activeTrip->start_km) }}
            </div>

            <button type="button"
                    title="Refresh"
                    aria-label="Refresh"
                    onclick="window.location.reload()"
                    style="position:absolute; right:12px; bottom:12px; background:transparent; border:0; color:#2CBFAE; font-size:18px; line-height:1; cursor:pointer;">
                ↻
            </button>
        </div>

        <button
            id="endDriveBtn"
            type="button"
            class="sf-mobile-primary-btn"
            data-sheet-open="end-trip"
            style="margin-bottom: 20px;"
        >
            End Drive
        </button>
    @else
        <button
            id="noActiveTripCard"
            type="button"
            class="sf-mobile-card"
            data-sheet-open="start-trip"
            style="margin-bottom: 20px; text-align: left; width: 100%; color: #EAF7F4;"
        >
            <div class="sf-mobile-card-title" style="color: #EAF7F4;">                
                No Active Trip
            </div>

            <div class="hint-text" style="margin-top: 6px;">
                Ready when you are.
            </div>
            <div class="hint-text" style="margin-top: 6px;">
                Tap to start a trip.
            </div>
        </button>


    @endif

    <div id="offlineActiveTripCard" class="sf-mobile-card" style="margin-bottom: 20px; display:none;">
        <div class="sf-mobile-card-title" style="color: #EAF7F4;">Trip in Progress (Offline)</div>
        <div class="hint-text" style="margin-top: 6px;">
            <strong>Vehicle:</strong> <span id="offlineTripVehicle">-</span>
        </div>
        <div class="hint-text" style="margin-top: 6px;">
            <strong>Started:</strong> <span id="offlineTripStarted">-</span>
        </div>
        <div class="hint-text" style="margin-top: 6px;">
            <strong>Starting reading:</strong> <span id="offlineTripStartKm">-</span>
        </div>
    </div>

    <button
        id="offlineEndDriveBtn"
        type="button"
        class="sf-mobile-primary-btn"
        data-sheet-open="end-trip"
        style="margin-bottom: 20px; display:none;"
    >
        End Drive
    </button>

    @if(($settings['vehicles']['fuel_receipts_enabled'] ?? false) === true)
        <button
            type="button"
            class="sf-mobile-card sf-card-accent"
            data-sheet-open="fuel-entry"
            style="text-align: left; width: 100%; margin-bottom: 8px;"
        >
            <div class="sf-mobile-card-title" style="color: #EAF7F4;">⛽ Add Fuel</div>
            <div class="sf-mobile-card-text" style="color: #EAF7F4;">Log a fuel receipt for a vehicle.</div>
        </button>
    @endif

    {{-- ===============================
         Trip Requirements
    ================================ --}}
    @php
        $clientPresenceLabel = trim((string) ($settings['client_presence']['label'] ?? 'Client'));
        $clientPresenceLabel = $clientPresenceLabel !== '' ? $clientPresenceLabel : 'Client';
    @endphp
    <div class="sf-mobile-card sf-card-accent" style="margin-top: 12px; margin-bottom: 20px;">
        <div class="sf-mobile-card-title" style="color: #EAF7F4;">
            Before each trip, your company requires:
        </div>

        <ul class="hint-text" style="margin: 8px 0 0 16px;">
            <li>Vehicle selection</li>
            <li>Starting odometer</li>
            @if($safetyCheckEnabled)
                <li>Safety check</li>
            @endif
            @if(($settings['client_presence']['enabled'] ?? false) === true)
                <li>{{ $clientPresenceLabel }} presence (when applicable)</li>
            @endif
        </ul>
    </div>

    {{-- ===============================
         Secondary Action
    ================================ --}}
    @if($faultsEnabled)
        <button
            class="sf-mobile-secondary-btn sf-card-accent"
            type="button"
            data-sheet-open="report-fault"
            style="margin-top: 12px;"
        >
            Report Vehicle Issue
        </button>
    @endif

</section>

{{-- Start Trip Sheet --}}
@include('sharpfleet.mobile.sheets.start-trip')

{{-- End Trip Sheet --}}
@include('sharpfleet.mobile.sheets.end-trip')

{{-- Report Fault Sheet --}}
@if($faultsEnabled)
    @include('sharpfleet.mobile.sheets.report-fault')
@endif

{{-- Fuel Entry Sheet --}}
@if(($settings['vehicles']['fuel_receipts_enabled'] ?? false) === true)
    @include('sharpfleet.mobile.sheets.fuel-entry')
@endif

@php
    $serverActiveTripPayload = null;
    if ($activeTrip) {
        $activeTripBranchId = isset($activeTrip->vehicle_branch_id)
            ? (int) ($activeTrip->vehicle_branch_id ?? 0)
            : 0;
        $activeTripDistanceUnit = $settingsService->distanceUnitForBranch(
            $activeTripBranchId > 0 ? $activeTripBranchId : null
        );
        $serverActiveTripPayload = [
            'trip_id' => (int) $activeTrip->id,
            'vehicle_id' => $activeTrip->vehicle_id ? (int) $activeTrip->vehicle_id : null,
            'vehicle_text' => $activeTrip->vehicle_name
                ? trim(($activeTrip->vehicle_name ?? '') . ' (' . ($activeTrip->registration_number ?? '') . ')')
                : 'Private vehicle',
            'started_at' => $activeTrip->started_at ?? null,
            'start_km' => isset($activeTrip->start_km) ? (int) $activeTrip->start_km : null,
            'tracking_mode' => $activeTrip->tracking_mode ?? 'distance',
            'distance_unit' => $activeTripDistanceUnit,
            'trip_mode' => $activeTrip->trip_mode ?? 'business',
            'private_vehicle' => (int) ($activeTrip->is_private_vehicle ?? 0),
            'customer_id' => $activeTrip->customer_id ?? null,
            'customer_name' => $activeTrip->customer_name ?? null,
            'client_present' => $activeTrip->client_present ?? null,
            'client_address' => $activeTrip->client_address ?? null,
            'purpose_of_travel' => $activeTrip->purpose_of_travel ?? null,
            'timezone' => $activeTrip->timezone ?? null,
        ];
    }
@endphp

<script>
(() => {
    const COMPANY_TIMEZONE = @json($companyTimezone ?? 'UTC');
    const COMPANY_DISTANCE_UNIT = @json($settingsService->distanceUnit());
    const MANUAL_TRIP_TIMES_REQUIRED = @json((bool) $manualTripTimesRequired);
    const ODOMETER_REQUIRED = @json((bool) $odometerRequired);
    const CLIENT_PRESENCE_REQUIRED = @json((bool) ($settings['client_presence']['required'] ?? false));
    const CLIENT_PRESENCE_LABEL = @json((string) ($settings['client_presence']['label'] ?? 'Client'));
    const SERVER_ACTIVE_TRIP = @json($serverActiveTripPayload);

    const OFFLINE_ACTIVE_KEY = 'sharpfleet_offline_active_trip_v1';
    const OFFLINE_COMPLETED_KEY = 'sharpfleet_offline_completed_trips_v1';
    const OFFLINE_END_UPDATES_KEY = 'sharpfleet_offline_end_updates_v1';

    const offlineTripAlert = document.getElementById('offlineTripAlert');
    const activeTripCard = document.getElementById('activeTripCard');
    const noActiveTripCard = document.getElementById('noActiveTripCard');
    const offlineActiveTripCard = document.getElementById('offlineActiveTripCard');
    const offlineEndDriveBtn = document.getElementById('offlineEndDriveBtn');
    const endReadingLabel = document.getElementById('endReadingLabel');
    const endKmInput = document.getElementById('endKmInput');
    const startTripForm = document.getElementById('startTripForm');
    const endTripForm = document.getElementById('endTripForm');
    const vehicleSelect = document.getElementById('vehicleSelect');

    if (endTripForm) {
        endTripForm.addEventListener('submit', (e) => {
            if (!navigator.onLine && typeof window.sfHandleOfflineTripSubmit === 'function') {
                e.preventDefault();
                window.sfHandleOfflineTripSubmit(endTripForm, new FormData(endTripForm));
            }
        });
    }

    function showOfflineMessage(msg) {
        if (!offlineTripAlert) return;
        offlineTripAlert.textContent = msg;
        offlineTripAlert.style.display = '';
    }

    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function getLocalJson(key, fallback) {
        try {
            const raw = localStorage.getItem(key);
            return raw ? JSON.parse(raw) : fallback;
        } catch (e) {
            return fallback;
        }
    }

    function setLocalJson(key, value) {
        localStorage.setItem(key, JSON.stringify(value));
    }

    function getOfflineActiveTrip() {
        return getLocalJson(OFFLINE_ACTIVE_KEY, null);
    }

    function setOfflineActiveTrip(trip) {
        if (trip === null) {
            localStorage.removeItem(OFFLINE_ACTIVE_KEY);
            return;
        }
        setLocalJson(OFFLINE_ACTIVE_KEY, trip);
    }

    function getOfflineCompletedTrips() {
        return getLocalJson(OFFLINE_COMPLETED_KEY, []);
    }

    function setOfflineCompletedTrips(trips) {
        setLocalJson(OFFLINE_COMPLETED_KEY, trips);
    }

    function getOfflineEndUpdates() {
        return getLocalJson(OFFLINE_END_UPDATES_KEY, []);
    }

    function setOfflineEndUpdates(updates) {
        setLocalJson(OFFLINE_END_UPDATES_KEY, updates);
    }

    function seedServerActiveTrip() {
        const existing = getOfflineActiveTrip();
        if (!SERVER_ACTIVE_TRIP) {
            if (existing && existing.source === 'server') {
                setOfflineActiveTrip(null);
            }
            return;
        }
        if (existing) return;
        setOfflineActiveTrip({
            ...SERVER_ACTIVE_TRIP,
            source: 'server',
        });
    }

    function applyEndTripUiFromActive(active) {
        if (!active || !endReadingLabel || !endKmInput) return;
        const trackingMode = active.tracking_mode || 'distance';
        const distanceUnit = active.distance_unit || COMPANY_DISTANCE_UNIT;
        endReadingLabel.textContent = trackingMode === 'hours'
            ? 'Ending hour meter (hours)'
            : `Ending odometer (${distanceUnit})`;

        const minVal = active.start_km !== null && active.start_km !== undefined
            ? Number(active.start_km)
            : 0;
        endKmInput.min = Number.isNaN(minVal) ? '0' : String(minVal);
    }

    function renderOfflineActiveTrip() {
        if (!offlineActiveTripCard) return;
        const t = getOfflineActiveTrip();

        if (!t) {
            offlineActiveTripCard.style.display = 'none';
            if (offlineEndDriveBtn) offlineEndDriveBtn.style.display = 'none';
            if (activeTripCard) activeTripCard.style.display = '';
            if (noActiveTripCard) noActiveTripCard.style.display = '';
            return;
        }

        if (t.source === 'server' && navigator.onLine) {
            offlineActiveTripCard.style.display = 'none';
            if (offlineEndDriveBtn) offlineEndDriveBtn.style.display = 'none';
            if (activeTripCard) activeTripCard.style.display = '';
            if (noActiveTripCard) noActiveTripCard.style.display = 'none';
            return;
        }

        offlineActiveTripCard.style.display = '';
        if (offlineEndDriveBtn) offlineEndDriveBtn.style.display = '';
        if (activeTripCard) activeTripCard.style.display = 'none';
        if (noActiveTripCard) noActiveTripCard.style.display = 'none';

        const v = document.getElementById('offlineTripVehicle');
        const s = document.getElementById('offlineTripStarted');
        const skm = document.getElementById('offlineTripStartKm');

        if (v) v.textContent = t.vehicle_text || '-';
        if (t.private_vehicle && v && (!t.vehicle_text || t.vehicle_text === '-' || t.vehicle_text === '—')) {
            v.textContent = 'Private vehicle';
        }
        if (s) {
            try {
                const tz = t.timezone && String(t.timezone).trim() !== '' ? t.timezone : COMPANY_TIMEZONE;
                s.textContent = new Date(t.started_at).toLocaleString(undefined, { timeZone: tz });
            } catch (e) {
                try { s.textContent = new Date(t.started_at).toLocaleString(); } catch (e2) { s.textContent = t.started_at; }
            }
        }
        if (skm) skm.textContent = String(t.start_km ?? '-');

        applyEndTripUiFromActive(t);
    }

    async function syncOfflineTripsIfPossible() {
        if (!navigator.onLine) return;
        const completed = getOfflineCompletedTrips();
        if (!Array.isArray(completed) || completed.length === 0) return;

        try {
            const res = await fetch('/app/sharpfleet/trips/offline-sync', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify({ trips: completed }),
            });

            if (!res.ok) {
                let msg = 'Could not sync offline trips yet.';
                try {
                    const data = await res.json();
                    if (data && data.message) msg = data.message;
                    if (data && data.errors) {
                        const keys = Object.keys(data.errors);
                        if (keys.length && Array.isArray(data.errors[keys[0]]) && data.errors[keys[0]][0]) {
                            msg = data.errors[keys[0]][0];
                        }
                    }
                } catch (e) {}
                showOfflineMessage(msg);
                return;
            }

            const data = await res.json();
            setOfflineCompletedTrips([]);
            showOfflineMessage(`Offline trips synced (${(data.synced || []).length} sent).`);
            setTimeout(() => window.location.reload(), 800);
        } catch (e) {
            // ignore network errors
        }
    }

    async function syncOfflineEndUpdatesIfPossible() {
        if (!navigator.onLine) return;
        const updates = getOfflineEndUpdates();
        if (!Array.isArray(updates) || updates.length === 0) return;

        const remaining = [];
        let syncedCount = 0;

        for (const update of updates) {
            try {
                const formData = new FormData();
                formData.append('trip_id', update.trip_id);
                formData.append('end_km', update.end_km);
                if (update.ended_at) {
                    formData.append('ended_at', update.ended_at);
                }

                const res = await fetch('/app/sharpfleet/trips/end', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'text/html',
                    },
                    body: formData,
                });

                if (!res.ok) {
                    remaining.push(update);
                } else {
                    syncedCount += 1;
                }
            } catch (e) {
                remaining.push(update);
            }
        }

        setOfflineEndUpdates(remaining);
        if (syncedCount > 0) {
            showOfflineMessage(`Offline trip endings synced (${syncedCount} sent).`);
            setTimeout(() => window.location.reload(), 800);
        }
    }

    function closeSheetById(id) {
        const sheet = document.getElementById('sf-sheet-' + id);
        if (!sheet) return;
        sheet.classList.remove('is-open');
        sheet.setAttribute('aria-hidden', 'true');
        sheet.hidden = true;
        const backdrop = document.getElementById('sf-sheet-backdrop');
        if (backdrop) backdrop.style.display = 'none';
        document.body.style.overflow = '';
    }

    function buildOfflineStartPayload(fd) {
        const payload = {};
        for (const [key, value] of fd.entries()) {
            if (!(key in payload)) payload[key] = value;
        }

        const mode = payload.trip_mode ? String(payload.trip_mode) : 'business';
        const isPrivateVehicle = payload.vehicle_id === 'private_vehicle';
        const startKmRaw = payload.start_km !== undefined ? String(payload.start_km) : '';
        const hasStartKm = startKmRaw.trim() !== '';
        const startKmVal = hasStartKm ? Number(startKmRaw) : null;

        let trackingMode = 'distance';
        let distanceUnit = COMPANY_DISTANCE_UNIT;
        let vehicleText = payload.vehicle_id ? String(payload.vehicle_id) : '';

        if (vehicleSelect) {
            const selected = vehicleSelect.options[vehicleSelect.selectedIndex];
            if (selected) {
                trackingMode = selected.dataset.trackingMode || trackingMode;
                distanceUnit = selected.dataset.distanceUnit || distanceUnit;
                vehicleText = selected.textContent || vehicleText;
            }
        }

        return {
            vehicle_id: isPrivateVehicle ? null : Number(payload.vehicle_id),
            private_vehicle: isPrivateVehicle ? 1 : 0,
            trip_mode: mode,
            start_km: startKmVal,
            started_at: payload.started_at ? String(payload.started_at) : null,
            customer_id: payload.customer_id ? Number(payload.customer_id) : null,
            customer_name: payload.customer_name ? String(payload.customer_name) : null,
            client_present: payload.client_present !== undefined && payload.client_present !== '' ? payload.client_present : null,
            client_address: payload.client_address ? String(payload.client_address) : null,
            purpose_of_travel: payload.purpose_of_travel ? String(payload.purpose_of_travel) : null,
            vehicle_text: isPrivateVehicle ? 'Private vehicle' : vehicleText,
            tracking_mode: trackingMode,
            distance_unit: distanceUnit,
        };
    }

    window.sfHandleOfflineTripSubmit = async function (form, formData, opts = {}) {
        const isStart = form && form.id === 'startTripForm';
        const isEnd = form && form.id === 'endTripForm';
        if (!isStart && !isEnd) return false;

        const offline = !navigator.onLine;
        const active = isEnd ? getOfflineActiveTrip() : null;
        const allowEndWhileOnline = !!(active && active.source === 'offline');
        if (!offline && !opts.force && !allowEndWhileOnline) return false;

        if (isStart) {
            if (getOfflineActiveTrip()) {
                showOfflineMessage('A trip is already in progress offline. End it before starting another.');
                renderOfflineActiveTrip();
                return true;
            }

            const payload = buildOfflineStartPayload(formData);
            if (!payload.private_vehicle && (!payload.vehicle_id || Number.isNaN(payload.vehicle_id))) {
                showOfflineMessage('Select a vehicle before starting.');
                return true;
            }
            if (ODOMETER_REQUIRED) {
                if (payload.start_km === null || Number.isNaN(payload.start_km)) {
                    showOfflineMessage('Enter a valid starting reading.');
                    return true;
                }
            } else if (payload.start_km !== null && Number.isNaN(payload.start_km)) {
                showOfflineMessage('Enter a valid starting reading.');
                return true;
            }
            if (MANUAL_TRIP_TIMES_REQUIRED && (!payload.started_at || String(payload.started_at).trim() === '')) {
                showOfflineMessage('Enter a start time before starting.');
                return true;
            }

            if (CLIENT_PRESENCE_REQUIRED) {
                const present = payload.client_present;
                if (present === null || present === '') {
                    showOfflineMessage(`Select whether a ${CLIENT_PRESENCE_LABEL} was present.`);
                    return true;
                }
                if (String(present) === '1') {
                    const hasClient = payload.customer_id || (payload.customer_name && String(payload.customer_name).trim() !== '');
                    if (!hasClient) {
                        showOfflineMessage(`Enter a ${CLIENT_PRESENCE_LABEL} name before starting.`);
                        return true;
                    }
                }
            }

            let startedAtIso = new Date().toISOString();
            if (MANUAL_TRIP_TIMES_REQUIRED && payload.started_at) {
                const dt = new Date(String(payload.started_at));
                if (Number.isNaN(dt.getTime())) {
                    showOfflineMessage('Enter a valid start time before starting.');
                    return true;
                }
                startedAtIso = dt.toISOString();
            }

            setOfflineActiveTrip({
                ...payload,
                started_at: startedAtIso,
                source: 'offline',
            });

            showOfflineMessage('No signal: trip started offline. End it to sync later.');
            renderOfflineActiveTrip();
            closeSheetById('start-trip');
            return true;
        }

        if (isEnd) {
            if (!active) {
                showOfflineMessage('No offline trip found.');
                renderOfflineActiveTrip();
                return true;
            }

            const endKmRaw = formData.get('end_km');
            const endKmVal = endKmRaw === null || String(endKmRaw).trim() === '' ? Number.NaN : Number(endKmRaw);
            if (Number.isNaN(endKmVal)) {
                showOfflineMessage('Enter a valid ending reading.');
                return true;
            }

            const minVal = active.start_km !== null && active.start_km !== undefined ? Number(active.start_km) : 0;
            if (endKmVal < minVal) {
                showOfflineMessage('Ending reading must be the same as or greater than the starting reading.');
                return true;
            }

            const endedAtRaw = formData.get('ended_at');
            if (MANUAL_TRIP_TIMES_REQUIRED && (!endedAtRaw || String(endedAtRaw).trim() === '')) {
                showOfflineMessage('Enter an end time before ending.');
                return true;
            }

            if (active.source === 'server' && active.trip_id) {
                const updates = getOfflineEndUpdates();
                updates.push({
                    trip_id: active.trip_id,
                    end_km: endKmVal,
                    ended_at: MANUAL_TRIP_TIMES_REQUIRED ? String(endedAtRaw || '') : new Date().toISOString(),
                });
                setOfflineEndUpdates(updates);
                setOfflineActiveTrip(null);

                showOfflineMessage('Trip ended offline. Will sync when signal returns.');
                renderOfflineActiveTrip();
                closeSheetById('end-trip');
                return true;
            }

            let endedAtIso = new Date().toISOString();
            if (MANUAL_TRIP_TIMES_REQUIRED && endedAtRaw) {
                const dt = new Date(String(endedAtRaw));
                if (Number.isNaN(dt.getTime())) {
                    showOfflineMessage('Enter a valid end time.');
                    return true;
                }
                endedAtIso = dt.toISOString();
            }

            const completed = getOfflineCompletedTrips();
            completed.push({
                vehicle_id: active.vehicle_id,
                private_vehicle: active.private_vehicle ? 1 : 0,
                vehicle_text: active.vehicle_text || null,
                tracking_mode: active.tracking_mode || 'distance',
                distance_unit: active.distance_unit || COMPANY_DISTANCE_UNIT,
                trip_mode: active.trip_mode,
                start_km: active.start_km,
                end_km: endKmVal,
                started_at: active.started_at,
                ended_at: endedAtIso,
                customer_id: active.customer_id,
                customer_name: active.customer_name,
                client_present: active.client_present,
                client_address: active.client_address,
                purpose_of_travel: active.purpose_of_travel,
            });
            setOfflineCompletedTrips(completed);
            setOfflineActiveTrip(null);

            showOfflineMessage('Trip ended offline. Will sync when signal returns.');
            renderOfflineActiveTrip();
            closeSheetById('end-trip');
            if (navigator.onLine) {
                await syncOfflineTripsIfPossible();
            }
            return true;
        }

        return false;
    };

    function showQueueSummary() {
        const completed = getOfflineCompletedTrips();
        const endUpdates = getOfflineEndUpdates();
        const total = (Array.isArray(completed) ? completed.length : 0) + (Array.isArray(endUpdates) ? endUpdates.length : 0);
        if (total > 0) {
            showOfflineMessage(`Offline trips queued for sync: ${total}.`);
        }
    }

    seedServerActiveTrip();
    renderOfflineActiveTrip();
    showQueueSummary();
    syncOfflineTripsIfPossible();
    syncOfflineEndUpdatesIfPossible();

    window.addEventListener('online', () => {
        showOfflineMessage('Back online. Syncing offline trips...');
        syncOfflineTripsIfPossible();
        syncOfflineEndUpdatesIfPossible();
        renderOfflineActiveTrip();
    });

    window.addEventListener('offline', renderOfflineActiveTrip);
})();
</script>

@endsection



