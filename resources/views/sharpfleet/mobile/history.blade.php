@extends('sharpfleet.mobile.layouts.app')

@section('title', 'Trip History')

@section('content')
@php
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Schema;
    use App\Services\SharpFleet\CompanySettingsService;
    use App\Services\SharpFleet\BranchService;

    $user = session('sharpfleet.user');

    $settingsService = new CompanySettingsService($user['organisation_id']);
    $settings = $settingsService->all();
    $companyTimezone = $settingsService->timezone();
    $purposeOfTravelEnabled = $settingsService->purposeOfTravelEnabled();

    $customerCaptureEnabled = (bool) ($settings['customer']['enabled'] ?? false);
    $hasCustomersTable = Schema::connection('sharpfleet')->hasTable('customers');
    $customerLinkingEnabled = $customerCaptureEnabled && $hasCustomersTable;

    $customerLabel = trim((string) $settingsService->clientLabel());
    $customerLabel = $customerLabel !== '' ? $customerLabel : 'Customer';

    $branchesService = new BranchService();
    $branchesEnabled = $branchesService->branchesEnabled();
    $branchAccessEnabled = $branchesEnabled
        && $branchesService->vehiclesHaveBranchSupport()
        && $branchesService->userBranchAccessEnabled();

    $accessibleBranchIds = $branchAccessEnabled
        ? $branchesService->getAccessibleBranchIdsForUser((int) $user['organisation_id'], (int) $user['id'])
        : [];

    if ($branchAccessEnabled && count($accessibleBranchIds) === 0) {
        abort(403, 'No branch access.');
    }

    $activeTrip = DB::connection('sharpfleet')
        ->table('trips')
        ->leftJoin('vehicles', 'trips.vehicle_id', '=', 'vehicles.id')
        ->select(
            'trips.*',
            'vehicles.name as vehicle_name',
            'vehicles.registration_number',
            'vehicles.tracking_mode',
            'vehicles.branch_id as vehicle_branch_id'
        )
        ->where('trips.user_id', $user['id'])
        ->where('trips.organisation_id', $user['organisation_id'])
        ->when(
            $branchAccessEnabled,
            fn ($q) => $q->where(function ($sub) use ($accessibleBranchIds) {
                $sub->whereNull('trips.vehicle_id')
                    ->orWhereIn('vehicles.branch_id', $accessibleBranchIds);
            })
        )
        ->whereNotNull('trips.started_at')
        ->whereNull('trips.ended_at')
        ->first();

    $tripQuery = DB::connection('sharpfleet')
        ->table('trips')
        ->leftJoin('vehicles', 'trips.vehicle_id', '=', 'vehicles.id')
        ->when(
            $customerLinkingEnabled,
            fn ($q) => $q->leftJoin('customers', 'trips.customer_id', '=', 'customers.id')
        )
        ->select(
            'trips.*',
            'vehicles.name as vehicle_name',
            'vehicles.registration_number',
            'vehicles.tracking_mode',
            'vehicles.branch_id as vehicle_branch_id',
            $customerLinkingEnabled
                ? DB::raw('COALESCE(customers.name, trips.customer_name) as customer_name_display')
                : DB::raw('trips.customer_name as customer_name_display')
        )
        ->where('trips.user_id', $user['id'])
        ->where('trips.organisation_id', $user['organisation_id'])
        ->when(
            $branchAccessEnabled,
            fn ($q) => $q->where(function ($sub) use ($accessibleBranchIds) {
                $sub->whereNull('trips.vehicle_id')
                    ->orWhereIn('vehicles.branch_id', $accessibleBranchIds);
            })
        )
        ->whereNotNull('trips.started_at')
        ->whereNotNull('trips.ended_at')
        ->orderByDesc('trips.ended_at')
        ->limit(10);

    $trips = $tripQuery->get();
@endphp

<section class="sf-mobile-dashboard">
    <h1 class="sf-mobile-title">Trip History</h1>
    <p class="sf-mobile-subtitle">Your recent completed trips.</p>

    <div id="sf-offline-history" class="sf-mobile-card" style="display:none;">
        <div class="sf-mobile-card-title">You are offline</div>
        <div class="sf-mobile-card-text">
            History may be limited until you are back online.
        </div>
        <button type="button" class="sf-mobile-secondary-btn" id="sf-offline-history-close">
            Close
        </button>
    </div>

    <div id="sf-offline-queued" class="sf-mobile-card" style="display:none;">
        <div class="sf-mobile-card-title">Queued Trips (Offline)</div>
        <div class="sf-mobile-card-text">These entries will sync when you are back online.</div>
        <div id="sf-offline-queued-list" style="margin-top: 10px;"></div>
    </div>

    @if($activeTrip)
        <div class="sf-mobile-card">
            <div class="sf-mobile-card-title">Trip in Progress</div>
            <div class="hint-text" style="margin-top: 6px;">
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
                <strong>Started:</strong> {{ \Carbon\Carbon::parse($activeTrip->started_at)->timezone($tripTz)->format('M j, Y g:i A') }}
            </div>
            <a href="/app/sharpfleet/mobile" class="sf-mobile-secondary-btn" style="margin-top: 12px; display: block; text-align: center;">
                Go to Active Trip
            </a>
        </div>
    @endif

    @if($trips->count() === 0)
        <div class="sf-mobile-card">
            <div class="sf-mobile-card-title">No trips yet</div>
            <div class="sf-mobile-card-text">Completed trips will appear here once you finish them.</div>
        </div>
    @else
        @foreach($trips as $trip)
            @php
                $tripTz = isset($trip->timezone) && trim((string) $trip->timezone) !== ''
                    ? (string) $trip->timezone
                    : $companyTimezone;

                $trackingMode = (string) ($trip->tracking_mode ?? 'distance');
                $isHours = $trackingMode === 'hours';

                $tripBranchId = (int) ($trip->vehicle_branch_id ?? 0);
                $distanceUnit = $isHours
                    ? 'hours'
                    : $settingsService->distanceUnitForBranch($tripBranchId > 0 ? $tripBranchId : null);

                $startReading = $trip->start_km;
                $endReading = $trip->end_km;
                $delta = null;

                if ($startReading !== null && $endReading !== null && is_numeric($startReading) && is_numeric($endReading)) {
                    $deltaVal = (float) $endReading - (float) $startReading;
                    if ($deltaVal >= 0) {
                        $delta = $deltaVal;
                    }
                }

                $tripMode = (string) ($trip->trip_mode ?? 'business');
                $isBusinessTrip = $tripMode !== 'private';
                $tripTypeLabel = $tripMode === 'private' ? 'Private' : 'Business';

                $customerName = trim((string) ($trip->customer_name_display ?? ''));
                $formattedDelta = $delta !== null
                    ? ($isHours ? number_format($delta, 1) : number_format($delta, 0))
                    : null;
            @endphp

            <div class="sf-mobile-card">
                <div class="sf-mobile-card-title">
                    @if($trip->vehicle_name)
                        {{ $trip->vehicle_name }} ({{ $trip->registration_number }})
                    @else
                        Private vehicle
                    @endif
                </div>
                <div class="hint-text" style="margin-top: 6px;">
                    <strong>Trip Type:</strong> {{ $tripTypeLabel }}
                </div>
                <div class="hint-text" style="margin-top: 6px;">
                    <strong>Started:</strong> {{ \Carbon\Carbon::parse($trip->started_at)->timezone($tripTz)->format('M j, Y g:i A') }}
                </div>
                <div class="hint-text" style="margin-top: 6px;">
                    <strong>Ended:</strong> {{ \Carbon\Carbon::parse($trip->ended_at)->timezone($tripTz)->format('M j, Y g:i A') }}
                </div>
                <div class="hint-text" style="margin-top: 6px;">
                    <strong>{{ $isHours ? 'Hours' : 'Odometer' }}:</strong>
                    {{ $startReading !== null ? number_format($startReading) : '-' }}
                    -
                    {{ $endReading !== null ? number_format($endReading) : '-' }}
                    ({{ $distanceUnit }})
                </div>
                @if($formattedDelta !== null)
                    <div class="hint-text" style="margin-top: 6px;">
                        <strong>{{ $isHours ? 'Total Hours' : 'Distance' }}:</strong>
                        {{ $formattedDelta }} {{ $distanceUnit }}
                    </div>
                @endif

                @if($isBusinessTrip && $customerCaptureEnabled)
                    <div class="hint-text" style="margin-top: 6px;">
                        <strong>{{ $customerLabel }}:</strong> {{ $customerName !== '' ? $customerName : '-' }}
                    </div>
                @endif

                @if($isBusinessTrip && ($settings['client_presence']['enabled'] ?? false))
                    <div class="hint-text" style="margin-top: 6px;">
                        <strong>{{ $settings['client_presence']['label'] ?? 'Client' }} Present:</strong>
                        {{ $trip->client_present ? 'Yes' : 'No' }}
                    </div>
                    @if(($settings['client_presence']['enable_addresses'] ?? false) && $trip->client_address)
                        <div class="hint-text" style="margin-top: 6px;">
                            <strong>Client Address:</strong> {{ $trip->client_address }}
                        </div>
                    @endif
                @endif

                @if($isBusinessTrip && $purposeOfTravelEnabled)
                    <div class="hint-text" style="margin-top: 6px;">
                        <strong>Purpose of Travel:</strong> {{ $trip->purpose_of_travel ?: '-' }}
                    </div>
                @endif
            </div>
        @endforeach

        <div class="hint-text" style="margin-top: 8px;">
            Times shown in {{ $companyTimezone }}.
        </div>
    @endif
</section>

<script>
(function () {
    const card = document.getElementById('sf-offline-history');
    const closeBtn = document.getElementById('sf-offline-history-close');
    const queuedCard = document.getElementById('sf-offline-queued');
    const queuedList = document.getElementById('sf-offline-queued-list');
    const companyTimezone = @json($companyTimezone ?? 'UTC');
    const OFFLINE_ACTIVE_KEY = 'sharpfleet_offline_active_trip_v1';
    const OFFLINE_COMPLETED_KEY = 'sharpfleet_offline_completed_trips_v1';
    const OFFLINE_END_UPDATES_KEY = 'sharpfleet_offline_end_updates_v1';
    if (!card) return;

    function safeParse(raw, fallback) {
        try { return JSON.parse(raw); } catch (e) { return fallback; }
    }

    function getLocalJson(key, fallback) {
        try {
            const raw = localStorage.getItem(key);
            return raw ? safeParse(raw, fallback) : fallback;
        } catch (e) {
            return fallback;
        }
    }

    function formatDate(iso) {
        if (!iso) return '-';
        try {
            return new Date(iso).toLocaleString(undefined, { timeZone: companyTimezone });
        } catch (e) {
            try { return new Date(iso).toLocaleString(); } catch (e2) { return String(iso); }
        }
    }

    function renderQueuedTrips() {
        if (!queuedCard || !queuedList) return;
        const active = getLocalJson(OFFLINE_ACTIVE_KEY, null);
        const completed = getLocalJson(OFFLINE_COMPLETED_KEY, []);
        const endUpdates = getLocalJson(OFFLINE_END_UPDATES_KEY, []);

        const entries = [];
        if (active && active.source === 'offline') {
            entries.push({
                title: active.private_vehicle ? 'Private vehicle' : (active.vehicle_text || `Vehicle ${active.vehicle_id || ''}`),
                started: active.started_at ? formatDate(active.started_at) : '-',
                ended: null,
                start_km: active.start_km ?? null,
                end_km: null,
                type: 'In progress',
            });
        }

        if (Array.isArray(completed)) {
            completed.forEach((t, idx) => {
                const label = t.vehicle_text
                    ? String(t.vehicle_text)
                    : (t.private_vehicle ? 'Private vehicle' : `Vehicle ${t.vehicle_id ?? ''}`);
                const distanceUnit = t.distance_unit ? String(t.distance_unit) : '';
                const trackingMode = t.tracking_mode ? String(t.tracking_mode) : 'distance';
                const unitLabel = trackingMode === 'hours' ? 'hours' : (distanceUnit || '');

                entries.push({
                    title: label,
                    started: t.started_at ? formatDate(t.started_at) : '-',
                    ended: t.ended_at ? formatDate(t.ended_at) : '-',
                    start_km: t.start_km ?? null,
                    end_km: t.end_km ?? null,
                    unit: unitLabel,
                    type: 'Completed offline',
                    key: `completed-${idx}`,
                });
            });
        }

        if (Array.isArray(endUpdates)) {
            endUpdates.forEach((t, idx) => {
                entries.push({
                    title: `Trip ${t.trip_id ?? ''}`,
                    started: null,
                    ended: t.ended_at ? formatDate(t.ended_at) : '-',
                    start_km: null,
                    end_km: t.end_km ?? null,
                    type: 'End queued',
                    key: `end-${idx}`,
                });
            });
        }

        if (entries.length === 0) {
            queuedCard.style.display = 'none';
            queuedList.innerHTML = '';
            return;
        }

        queuedList.innerHTML = '';
        entries.forEach((entry) => {
            const item = document.createElement('div');
            item.className = 'sf-mobile-card';
            item.style.marginTop = '10px';
            item.innerHTML = `
                <div class="sf-mobile-card-title">${entry.title || 'Trip'}</div>
                <div class="hint-text" style="margin-top: 6px;"><strong>Status:</strong> ${entry.type}</div>
                ${entry.started ? `<div class="hint-text" style="margin-top: 6px;"><strong>Started:</strong> ${entry.started}</div>` : ''}
                ${entry.ended ? `<div class="hint-text" style="margin-top: 6px;"><strong>Ended:</strong> ${entry.ended}</div>` : ''}
                ${(entry.start_km !== null && entry.start_km !== undefined) ? `<div class="hint-text" style="margin-top: 6px;"><strong>Start:</strong> ${entry.start_km} ${entry.unit || ''}</div>` : ''}
                ${(entry.end_km !== null && entry.end_km !== undefined) ? `<div class="hint-text" style="margin-top: 6px;"><strong>End:</strong> ${entry.end_km} ${entry.unit || ''}</div>` : ''}
            `;
            queuedList.appendChild(item);
        });

        queuedCard.style.display = '';
    }

    function syncOfflineCard() {
        card.style.display = navigator.onLine ? 'none' : '';
        renderQueuedTrips();
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', () => {
            card.style.display = 'none';
        });
    }

    window.addEventListener('online', syncOfflineCard);
    window.addEventListener('offline', syncOfflineCard);
    syncOfflineCard();
})();
</script>
@endsection
