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
        ->join('vehicles', 'trips.vehicle_id', '=', 'vehicles.id')
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
            fn ($q) => $q->whereIn('vehicles.branch_id', $accessibleBranchIds)
        )
        ->whereNotNull('trips.started_at')
        ->whereNull('trips.ended_at')
        ->first();

    $tripQuery = DB::connection('sharpfleet')
        ->table('trips')
        ->join('vehicles', 'trips.vehicle_id', '=', 'vehicles.id')
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
            fn ($q) => $q->whereIn('vehicles.branch_id', $accessibleBranchIds)
        )
        ->whereNotNull('trips.started_at')
        ->whereNotNull('trips.ended_at')
        ->orderByDesc('trips.started_at')
        ->limit(5);

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

    @if($activeTrip)
        <div class="sf-mobile-card">
            <div class="sf-mobile-card-title">Trip in Progress</div>
            <div class="hint-text" style="margin-top: 6px;">
                <strong>Vehicle:</strong> {{ $activeTrip->vehicle_name }} ({{ $activeTrip->registration_number }})
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
                    {{ $trip->vehicle_name }} ({{ $trip->registration_number }})
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
    if (!card) return;

    function syncOfflineCard() {
        card.style.display = navigator.onLine ? 'none' : '';
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
