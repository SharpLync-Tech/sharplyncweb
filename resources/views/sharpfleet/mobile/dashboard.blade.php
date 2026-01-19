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

    {{-- ===============================
         Drive Status
    ================================ --}}
    @if($activeTrip)
        <div class="sf-mobile-card sf-drive-active" style="margin-bottom: 20px;">
            <div class="sf-mobile-card-title">Drive in Progress</div>

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
        </div>

        <button
            type="button"
            class="sf-mobile-primary-btn"
            data-sheet-open="end-trip"
            style="margin-bottom: 20px;"
        >
            End Drive
        </button>
    @else
        <div id="noActiveTripCard" class="sf-mobile-card" style="margin-bottom: 20px;">
            <div class="sf-mobile-card-title">No Active Trip</div>
            <div class="hint-text" style="margin-top: 6px;">
                Ready when you are.
            </div>
            <div class="hint-text" style="margin-top: 6px;">
                Tap the Start icon in the footer to begin a trip.
            </div>
        </div>

    <div id="offlineTripAlert" class="sf-mobile-card" style="display:none; margin-bottom: 20px;"></div>
    <div id="supportSentAlert" class="sf-mobile-card" style="display:none; margin-bottom: 20px;"></div>
    @endif

    <div id="offlineActiveTripCard" class="sf-mobile-card" style="margin-bottom: 20px; display:none;">
        <div class="sf-mobile-card-title">Trip in Progress (Offline)</div>
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

    {{-- ===============================
         Trip Requirements
    ================================ --}}
    @php
        $clientPresenceLabel = trim((string) ($settings['client_presence']['label'] ?? 'Client'));
        $clientPresenceLabel = $clientPresenceLabel !== '' ? $clientPresenceLabel : 'Client';
    @endphp
    <div class="sf-mobile-card" style="margin-bottom: 20px;">
        <div class="sf-mobile-card-title">
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
            class="sf-mobile-secondary-btn"
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

@php
    $serverActiveTripPayload = $activeTrip ? [
        'trip_id' => (int) $activeTrip->id,
        'vehicle_id' => $activeTrip->vehicle_id ? (int) $activeTrip->vehicle_id : null,
        'vehicle_text' => $activeTrip->vehicle_name
            ? trim(($activeTrip->vehicle_name ?? '') . ' (' . ($activeTrip->registration_number ?? '') . ')')
            : 'Private vehicle',
        'started_at' => $activeTrip->started_at ?? null,
        'start_km' => isset($activeTrip->start_km) ? (int) $activeTrip->start_km : null,
        'trip_mode' => $activeTrip->trip_mode ?? 'business',
        'private_vehicle' => (int) ($activeTrip->is_private_vehicle ?? 0),
        'customer_id' => $activeTrip->customer_id ?? null,
        'customer_name' => $activeTrip->customer_name ?? null,
        'client_present' => $activeTrip->client_present ?? null,
        'client_address' => $activeTrip->client_address ?? null,
        'purpose_of_travel' => $activeTrip->purpose_of_travel ?? null,
    ] : null;
@endphp

<script>
    (function () {
        const MANUAL_TRIP_TIMES_REQUIRED = @json((bool) $manualTripTimesRequired);
        const COMPANY_TIMEZONE = @json($companyTimezone ?? 'UTC');

        const offlineTripAlert = document.getElementById('offlineTripAlert');
        const offlineActiveTripCard = document.getElementById('offlineActiveTripCard');
        const offlineEndDriveBtn = document.getElementById('offlineEndDriveBtn');
        const noActiveTripCard = document.getElementById('noActiveTripCard');
        const supportSentAlert = document.getElementById('supportSentAlert');

        const startTripForm = document.getElementById('startTripForm');
        const endTripForm = document.getElementById('endTripForm');
        const reportFaultForm = document.getElementById('reportFaultForm');

        const OFFLINE_ACTIVE_KEY = 'sharpfleet_offline_active_trip_v1';
        const OFFLINE_COMPLETED_KEY = 'sharpfleet_offline_completed_trips_v1';
        const OFFLINE_FAULTS_KEY = 'sharpfleet_offline_fault_reports_v1';
        const OFFLINE_END_UPDATES_KEY = 'sharpfleet_offline_end_updates_v1';

        const SERVER_ACTIVE_TRIP = @json($serverActiveTripPayload);

        function showOfflineMessage(msg) {
            if (!offlineTripAlert) return;
            offlineTripAlert.textContent = msg;
            offlineTripAlert.style.display = '';
        }

        function hideOfflineMessage() {
            if (!offlineTripAlert) return;
            offlineTripAlert.textContent = '';
            offlineTripAlert.style.display = 'none';
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

        function getOfflineFaultReports() {
            return getLocalJson(OFFLINE_FAULTS_KEY, []);
        }

        function setOfflineFaultReports(reports) {
            setLocalJson(OFFLINE_FAULTS_KEY, reports);
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

        function renderOfflineActiveTrip() {
            if (!offlineActiveTripCard || !offlineEndDriveBtn) return;
            const t = getOfflineActiveTrip();
            if (!t) {
                offlineActiveTripCard.style.display = 'none';
                offlineEndDriveBtn.style.display = 'none';
                if (noActiveTripCard) noActiveTripCard.style.display = '';
                return;
            }

            if (t.source === 'server' && navigator.onLine) {
                offlineActiveTripCard.style.display = 'none';
                offlineEndDriveBtn.style.display = 'none';
                return;
            }

            offlineActiveTripCard.style.display = '';
            offlineEndDriveBtn.style.display = '';
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
                    s.textContent = new Date(t.started_at).toLocaleString(undefined, { timeZone: COMPANY_TIMEZONE });
                } catch (e) {
                    try { s.textContent = new Date(t.started_at).toLocaleString(); } catch (e2) { s.textContent = t.started_at; }
                }
            }
            if (skm) skm.textContent = String(t.start_km ?? '-');
        }

        function closeSheets() {
            document.querySelectorAll('.sf-sheet.is-open').forEach(sheet => {
                sheet.classList.remove('is-open');
                sheet.setAttribute('aria-hidden', 'true');
            });

            const backdrop = document.getElementById('sf-sheet-backdrop');
            if (backdrop) backdrop.style.display = 'none';
            document.body.style.overflow = '';
        }

        function getCsrfToken() {
            const meta = document.querySelector('meta[name="csrf-token"]');
            return meta ? meta.getAttribute('content') : '';
        }

        async function submitFormOnline(form) {
            const controller = new AbortController();
            const timeout = setTimeout(() => controller.abort(), 8000);

            try {
                const res = await fetch(form.action, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'text/html',
                    },
                    body: new FormData(form),
                    signal: controller.signal,
                });

                clearTimeout(timeout);

                if (res.ok) {
                    const target = res.url || window.location.href;
                    const separator = target.includes('?') ? '&' : '?';
                    window.location.replace(`${target}${separator}refresh=${Date.now()}`);
                    return { ok: true };
                }

                return { ok: false, networkError: false };
            } catch (e) {
                clearTimeout(timeout);
                return { ok: false, networkError: true };
            }
        }

        async function syncOfflineFaultReportsIfPossible() {
            if (!navigator.onLine) return;
            const reports = getOfflineFaultReports();
            if (!Array.isArray(reports) || reports.length === 0) return;

            const remaining = [];
            let syncedCount = 0;

            for (const report of reports) {
                try {
                    const formData = new FormData();
                    Object.keys(report).forEach((key) => {
                        if (report[key] !== null && report[key] !== undefined) {
                            formData.append(key, report[key]);
                        }
                    });

                    const res = await fetch('/app/sharpfleet/faults/standalone', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'X-CSRF-TOKEN': getCsrfToken(),
                        },
                        body: formData,
                    });

                    if (!res.ok) {
                        remaining.push(report);
                    } else {
                        syncedCount += 1;
                    }
                } catch (e) {
                    remaining.push(report);
                }
            }

            setOfflineFaultReports(remaining);
            if (syncedCount > 0) {
                showOfflineMessage(`Offline fault reports synced (${syncedCount} sent).`);
            }
        }

        async function syncOfflineTripsIfPossible() {
            if (!navigator.onLine) return;
            const completed = getOfflineCompletedTrips();
            if (!Array.isArray(completed) || completed.length === 0) {
                hideOfflineMessage();
                return;
            }

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
                    } catch (e) {}
                    showOfflineMessage(msg);
                    return;
                }

                const data = await res.json();
                setOfflineCompletedTrips([]);
                showOfflineMessage(`Offline trips synced (${(data.synced || []).length} sent).`);
                setTimeout(() => hideOfflineMessage(), 1800);
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

        function buildOfflineStartPayload(form) {
            const fd = new FormData(form);
            const payload = Object.fromEntries(fd.entries());

            const mode = payload.trip_mode ? String(payload.trip_mode) : 'business';
            const isPrivateVehicle = payload.vehicle_id === 'private_vehicle';
            return {
                vehicle_id: isPrivateVehicle ? null : Number(payload.vehicle_id),
                private_vehicle: isPrivateVehicle ? 1 : 0,
                trip_mode: mode,
                start_km: Number(payload.start_km),
                started_at: payload.started_at ? String(payload.started_at) : null,
                customer_id: payload.customer_id ? Number(payload.customer_id) : null,
                customer_name: payload.customer_name ? String(payload.customer_name) : null,
                client_present: payload.client_present !== undefined && payload.client_present !== '' ? payload.client_present : null,
                client_address: payload.client_address ? String(payload.client_address) : null,
                purpose_of_travel: payload.purpose_of_travel ? String(payload.purpose_of_travel) : null,
            };
        }

        if (startTripForm) {
            startTripForm.addEventListener('submit', async (e) => {
                if (!startTripForm.checkValidity()) {
                    return;
                }

                if (window.sfHandoverState && window.sfHandoverState.required) {
                    e.preventDefault();
                    if (typeof window.sfHandoverState.open === 'function') {
                        window.sfHandoverState.open();
                    }
                    return;
                }

                e.preventDefault();

                if (getOfflineActiveTrip()) {
                    showOfflineMessage('A trip is already in progress offline. End it before starting another.');
                    return;
                }

                if (navigator.onLine) {
                    const vehicleSelect = document.getElementById('vehicleSelect');
                    const vehicleId = vehicleSelect ? vehicleSelect.value : '';
                    if (vehicleId && vehicleId !== 'private_vehicle' && typeof window.sfCheckVehicleActiveForStart === 'function') {
                        const isActive = await window.sfCheckVehicleActiveForStart(vehicleId);
                        if (isActive) {
                            return;
                        }
                    }

                    if (window.sfStartTripState) {
                        window.sfStartTripState.submitting = true;
                    }
                    const result = await submitFormOnline(startTripForm);
                    if (window.sfStartTripState) {
                        window.sfStartTripState.submitting = false;
                    }
                    if (result.ok) return;
                    if (!result.networkError) {
                        showOfflineMessage('Could not start the trip right now. Please try again.');
                        return;
                    }
                }

                const payload = buildOfflineStartPayload(startTripForm);
                if (!payload.private_vehicle && (!payload.vehicle_id || Number.isNaN(payload.vehicle_id))) {
                    showOfflineMessage('Select a vehicle before starting.');
                    return;
                }
                if (Number.isNaN(payload.start_km)) {
                    showOfflineMessage('Enter a valid starting reading.');
                    return;
                }
                if (MANUAL_TRIP_TIMES_REQUIRED && (!payload.started_at || String(payload.started_at).trim() === '')) {
                    showOfflineMessage('Enter a start time before starting.');
                    return;
                }

                const vehicleSelect = document.getElementById('vehicleSelect');
                const selectedOpt = vehicleSelect && vehicleSelect.options[vehicleSelect.selectedIndex];
                const vehicleText = payload.private_vehicle
                    ? 'Private vehicle'
                    : (selectedOpt ? selectedOpt.textContent : '');

                setOfflineActiveTrip({
                    ...payload,
                    started_at: MANUAL_TRIP_TIMES_REQUIRED ? new Date(String(payload.started_at)).toISOString() : new Date().toISOString(),
                    vehicle_text: vehicleText,
                    private_vehicle: payload.private_vehicle ? 1 : 0,
                    source: 'offline',
                });

                closeSheets();
                showOfflineMessage('No signal: trip started offline. End it to sync later.');
                renderOfflineActiveTrip();
            });
        }

        if (endTripForm) {
            endTripForm.addEventListener('submit', async (e) => {
                if (!endTripForm.checkValidity()) {
                    return;
                }

                e.preventDefault();

                const active = getOfflineActiveTrip();
                if (navigator.onLine && (!active || active.source === 'server')) {
                    const result = await submitFormOnline(endTripForm);
                    if (result.ok) return;
                    if (!result.networkError) {
                        showOfflineMessage('Could not end the trip right now. Please try again.');
                        return;
                    }
                }

                if (!active) {
                    showOfflineMessage('No offline trip found to end.');
                    return;
                }

                const fd = new FormData(endTripForm);
                const endKmRaw = fd.get('end_km');
                const endKmVal = Number(endKmRaw);
                if (Number.isNaN(endKmVal)) {
                    showOfflineMessage('Enter a valid ending reading.');
                    return;
                }
                if (endKmVal < Number(active.start_km)) {
                    showOfflineMessage('Ending reading must be the same as or greater than the starting reading.');
                    return;
                }

                const endedAtVal = MANUAL_TRIP_TIMES_REQUIRED ? String(fd.get('ended_at') || '').trim() : '';
                if (MANUAL_TRIP_TIMES_REQUIRED && endedAtVal === '') {
                    showOfflineMessage('Enter an end time before ending.');
                    return;
                }

                const endedAtLocal = endedAtVal;
                let endedAtIso = '';
                if (MANUAL_TRIP_TIMES_REQUIRED) {
                    try {
                        endedAtIso = new Date(endedAtVal).toISOString();
                    } catch (e) {
                        showOfflineMessage('Enter a valid end time.');
                        return;
                    }
                }

                if (active.source === 'server' && active.trip_id) {
                    const updates = getOfflineEndUpdates();
                    updates.push({
                        trip_id: active.trip_id,
                        end_km: endKmVal,
                        ended_at: MANUAL_TRIP_TIMES_REQUIRED ? endedAtLocal : new Date().toISOString(),
                    });
                    setOfflineEndUpdates(updates);
                    setOfflineActiveTrip(null);

                    closeSheets();
                    showOfflineMessage('Trip ended offline. Will sync when signal returns.');
                    renderOfflineActiveTrip();
                    await syncOfflineEndUpdatesIfPossible();
                    return;
                }

                const completedTrip = {
                    vehicle_id: active.vehicle_id,
                    trip_mode: active.trip_mode,
                    start_km: active.start_km,
                    end_km: endKmVal,
                    started_at: active.started_at,
                    ended_at: MANUAL_TRIP_TIMES_REQUIRED ? endedAtIso : new Date().toISOString(),
                    customer_id: active.customer_id,
                    customer_name: active.customer_name,
                    client_present: active.client_present,
                    client_address: active.client_address,
                    purpose_of_travel: active.purpose_of_travel,
                };

                const completed = getOfflineCompletedTrips();
                completed.push(completedTrip);
                setOfflineCompletedTrips(completed);
                setOfflineActiveTrip(null);

                closeSheets();
                showOfflineMessage('Trip ended offline. Will sync when signal returns.');
                renderOfflineActiveTrip();
                await syncOfflineTripsIfPossible();
            });
        }

        if (reportFaultForm) {
            reportFaultForm.addEventListener('submit', async (e) => {
                e.preventDefault();

                if (!reportFaultForm.checkValidity()) {
                    return;
                }

                const fd = new FormData(reportFaultForm);
                const payload = Object.fromEntries(fd.entries());

                if (!payload.vehicle_id) {
                    showOfflineMessage('Select a vehicle before reporting.');
                    return;
                }
                if (!payload.report_type) {
                    showOfflineMessage('Select a report type.');
                    return;
                }
                if (!payload.severity) {
                    showOfflineMessage('Select a severity.');
                    return;
                }
                if (!payload.description || String(payload.description).trim() === '') {
                    showOfflineMessage('Add a description before reporting.');
                    return;
                }

                if (navigator.onLine) {
                    try {
                        const controller = new AbortController();
                        const timeout = setTimeout(() => controller.abort(), 8000);

                        const res = await fetch('/app/sharpfleet/faults/standalone', {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'X-CSRF-TOKEN': getCsrfToken(),
                            },
                            body: fd,
                            signal: controller.signal,
                        });

                        clearTimeout(timeout);

                        if (res.ok) {
                            closeSheets();
                            showOfflineMessage('Report submitted.');
                            setTimeout(() => window.location.href = '/app/sharpfleet/mobile', 500);
                            return;
                        }
                    } catch (e) {
                        // fall back to offline queue
                    }
                }

                const reports = getOfflineFaultReports();
                reports.push(payload);
                setOfflineFaultReports(reports);

                closeSheets();
                showOfflineMessage('No signal: report queued and will sync later.');
            });
        }

        window.addEventListener('online', () => {
            showOfflineMessage('Back online. Syncing offline trips...');
            renderOfflineActiveTrip();
            syncOfflineTripsIfPossible();
            syncOfflineEndUpdatesIfPossible();
            syncOfflineFaultReportsIfPossible();
            showSupportSentMessage();
        });

        window.addEventListener('offline', renderOfflineActiveTrip);

        seedServerActiveTrip();
        renderOfflineActiveTrip();
        syncOfflineTripsIfPossible();
        syncOfflineEndUpdatesIfPossible();
        syncOfflineFaultReportsIfPossible();
        showSupportSentMessage();

        function showSupportSentMessage() {
            if (!supportSentAlert || !navigator.onLine) return;
            const key = 'sf_support_sent_notice_v1';
            const stamp = localStorage.getItem(key);
            if (!stamp) return;
            supportSentAlert.textContent = 'Support request sent.';
            supportSentAlert.style.display = '';
            localStorage.removeItem(key);
            setTimeout(() => {
                supportSentAlert.style.display = 'none';
            }, 4000);
        }
    })();
</script>
@endsection
