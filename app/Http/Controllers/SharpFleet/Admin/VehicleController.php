<?php

namespace App\Http\Controllers\SharpFleet\Admin;

use App\Http\Controllers\Controller;
use App\Services\SharpFleet\AuditLogService;
use App\Services\SharpFleet\CompanySettingsService;
use App\Services\SharpFleet\EntitlementService;
use App\Services\SharpFleet\StripeSubscriptionSyncService;
use App\Services\SharpFleet\VehicleService;
use App\Services\SharpFleet\BranchService;
use App\Support\SharpFleet\Roles;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class VehicleController extends Controller
{
    protected VehicleService $vehicleService;
    protected StripeSubscriptionSyncService $stripeSubscriptionSync;
    protected AuditLogService $audit;

    private const PENDING_CREATE_SESSION_KEY = 'sharpfleet.pending_vehicle_create';
    private const PENDING_ARCHIVE_SESSION_KEY = 'sharpfleet.pending_vehicle_archive';

    public function __construct(VehicleService $vehicleService, StripeSubscriptionSyncService $stripeSubscriptionSync, AuditLogService $audit)
    {
        $this->vehicleService = $vehicleService;
        $this->stripeSubscriptionSync = $stripeSubscriptionSync;
        $this->audit = $audit;

        // Booking admins can access the admin portal, but must not manage fleet/vehicles.
        $this->middleware(function (Request $request, $next) {
            $fleetUser = $request->session()->get('sharpfleet.user');
            if (!$fleetUser || !Roles::canManageFleet($fleetUser)) {
                abort(403);
            }
            return $next($request);
        });
    }

    /**
     * List vehicles for the logged-in organisation (admin only)
     */
    public function index(Request $request)
    {
        $fleetUser = $request->session()->get('sharpfleet.user');

        if (!$fleetUser || empty($fleetUser['organisation_id'])) {
            abort(403, 'No SharpFleet organisation context.');
        }

        $organisationId = (int) $fleetUser['organisation_id'];

        $branchService = new BranchService();
        $bypassBranchRestrictions = Roles::bypassesBranchRestrictions($fleetUser);
        $branchesEnabled = $branchService->branchesEnabled();
        $branchAccessEnabled = $branchesEnabled
            && $branchService->vehiclesHaveBranchSupport()
            && $branchService->userBranchAccessEnabled();
        // Company admins bypass branch scoping entirely.
        $branchScopeEnabled = $branchAccessEnabled && !$bypassBranchRestrictions;
        $accessibleBranchIds = $branchScopeEnabled
            ? $branchService->getAccessibleBranchIdsForUser($organisationId, (int) ($fleetUser['id'] ?? 0))
            : [];
        if ($branchScopeEnabled && count($accessibleBranchIds) === 0) {
            abort(403, 'No branch access.');
        }

        $entitlements = new EntitlementService($fleetUser);
        $isSubscribed = $entitlements->isSubscriptionActive();

        $vehicles = $this->vehicleService->getAvailableVehicles(
            $organisationId,
            $branchScopeEnabled ? $accessibleBranchIds : null
        );

        $activeTrips = DB::connection('sharpfleet')
            ->table('trips')
            ->join('vehicles', 'trips.vehicle_id', '=', 'vehicles.id')
            ->join('users', 'trips.user_id', '=', 'users.id')
            ->where('trips.organisation_id', $organisationId)
            ->when(
                $branchScopeEnabled,
                fn ($q) => $q->whereIn('vehicles.branch_id', $accessibleBranchIds)
            )
            ->whereNotNull('trips.started_at')
            ->whereNull('trips.ended_at')
            ->orderByDesc('trips.started_at')
            ->select(
                'trips.vehicle_id',
                'trips.started_at',
                DB::raw("CONCAT(users.first_name, ' ', users.last_name) as driver_name")
            )
            ->get();

        $activeTripVehicleIds = [];
        $activeTripsByVehicle = [];

        foreach ($activeTrips as $t) {
            $vehicleId = (int) $t->vehicle_id;

            // Keep the latest active trip per vehicle (the query is already ordered by started_at DESC)
            if (!isset($activeTripsByVehicle[$vehicleId])) {
                $activeTripsByVehicle[$vehicleId] = [
                    'driver_name' => trim((string) $t->driver_name) ?: '—',
                    'started_at'  => $t->started_at,
                ];
            }

            $activeTripVehicleIds[$vehicleId] = true;
        }

        return view('sharpfleet.admin.vehicles.index', [
            'vehicles' => $vehicles,
            'activeTripVehicleIds' => $activeTripVehicleIds,
            'activeTripsByVehicle' => $activeTripsByVehicle,
            'isSubscribed' => $isSubscribed,
        ]);
    }

    /**
     * List permanently assigned vehicles and their assigned driver (admin only)
     */
    public function assigned(Request $request)
    {
        $fleetUser = $request->session()->get('sharpfleet.user');

        if (!$fleetUser || empty($fleetUser['organisation_id'])) {
            abort(403, 'No SharpFleet organisation context.');
        }

        $organisationId = (int) $fleetUser['organisation_id'];

        $vehiclesHaveAssignment = Schema::connection('sharpfleet')->hasColumn('vehicles', 'assignment_type')
            && Schema::connection('sharpfleet')->hasColumn('vehicles', 'assigned_driver_id');

        $branchService = new BranchService();
        $bypassBranchRestrictions = Roles::bypassesBranchRestrictions($fleetUser);
        $branchesEnabled = $branchService->branchesEnabled();
        $branchAccessEnabled = $branchesEnabled
            && $branchService->vehiclesHaveBranchSupport()
            && $branchService->userBranchAccessEnabled();
        $branchScopeEnabled = $branchAccessEnabled && !$bypassBranchRestrictions;
        $accessibleBranchIds = $branchScopeEnabled
            ? $branchService->getAccessibleBranchIdsForUser($organisationId, (int) ($fleetUser['id'] ?? 0))
            : [];
        if ($branchScopeEnabled && count($accessibleBranchIds) === 0) {
            abort(403, 'No branch access.');
        }

        $vehicles = collect();
        if ($vehiclesHaveAssignment) {
            $vehicles = DB::connection('sharpfleet')
                ->table('vehicles')
                ->leftJoin('users', function ($join) {
                    $join->on('vehicles.assigned_driver_id', '=', 'users.id');
                })
                ->where('vehicles.organisation_id', $organisationId)
                ->where('vehicles.is_active', 1)
                ->where('vehicles.assignment_type', 'permanent')
                ->whereNotNull('vehicles.assigned_driver_id')
                ->when(
                    $branchScopeEnabled && Schema::connection('sharpfleet')->hasColumn('vehicles', 'branch_id'),
                    fn ($q) => $q->whereIn('vehicles.branch_id', $accessibleBranchIds)
                )
                ->orderBy('vehicles.name')
                ->select(
                    'vehicles.id',
                    'vehicles.name',
                    'vehicles.registration_number',
                    'vehicles.branch_id',
                    'vehicles.is_in_service',
                    'vehicles.out_of_service_reason',
                    'vehicles.out_of_service_note',
                    'vehicles.assigned_driver_id',
                    'users.first_name as driver_first_name',
                    'users.last_name as driver_last_name'
                )
                ->get();
        }

        $branches = collect();
        if ($branchesEnabled && $branchService->vehiclesHaveBranchSupport()) {
            $branches = $branchService->getBranches($organisationId);
            if ($branchScopeEnabled) {
                $branches = $branches->filter(fn ($b) => in_array((int) ($b->id ?? 0), $accessibleBranchIds, true))->values();
            }
        }

        return view('sharpfleet.admin.vehicles.assigned', [
            'vehiclesHaveAssignment' => $vehiclesHaveAssignment,
            'vehicles' => $vehicles,
            'branchesEnabled' => ($branchesEnabled && $branchService->vehiclesHaveBranchSupport()),
            'branches' => $branches,
        ]);
    }

    /**
     * List out-of-service vehicles (admin only)
     */
    public function outOfService(Request $request)
    {
        $fleetUser = $request->session()->get('sharpfleet.user');

        if (!$fleetUser || empty($fleetUser['organisation_id'])) {
            abort(403, 'No SharpFleet organisation context.');
        }

        $organisationId = (int) $fleetUser['organisation_id'];

        $hasIsInService = Schema::connection('sharpfleet')->hasColumn('vehicles', 'is_in_service');
        $hasOutOfServiceReason = Schema::connection('sharpfleet')->hasColumn('vehicles', 'out_of_service_reason');
        $hasOutOfServiceNote = Schema::connection('sharpfleet')->hasColumn('vehicles', 'out_of_service_note');
        $hasOutOfServiceAt = Schema::connection('sharpfleet')->hasColumn('vehicles', 'out_of_service_at');

        $branchService = new BranchService();
        $bypassBranchRestrictions = Roles::bypassesBranchRestrictions($fleetUser);
        $branchesEnabled = $branchService->branchesEnabled();
        $branchAccessEnabled = $branchesEnabled
            && $branchService->vehiclesHaveBranchSupport()
            && $branchService->userBranchAccessEnabled();
        $branchScopeEnabled = $branchAccessEnabled && !$bypassBranchRestrictions;
        $accessibleBranchIds = $branchScopeEnabled
            ? $branchService->getAccessibleBranchIdsForUser($organisationId, (int) ($fleetUser['id'] ?? 0))
            : [];
        if ($branchScopeEnabled && count($accessibleBranchIds) === 0) {
            abort(403, 'No branch access.');
        }

        $vehicles = collect();
        if ($hasIsInService) {
            $select = [
                'vehicles.id',
                'vehicles.name',
                'vehicles.registration_number',
                'vehicles.branch_id',
                'vehicles.is_in_service',
            ];
            if ($hasOutOfServiceReason) {
                $select[] = 'vehicles.out_of_service_reason';
            }
            if ($hasOutOfServiceNote) {
                $select[] = 'vehicles.out_of_service_note';
            }
            if ($hasOutOfServiceAt) {
                $select[] = 'vehicles.out_of_service_at';
            }

            $vehicles = DB::connection('sharpfleet')
                ->table('vehicles')
                ->where('vehicles.organisation_id', $organisationId)
                ->where('vehicles.is_active', 1)
                ->where('vehicles.is_in_service', 0)
                ->when(
                    $branchScopeEnabled && Schema::connection('sharpfleet')->hasColumn('vehicles', 'branch_id'),
                    fn ($q) => $q->whereIn('vehicles.branch_id', $accessibleBranchIds)
                )
                ->orderBy('vehicles.name')
                ->select($select)
                ->get();
        }

        $branches = collect();
        if ($branchesEnabled && $branchService->vehiclesHaveBranchSupport()) {
            $branches = $branchService->getBranches($organisationId);
            if ($branchScopeEnabled) {
                $branches = $branches->filter(fn ($b) => in_array((int) ($b->id ?? 0), $accessibleBranchIds, true))->values();
            }
        }

        return view('sharpfleet.admin.vehicles.out-of-service', [
            'hasIsInService' => $hasIsInService,
            'hasOutOfServiceReason' => $hasOutOfServiceReason,
            'hasOutOfServiceNote' => $hasOutOfServiceNote,
            'hasOutOfServiceAt' => $hasOutOfServiceAt,
            'vehicles' => $vehicles,
            'branchesEnabled' => ($branchesEnabled && $branchService->vehiclesHaveBranchSupport()),
            'branches' => $branches,
        ]);
    }

    /**
     * Confirmation step (subscribed orgs only) for archiving a vehicle.
     */
    public function confirmArchive(Request $request, $vehicle)
    {
        $fleetUser = $request->session()->get('sharpfleet.user');

        if (!$fleetUser || empty($fleetUser['organisation_id'])) {
            abort(403, 'No SharpFleet organisation context.');
        }

        $organisationId = (int) $fleetUser['organisation_id'];
        $vehicleId = (int) $vehicle;

        $branchService = new BranchService();
        [$branchScopeEnabled, $accessibleBranchIds] = $this->resolveVehicleBranchScope($fleetUser, $organisationId, $branchService);

        $entitlements = new EntitlementService($fleetUser);
        $isSubscribed = $entitlements->isSubscriptionActive();

        if (!$isSubscribed) {
            $request->session()->forget(self::PENDING_ARCHIVE_SESSION_KEY);
            return redirect('/app/sharpfleet/admin/vehicles');
        }

        $record = $this->vehicleService
            ->getVehicleForOrganisation($organisationId, $vehicleId);

        if (!$record) {
            abort(404, 'Vehicle not found.');
        }

        if ($branchScopeEnabled) {
            $this->assertVehicleRecordInBranches($record, $accessibleBranchIds);
        }

        $isActive = (int) ($record->is_active ?? 0) === 1;
        if (!$isActive) {
            return redirect('/app/sharpfleet/admin/vehicles')
                ->with('error', 'Vehicle is already archived.');
        }

        $request->session()->put(self::PENDING_ARCHIVE_SESSION_KEY, [
            'organisation_id' => $organisationId,
            'vehicle_id' => $vehicleId,
            'vehicle_name' => (string) ($record->name ?? ''),
        ]);

        $currentVehiclesCount = (int) DB::connection('sharpfleet')
            ->table('vehicles')
            ->where('organisation_id', $organisationId)
            ->where('is_active', 1)
            ->count();

        $newVehiclesCount = max(0, $currentVehiclesCount - 1);

        $currentPricing = $this->calculateMonthlyPrice($currentVehiclesCount);
        $newPricing = $this->calculateMonthlyPrice($newVehiclesCount);

        return view('sharpfleet.admin.vehicles.confirm-archive', [
            'vehicleId' => $vehicleId,
            'vehicleName' => (string) ($record->name ?? ''),
            'currentVehiclesCount' => $currentVehiclesCount,
            'newVehiclesCount' => $newVehiclesCount,
            'currentMonthlyPrice' => $currentPricing['monthlyPrice'],
            'currentMonthlyPriceBreakdown' => $currentPricing['breakdown'],
            'newMonthlyPrice' => $newPricing['monthlyPrice'],
            'newMonthlyPriceBreakdown' => $newPricing['breakdown'],
            'requiresContactForPricing' => $newPricing['requiresContact'],
        ]);
    }

    /**
     * Finalize archive after acknowledgement (subscribed orgs only).
     */
    public function confirmArchiveStore(Request $request, $vehicle)
    {
        $fleetUser = $request->session()->get('sharpfleet.user');

        if (!$fleetUser || empty($fleetUser['organisation_id'])) {
            abort(403, 'No SharpFleet organisation context.');
        }

        $organisationId = (int) $fleetUser['organisation_id'];
        $vehicleId = (int) $vehicle;

        $branchService = new BranchService();
        [$branchScopeEnabled, $accessibleBranchIds] = $this->resolveVehicleBranchScope($fleetUser, $organisationId, $branchService);

        $entitlements = new EntitlementService($fleetUser);
        $isSubscribed = $entitlements->isSubscriptionActive();

        if (!$isSubscribed) {
            $request->session()->forget(self::PENDING_ARCHIVE_SESSION_KEY);
            return redirect('/app/sharpfleet/admin/vehicles');
        }

        $pending = $request->session()->get(self::PENDING_ARCHIVE_SESSION_KEY);
        $pendingOrgId = (int) ($pending['organisation_id'] ?? 0);
        $pendingVehicleId = (int) ($pending['vehicle_id'] ?? 0);

        if ($pendingOrgId !== $organisationId || $pendingVehicleId !== $vehicleId) {
            return redirect('/app/sharpfleet/admin/vehicles');
        }

        $request->validate([
            'ack_subscription_price_decrease' => ['required', 'accepted'],
        ]);

        $record = $this->vehicleService
            ->getVehicleForOrganisation($organisationId, $vehicleId);

        if (!$record) {
            abort(404, 'Vehicle not found.');
        }

        if ($branchScopeEnabled) {
            $this->assertVehicleRecordInBranches($record, $accessibleBranchIds);
        }

        $isActive = (int) ($record->is_active ?? 0) === 1;
        if (!$isActive) {
            $request->session()->forget(self::PENDING_ARCHIVE_SESSION_KEY);
            return redirect('/app/sharpfleet/admin/vehicles')
                ->with('warning', 'Vehicle was already archived.');
        }

        $currentVehiclesCount = (int) DB::connection('sharpfleet')
            ->table('vehicles')
            ->where('organisation_id', $organisationId)
            ->where('is_active', 1)
            ->count();

        $newVehiclesCount = max(0, $currentVehiclesCount - 1);

        if ($newVehiclesCount < 1) {
            return redirect('/app/sharpfleet/admin/vehicles/' . $vehicleId . '/archive/confirm')
                ->with('error', 'You cannot archive your last active vehicle while your subscription is active. Cancel your subscription first.');
        }

        try {
            // Update Stripe FIRST so we don't archive the vehicle if billing couldn't be updated.
            $sync = $this->stripeSubscriptionSync->syncVehicleQuantityToStripe($organisationId, $newVehiclesCount);

            $this->audit->logSubscriber($request, 'Billing: Stripe Subscription Updated', [
                'reason' => 'vehicle_archived',
                ...$sync,
            ]);

            $this->vehicleService->archiveVehicle($organisationId, $vehicleId);

            $beforePricing = $this->calculateMonthlyPrice($currentVehiclesCount);
            $afterPricing = $this->calculateMonthlyPrice($newVehiclesCount);

            $this->audit->logSubscriber($request, 'Billing: Vehicle Archived', [
                'vehicle_id' => $vehicleId,
                'vehicle_name' => (string) ($record->name ?? ''),
                'vehicles' => [
                    'from' => $currentVehiclesCount,
                    'to' => $newVehiclesCount,
                ],
                'monthly_estimate' => [
                    'from' => $beforePricing['monthlyPrice'],
                    'to' => $afterPricing['monthlyPrice'],
                    'from_breakdown' => $beforePricing['breakdown'],
                    'to_breakdown' => $afterPricing['breakdown'],
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('SharpFleet: failed syncing Stripe quantity before vehicle archive', [
                'organisation_id' => $organisationId,
                'vehicle_id' => $vehicleId,
                'exception' => $e->getMessage(),
            ]);

            $this->audit->logSubscriber($request, 'Billing: Stripe Subscription Update Failed', [
                'reason' => 'vehicle_archived',
                'vehicle_id' => $vehicleId,
                'to_quantity' => $newVehiclesCount,
                'exception' => $e->getMessage(),
            ]);

            return redirect('/app/sharpfleet/admin/vehicles/' . $vehicleId . '/archive/confirm')
                ->with('error', 'Unable to archive vehicle because billing could not be updated. Please try again or contact support.');
        } finally {
            $request->session()->forget(self::PENDING_ARCHIVE_SESSION_KEY);
        }

        return redirect('/app/sharpfleet/admin/vehicles')
            ->with('success', 'Vehicle archived. Subscription updated for your next invoice.');
    }

    /**
     * Cancel archive confirmation.
     */
    public function cancelArchive(Request $request, $vehicle)
    {
        $request->session()->forget(self::PENDING_ARCHIVE_SESSION_KEY);

        return redirect('/app/sharpfleet/admin/vehicles');
    }

    /**
     * Show create form
     */
    public function create(Request $request)
    {
        $fleetUser = $request->session()->get('sharpfleet.user');

        if (!$fleetUser || empty($fleetUser['organisation_id'])) {
            abort(403, 'No SharpFleet organisation context.');
        }

        $organisationId = (int) $fleetUser['organisation_id'];

        $settingsService = new CompanySettingsService($organisationId);

        $branchService = new BranchService();
        $branchesEnabled = $branchService->branchesEnabled() && $branchService->vehiclesHaveBranchSupport();
        [$branchScopeEnabled, $accessibleBranchIds] = $this->resolveVehicleBranchScope($fleetUser, $organisationId, $branchService);

        $branches = $branchesEnabled ? $branchService->getBranches($organisationId) : collect();
        if ($branchScopeEnabled) {
            $branches = $branches->filter(fn ($b) => in_array((int) ($b->id ?? 0), $accessibleBranchIds, true))->values();
        }

        $defaultBranchId = null;
        if ($branchesEnabled) {
            if ($branchScopeEnabled) {
                $defaultBranchId = count($accessibleBranchIds) > 0 ? (int) $accessibleBranchIds[0] : null;
            } else {
                $defaultBranchId = $branchService->ensureDefaultBranch($organisationId);
            }
        }

        return view('sharpfleet.admin.vehicles.create', [
            'vehicleRegistrationTrackingEnabled' => $settingsService->vehicleRegistrationTrackingEnabled(),
            'vehicleServicingTrackingEnabled' => $settingsService->vehicleServicingTrackingEnabled(),
            'companyDistanceUnit' => $settingsService->distanceUnit(),
            'branchesEnabled' => $branchesEnabled,
            'branches' => $branches,
            'defaultBranchId' => $defaultBranchId,
        ]);
    }

    /**
     * Confirmation step (subscribed orgs only) for adding a vehicle.
     */
    public function confirmCreate(Request $request)
    {
        $fleetUser = $request->session()->get('sharpfleet.user');

        if (!$fleetUser || empty($fleetUser['organisation_id'])) {
            abort(403, 'No SharpFleet organisation context.');
        }

        $organisationId = (int) $fleetUser['organisation_id'];

        $entitlements = new EntitlementService($fleetUser);
        $isSubscribed = $entitlements->isSubscriptionActive();

        if (!$isSubscribed) {
            $request->session()->forget(self::PENDING_CREATE_SESSION_KEY);
            return redirect('/app/sharpfleet/admin/vehicles/create');
        }

        $pending = $request->session()->get(self::PENDING_CREATE_SESSION_KEY);
        $pendingOrgId = (int) ($pending['organisation_id'] ?? 0);
        $pendingPayload = is_array($pending['payload'] ?? null) ? $pending['payload'] : null;

        if ($pendingOrgId !== $organisationId || !$pendingPayload) {
            return redirect('/app/sharpfleet/admin/vehicles/create');
        }

        $currentVehiclesCount = (int) DB::connection('sharpfleet')
            ->table('vehicles')
            ->where('organisation_id', $organisationId)
            ->where('is_active', 1)
            ->count();

        $newVehiclesCount = $currentVehiclesCount + 1;
        $newPricing = $this->calculateMonthlyPrice($newVehiclesCount);

        return view('sharpfleet.admin.vehicles.confirm-create', [
            'pendingVehicleName' => (string) ($pendingPayload['name'] ?? ''),
            'currentVehiclesCount' => $currentVehiclesCount,
            'newVehiclesCount' => $newVehiclesCount,
            'newMonthlyPrice' => $newPricing['monthlyPrice'],
            'newMonthlyPriceBreakdown' => $newPricing['breakdown'],
            'requiresContactForPricing' => $newPricing['requiresContact'],
        ]);
    }

    /**
     * Finalize creation after acknowledgement (subscribed orgs only).
     */
    public function confirmStore(Request $request)
    {
        $fleetUser = $request->session()->get('sharpfleet.user');

        if (!$fleetUser || empty($fleetUser['organisation_id'])) {
            abort(403, 'No SharpFleet organisation context.');
        }

        $organisationId = (int) $fleetUser['organisation_id'];

        $entitlements = new EntitlementService($fleetUser);
        $isSubscribed = $entitlements->isSubscriptionActive();

        if (!$isSubscribed) {
            $request->session()->forget(self::PENDING_CREATE_SESSION_KEY);
            return redirect('/app/sharpfleet/admin/vehicles/create');
        }

        $pending = $request->session()->get(self::PENDING_CREATE_SESSION_KEY);
        $pendingOrgId = (int) ($pending['organisation_id'] ?? 0);
        $payload = is_array($pending['payload'] ?? null) ? $pending['payload'] : null;

        if ($pendingOrgId !== $organisationId || !$payload) {
            return redirect('/app/sharpfleet/admin/vehicles/create');
        }

        $request->validate([
            'ack_subscription_price_increase' => ['required', 'accepted'],
        ]);

        // Re-check uniqueness for rego (if present) to prevent race conditions.
        $rego = trim((string) ($payload['registration_number'] ?? ''));
        $isRoadRegistered = (int) ($payload['is_road_registered'] ?? 0) === 1;
        if ($isRoadRegistered && $rego !== '') {
            $exists = DB::connection('sharpfleet')
                ->table('vehicles')
                ->where('organisation_id', $organisationId)
                ->where('registration_number', $rego)
                ->exists();

            if ($exists) {
                $request->session()->forget(self::PENDING_CREATE_SESSION_KEY);
                return redirect('/app/sharpfleet/admin/vehicles/create')
                    ->withErrors(['registration_number' => 'Registration number already exists for this organisation.'])
                    ->withInput($payload);
            }
        }

        $beforeVehiclesCount = (int) DB::connection('sharpfleet')
            ->table('vehicles')
            ->where('organisation_id', $organisationId)
            ->where('is_active', 1)
            ->count();

        $beforePricing = $this->calculateMonthlyPrice($beforeVehiclesCount);

        $vehicleId = (int) $this->vehicleService->createVehicle($organisationId, $payload);

        $afterVehiclesCount = (int) DB::connection('sharpfleet')
            ->table('vehicles')
            ->where('organisation_id', $organisationId)
            ->where('is_active', 1)
            ->count();

        $afterPricing = $this->calculateMonthlyPrice($afterVehiclesCount);

        $this->audit->logSubscriber($request, 'Billing: Vehicle Added', [
            'vehicle_id' => $vehicleId,
            'vehicle_name' => (string) ($payload['name'] ?? ''),
            'vehicles' => [
                'from' => $beforeVehiclesCount,
                'to' => $afterVehiclesCount,
            ],
            'monthly_estimate' => [
                'from' => $beforePricing['monthlyPrice'],
                'to' => $afterPricing['monthlyPrice'],
                'from_breakdown' => $beforePricing['breakdown'],
                'to_breakdown' => $afterPricing['breakdown'],
            ],
        ]);

        // Sync subscription quantity to include this new vehicle on the next invoice.
        try {
            $sync = $this->stripeSubscriptionSync->syncVehicleQuantityToStripe($organisationId, $afterVehiclesCount);

            $this->audit->logSubscriber($request, 'Billing: Stripe Subscription Updated', [
                'reason' => 'vehicle_added',
                ...$sync,
            ]);
        } catch (\Throwable $e) {
            Log::error('SharpFleet: failed syncing Stripe quantity after vehicle create', [
                'organisation_id' => $organisationId,
                'exception' => $e->getMessage(),
            ]);

            $this->audit->logSubscriber($request, 'Billing: Stripe Subscription Update Failed', [
                'reason' => 'vehicle_added',
                'vehicle_id' => $vehicleId,
                'to_quantity' => $afterVehiclesCount,
                'exception' => $e->getMessage(),
            ]);
        }

        $request->session()->forget(self::PENDING_CREATE_SESSION_KEY);

        return redirect('/app/sharpfleet/admin/vehicles')
            ->with('success', 'Asset added successfully.');
    }

    /**
     * Cancel confirmation and return to the create form with inputs restored.
     */
    public function cancelCreate(Request $request)
    {
        $pending = $request->session()->get(self::PENDING_CREATE_SESSION_KEY);
        $payload = is_array($pending['payload'] ?? null) ? $pending['payload'] : [];

        $request->session()->forget(self::PENDING_CREATE_SESSION_KEY);

        return redirect('/app/sharpfleet/admin/vehicles/create')
            ->withInput($payload);
    }

    /**
     * Store new vehicle / asset
     */
    public function store(Request $request)
    {
        $fleetUser = $request->session()->get('sharpfleet.user');

        if (!$fleetUser || empty($fleetUser['organisation_id'])) {
            abort(403, 'No SharpFleet organisation context.');
        }

        $organisationId = (int) $fleetUser['organisation_id'];

        $entitlements = new EntitlementService($fleetUser);
        $isSubscribed = $entitlements->isSubscriptionActive();

        $settingsService = new CompanySettingsService($organisationId);
        $regoTrackingEnabled = $settingsService->vehicleRegistrationTrackingEnabled();
        $serviceTrackingEnabled = $settingsService->vehicleServicingTrackingEnabled();

        /*
         |----------------------------------------------------------
         | VALIDATION
         |----------------------------------------------------------
         */
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],

            // Branch (optional; requires DB columns)
            'branch_id' => ['nullable', 'integer'],

            // Registration tracking (optional, company-controlled)
            'is_road_registered' => [Rule::requiredIf(fn () => $regoTrackingEnabled), 'boolean'],

            'registration_number' => [
                'nullable',
                'string',
                'max:20',
                Rule::requiredIf(fn () => $regoTrackingEnabled && $request->input('is_road_registered') == 1),
            ],

            'tracking_mode' => ['required', Rule::in(['distance', 'hours', 'none'])],

            'make' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],
            'variant' => ['nullable', 'string', 'max:100'],
            'first_registration_year' => ['nullable', 'integer', 'min:1900', 'max:2100'],

            'vehicle_type' => ['nullable', 'in:sedan,hatch,suv,van,bus,ute,ex,dozer,other'],
            'vehicle_class' => ['nullable', 'string', 'max:100'],

            'wheelchair_accessible' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],

            // Optional starting odometer for first trip autofill
            'starting_km' => ['nullable', 'integer', 'min:0'],

            // Admin-managed registration + servicing details (stored on vehicles table)
            'registration_expiry' => ['nullable', 'date'],
            'service_due_date' => ['nullable', 'date'],
            'service_due_km' => ['nullable', 'integer', 'min:0'],
            'last_service_date' => ['nullable', 'date'],
            'last_service_km' => ['nullable', 'integer', 'min:0'],

            // Service status (optional; requires DB columns)
            'is_in_service' => ['nullable', 'boolean'],
            'out_of_service_reason' => ['nullable', 'string', 'max:50'],
            'out_of_service_note' => ['nullable', 'string', 'max:255'],
        ]);

        $branchService = new BranchService();
        $branchesEnabled = $branchService->branchesEnabled() && $branchService->vehiclesHaveBranchSupport();
        [$branchScopeEnabled, $accessibleBranchIds] = $this->resolveVehicleBranchScope($fleetUser, $organisationId, $branchService);
        if ($branchesEnabled) {
            $branchId = (int) ($validated['branch_id'] ?? 0);
            if ($branchScopeEnabled) {
                if ($branchId <= 0) {
                    $validated['branch_id'] = count($accessibleBranchIds) > 0 ? (int) $accessibleBranchIds[0] : null;
                } elseif (!in_array($branchId, $accessibleBranchIds, true)) {
                    return back()
                        ->withErrors(['branch_id' => 'Please select a valid branch.'])
                        ->withInput();
                }
            }

            $branchId = (int) ($validated['branch_id'] ?? 0);
            if ($branchId <= 0) {
                $defaultBranchId = (int) ($branchService->ensureDefaultBranch($organisationId) ?? 0);
                $validated['branch_id'] = $defaultBranchId > 0 ? $defaultBranchId : null;
            } else {
                $branch = $branchService->getBranch($organisationId, $branchId);
                if (!$branch) {
                    return back()
                        ->withErrors(['branch_id' => 'Please select a valid branch.'])
                        ->withInput();
                }
                $validated['branch_id'] = $branchId;
            }
        } else {
            unset($validated['branch_id']);
        }

        $wantsServiceStatus = array_key_exists('is_in_service', $validated) && ((int) ($validated['is_in_service'] ?? 1) === 0);
        if ($wantsServiceStatus) {
            $allowedReasons = ['Service', 'Repair', 'Accident', 'Inspection', 'Other'];
            $reason = trim((string) ($validated['out_of_service_reason'] ?? ''));
            if ($reason === '' || !in_array($reason, $allowedReasons, true)) {
                return back()
                    ->withErrors([
                        'out_of_service_reason' => 'Reason is required (Service, Repair, Accident, Inspection, Other).',
                    ])
                    ->withInput();
            }

            if (!Schema::connection('sharpfleet')->hasColumn('vehicles', 'is_in_service')) {
                return back()
                    ->withErrors([
                        'is_in_service' => "Out-of-service status can't be saved yet because the database is missing column vehicles.is_in_service. Run SQL: ALTER TABLE vehicles ADD COLUMN is_in_service TINYINT(1) NOT NULL DEFAULT 1;",
                    ])
                    ->withInput();
            }
        }

        // If the DB schema doesn't have the column yet, block only when the user tried to set it.
        if (
            array_key_exists('starting_km', $validated) &&
            $validated['starting_km'] !== null &&
            !Schema::connection('sharpfleet')->hasColumn('vehicles', 'starting_km')
        ) {
            return back()
                ->withErrors([
                    'starting_km' => "Starting reading can't be saved yet because the database is missing column vehicles.starting_km. Run: ALTER TABLE vehicles ADD COLUMN starting_km INT UNSIGNED NULL;",
                ])
                ->withInput();
        }

        if (
            array_key_exists('last_service_km', $validated) &&
            $validated['last_service_km'] !== null &&
            !Schema::connection('sharpfleet')->hasColumn('vehicles', 'last_service_km')
        ) {
            return back()
                ->withErrors([
                    'last_service_km' => "Last service reading can't be saved yet because the database is missing column vehicles.last_service_km. Run: ALTER TABLE vehicles ADD COLUMN last_service_km INT UNSIGNED NULL;",
                ])
                ->withInput();
        }

        if (
            array_key_exists('last_service_date', $validated) &&
            $validated['last_service_date'] !== null &&
            !Schema::connection('sharpfleet')->hasColumn('vehicles', 'last_service_date')
        ) {
            return back()
                ->withErrors([
                    'last_service_date' => "Last service date can't be saved yet because the database is missing column vehicles.last_service_date. Run: ALTER TABLE vehicles ADD COLUMN last_service_date DATE NULL;",
                ])
                ->withInput();
        }

        if (
            array_key_exists('last_service_km', $validated) &&
            $validated['last_service_km'] !== null &&
            !Schema::connection('sharpfleet')->hasColumn('vehicles', 'last_service_km')
        ) {
            return back()
                ->withErrors([
                    'last_service_km' => "Last service reading can't be saved yet because the database is missing column vehicles.last_service_km. Run: ALTER TABLE vehicles ADD COLUMN last_service_km INT UNSIGNED NULL;",
                ])
                ->withInput();
        }

        if (
            array_key_exists('last_service_date', $validated) &&
            $validated['last_service_date'] !== null &&
            !Schema::connection('sharpfleet')->hasColumn('vehicles', 'last_service_date')
        ) {
            return back()
                ->withErrors([
                    'last_service_date' => "Last service date can't be saved yet because the database is missing column vehicles.last_service_date. Run: ALTER TABLE vehicles ADD COLUMN last_service_date DATE NULL;",
                ])
                ->withInput();
        }

        if (
            array_key_exists('variant', $validated) &&
            $validated['variant'] !== null &&
            !Schema::connection('sharpfleet')->hasColumn('vehicles', 'variant')
        ) {
            return back()
                ->withErrors([
                    'variant' => "Variant can't be saved yet because the database is missing column vehicles.variant. Run: ALTER TABLE vehicles ADD COLUMN variant VARCHAR(100) NULL;",
                ])
                ->withInput();
        }

        if (
            array_key_exists('first_registration_year', $validated) &&
            $validated['first_registration_year'] !== null &&
            !Schema::connection('sharpfleet')->hasColumn('vehicles', 'first_registration_year')
        ) {
            return back()
                ->withErrors([
                    'first_registration_year' => "First registration year can't be saved yet because the database is missing column vehicles.first_registration_year. Run: ALTER TABLE vehicles ADD COLUMN first_registration_year SMALLINT UNSIGNED NULL;",
                ])
                ->withInput();
        }

        /*
         |----------------------------------------------------------
         | NORMALISE DATA
         |----------------------------------------------------------
         */
        $validated['organisation_id'] = $organisationId;
        $validated['is_road_registered'] = (int) ($validated['is_road_registered'] ?? 0);
        $validated['wheelchair_accessible'] = (int) ($validated['wheelchair_accessible'] ?? 0);

        // Always discard any ack field submitted from the form; acknowledgement happens on the confirmation page.
        unset($validated['ack_subscription_price_increase']);

        // If NOT road registered, force rego to NULL
        if ($validated['is_road_registered'] === 0) {
            $validated['registration_number'] = null;
        }

        // If registration tracking is disabled at company level, force registration fields off.
        if (!$regoTrackingEnabled) {
            $validated['is_road_registered'] = 0;
            $validated['registration_number'] = null;
            $validated['registration_expiry'] = null;
        }

        if (!$serviceTrackingEnabled) {
            $validated['service_due_date'] = null;
            $validated['service_due_km'] = null;
        }

        /*
         |----------------------------------------------------------
         | UNIQUE REGO CHECK (ONLY WHEN ROAD REGISTERED)
         |----------------------------------------------------------
         */
        if (
            $regoTrackingEnabled &&
            $validated['is_road_registered'] === 1 &&
            DB::connection('sharpfleet')
                ->table('vehicles')
                ->where('organisation_id', $organisationId)
                ->where('registration_number', $validated['registration_number'])
                ->exists()
        ) {
            return back()
                ->withErrors([
                    'registration_number' =>
                        'Registration number already exists for this organisation.',
                ])
                ->withInput();
        }

        /*
         |----------------------------------------------------------
         | CONFIRMATION STEP (SUBSCRIBED)
         |----------------------------------------------------------
         */
        if ($isSubscribed) {
            $request->session()->put(self::PENDING_CREATE_SESSION_KEY, [
                'organisation_id' => $organisationId,
                'payload' => $validated,
            ]);

            return redirect('/app/sharpfleet/admin/vehicles/create/confirm');
        }

        /*
         |----------------------------------------------------------
         | CREATE VEHICLE (NON-SUBSCRIBED)
         |----------------------------------------------------------
         */
        $this->vehicleService->createVehicle($organisationId, $validated);

        return redirect('/app/sharpfleet/admin/vehicles')
            ->with('success', 'Asset added successfully.');
    }

    /**
     * Show edit form (rego locked)
     */
    public function details(Request $request, $vehicle)
    {
        $fleetUser = $request->session()->get('sharpfleet.user');

        if (!$fleetUser || empty($fleetUser['organisation_id'])) {
            abort(403, 'No SharpFleet organisation context.');
        }

        $organisationId = (int) $fleetUser['organisation_id'];
        $vehicleId = (int) $vehicle;

        $record = $this->vehicleService
            ->getVehicleForOrganisation($organisationId, $vehicleId);

        if (!$record) {
            abort(404, 'Vehicle not found.');
        }

        $branchService = new BranchService();
        [$branchScopeEnabled, $accessibleBranchIds] = $this->resolveVehicleBranchScope($fleetUser, $organisationId, $branchService);
        if ($branchScopeEnabled) {
            $this->assertVehicleRecordInBranches($record, $accessibleBranchIds);
        }

        $settingsService = new CompanySettingsService($organisationId);
        $timezone = $settingsService->timezone();
        $dateFormat = $settingsService->dateFormat();

        $branchId = Schema::connection('sharpfleet')->hasColumn('vehicles', 'branch_id')
            ? (int) ($record->branch_id ?? 0)
            : 0;

        $distanceUnit = $settingsService->distanceUnitForBranch($branchId);
        if (
            Schema::connection('sharpfleet')->hasTable('branches')
            && Schema::connection('sharpfleet')->hasColumn('branches', 'distance_unit')
            && $branchId > 0
        ) {
            $branchUnit = DB::connection('sharpfleet')
                ->table('branches')
                ->where('organisation_id', $organisationId)
                ->where('id', $branchId)
                ->value('distance_unit');

            $branchUnit = strtolower(trim((string) ($branchUnit ?? '')));
            if (in_array($branchUnit, ['km', 'mi'], true)) {
                $distanceUnit = $branchUnit;
            }
        }

        $trackingMode = (string) ($record->tracking_mode ?? 'distance');
        $isHours = $trackingMode === 'hours';

        $convertDistance = function (?float $value) use ($distanceUnit) {
            if ($value === null) {
                return null;
            }
            if ($distanceUnit === 'mi') {
                return $value * 0.621371;
            }
            return $value;
        };

        $lastTrip = DB::connection('sharpfleet')
            ->table('trips')
            ->where('organisation_id', $organisationId)
            ->where('vehicle_id', $vehicleId)
            ->whereNotNull('end_km')
            ->orderByDesc('ended_at')
            ->first();

        $lastReadingRaw = null;
        if ($lastTrip && $lastTrip->end_km !== null) {
            $lastReadingRaw = (float) $lastTrip->end_km;
        } elseif (Schema::connection('sharpfleet')->hasColumn('vehicles', 'starting_km')) {
            $lastReadingRaw = $record->starting_km !== null ? (float) $record->starting_km : null;
        }

        $lastReadingDisplay = $isHours ? $lastReadingRaw : $convertDistance($lastReadingRaw);

        $sumDelta = function (?string $start, ?string $end) use ($organisationId, $vehicleId) {
            $query = DB::connection('sharpfleet')
                ->table('trips')
                ->where('organisation_id', $organisationId)
                ->where('vehicle_id', $vehicleId)
                ->whereNotNull('start_km')
                ->whereNotNull('end_km');

            if ($start) {
                $query->where('started_at', '>=', $start);
            }
            if ($end) {
                $query->where('started_at', '<=', $end);
            }

            return (float) ($query->selectRaw('SUM(CASE WHEN end_km >= start_km THEN end_km - start_km ELSE 0 END) as total')->value('total') ?? 0);
        };

        $now = \Carbon\Carbon::now($timezone);
        $weekStart = $now->copy()->startOfWeek();
        $monthStart = $now->copy()->startOfMonth();
        $yearStart = $now->copy()->startOfYear();

        $totalSince = $sumDelta(null, null);
        $totalWeek = $sumDelta($weekStart->toDateTimeString(), $now->toDateTimeString());
        $totalMonth = $sumDelta($monthStart->toDateTimeString(), $now->toDateTimeString());
        $totalYear = $sumDelta($yearStart->toDateTimeString(), $now->toDateTimeString());

        $formatTotal = function (float $value) use ($isHours, $convertDistance) {
            if ($isHours) {
                return round($value, 1);
            }
            return round((float) $convertDistance($value), 1);
        };

        $totals = [
            'since' => $formatTotal($totalSince),
            'week' => $formatTotal($totalWeek),
            'month' => $formatTotal($totalMonth),
            'year' => $formatTotal($totalYear),
        ];

        $drivers = DB::connection('sharpfleet')
            ->table('trips')
            ->join('users', 'trips.user_id', '=', 'users.id')
            ->selectRaw('trips.user_id, COUNT(*) as trip_count, CONCAT(users.first_name, " ", users.last_name) as driver_name')
            ->where('trips.organisation_id', $organisationId)
            ->where('trips.vehicle_id', $vehicleId)
            ->groupBy('trips.user_id', 'users.first_name', 'users.last_name')
            ->orderByDesc('trip_count')
            ->limit(3)
            ->get();

        $customers = collect();
        $settings = $settingsService->all();
        $customerEnabled = (bool) ($settings['customer']['enabled'] ?? false);
        $hasCustomersTable = Schema::connection('sharpfleet')->hasTable('customers');
        $hasCustomerName = Schema::connection('sharpfleet')->hasColumn('trips', 'customer_name');
        $hasCustomerId = Schema::connection('sharpfleet')->hasColumn('trips', 'customer_id');

        if ($customerEnabled && ($hasCustomerName || $hasCustomerId)) {
            $query = DB::connection('sharpfleet')
                ->table('trips')
                ->where('trips.organisation_id', $organisationId)
                ->where('trips.vehicle_id', $vehicleId);

            if ($hasCustomersTable && $hasCustomerId) {
                $query->leftJoin('customers', 'trips.customer_id', '=', 'customers.id');
                $query->selectRaw('COALESCE(customers.name, trips.customer_name) as customer_name_display, COUNT(*) as trip_count');
                $query->groupBy('customer_name_display');
            } else {
                $query->selectRaw('trips.customer_name as customer_name_display, COUNT(*) as trip_count');
                $query->groupBy('trips.customer_name');
            }

            $customers = $query
                ->orderByDesc('trip_count')
                ->limit(3)
                ->get();
        }

        $age = null;
        if (Schema::connection('sharpfleet')->hasColumn('vehicles', 'first_registration_year')) {
            $year = (int) ($record->first_registration_year ?? 0);
            if ($year > 0) {
                $start = \Carbon\Carbon::create($year, 1, 1, 0, 0, 0, $timezone);
                $diff = $start->diff($now);
                $age = [
                    'years' => $diff->y,
                    'months' => $diff->m,
                ];
            }
        }

        $faults = collect();
        if ($settingsService->faultsEnabled() && Schema::connection('sharpfleet')->hasTable('faults')) {
            $faults = DB::connection('sharpfleet')
                ->table('faults')
                ->leftJoin('users', 'faults.user_id', '=', 'users.id')
                ->select(
                    'faults.id',
                    'faults.title',
                    'faults.description',
                    'faults.severity',
                    'faults.status',
                    'faults.created_at',
                    DB::raw("CONCAT(users.first_name, ' ', users.last_name) as reporter_name")
                )
                ->where('faults.organisation_id', $organisationId)
                ->where('faults.vehicle_id', $vehicleId)
                ->orderByDesc('faults.created_at')
                ->limit(5)
                ->get();
        }

        $assignment = null;
        if (
            Schema::connection('sharpfleet')->hasColumn('vehicles', 'assignment_type') &&
            Schema::connection('sharpfleet')->hasColumn('vehicles', 'assigned_driver_id')
        ) {
            $assignmentType = (string) ($record->assignment_type ?? '');
            if ($assignmentType === 'permanent' && !empty($record->assigned_driver_id)) {
                $driver = DB::connection('sharpfleet')
                    ->table('users')
                    ->where('organisation_id', $organisationId)
                    ->where('id', (int) $record->assigned_driver_id)
                    ->first();
                $assignment = $driver ? trim((string) ($driver->first_name ?? '') . ' ' . (string) ($driver->last_name ?? '')) : 'Assigned driver';
            } else {
                $assignment = 'Not permanently assigned';
            }
        }

        return view('sharpfleet.admin.vehicles.details', [
            'vehicle' => $record,
            'trackingMode' => $trackingMode,
            'distanceUnit' => $distanceUnit,
            'lastReading' => $lastReadingDisplay,
            'totals' => $totals,
            'drivers' => $drivers,
            'customers' => $customers,
            'age' => $age,
            'faults' => $faults,
            'assignment' => $assignment,
            'dateFormat' => $dateFormat,
            'serviceDueDate' => $record->service_due_date ?? null,
            'serviceDueReading' => $record->service_due_km ?? null,
            'lastServiceDate' => $record->last_service_date ?? null,
            'lastServiceReading' => $record->last_service_km ?? null,
        ]);
    }

    /**
     * Show edit form (rego locked)
     */
    public function edit(Request $request, $vehicle)
    {
        $fleetUser = $request->session()->get('sharpfleet.user');

        if (!$fleetUser || empty($fleetUser['organisation_id'])) {
            abort(403, 'No SharpFleet organisation context.');
        }

        $organisationId = (int) $fleetUser['organisation_id'];
        $vehicleId = (int) $vehicle;

        $record = $this->vehicleService
            ->getVehicleForOrganisation($organisationId, $vehicleId);

        if (!$record) {
            abort(404, 'Vehicle not found.');
        }

        $settingsService = new CompanySettingsService($organisationId);

        $branchService = new BranchService();
        $branchesEnabled = $branchService->branchesEnabled() && $branchService->vehiclesHaveBranchSupport();
        [$branchScopeEnabled, $accessibleBranchIds] = $this->resolveVehicleBranchScope($fleetUser, $organisationId, $branchService);

        if ($branchScopeEnabled) {
            $this->assertVehicleRecordInBranches($record, $accessibleBranchIds);
        }

        $branches = $branchesEnabled ? $branchService->getBranches($organisationId) : collect();
        if ($branchScopeEnabled) {
            $branches = $branches->filter(fn ($b) => in_array((int) ($b->id ?? 0), $accessibleBranchIds, true))->values();
        }

        $defaultBranchId = null;
        if ($branchesEnabled) {
            if ($branchScopeEnabled) {
                $defaultBranchId = count($accessibleBranchIds) > 0 ? (int) $accessibleBranchIds[0] : null;
            } else {
                $defaultBranchId = $branchService->ensureDefaultBranch($organisationId);
            }
        }

        // Driver list for permanent allocation.
        // Support older/newer schemas and real-world data where driver accounts may not have role === 'driver'
        // but are flagged via users.is_driver.
        $hasIsDriverFlag = Schema::connection('sharpfleet')->hasColumn('users', 'is_driver');

        $drivers = DB::connection('sharpfleet')
            ->table('users')
            ->where('organisation_id', $organisationId)
            ->where(function ($q) use ($hasIsDriverFlag) {
                // Case-insensitive role match (covers 'driver', 'Driver', etc.)
                $q->whereRaw('LOWER(role) = ?', ['driver']);

                if ($hasIsDriverFlag) {
                    // Include any user explicitly marked as a driver (e.g. admin-as-driver).
                    $q->orWhere('is_driver', 1);
                }
            })
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return view('sharpfleet.admin.vehicles.edit', [
            'vehicle' => $record,
            'vehicleRegistrationTrackingEnabled' => $settingsService->vehicleRegistrationTrackingEnabled(),
            'vehicleServicingTrackingEnabled' => $settingsService->vehicleServicingTrackingEnabled(),
            'companyDistanceUnit' => $settingsService->distanceUnit(),
            'drivers' => $drivers,
            'branchesEnabled' => $branchesEnabled,
            'branches' => $branches,
            'defaultBranchId' => $defaultBranchId,
        ]);
    }

    /**
     * Update vehicle (rego locked)
     */
    public function update(Request $request, $vehicle)
    {
        $fleetUser = $request->session()->get('sharpfleet.user');

        if (!$fleetUser || empty($fleetUser['organisation_id'])) {
            abort(403, 'No SharpFleet organisation context.');
        }

        $organisationId = (int) $fleetUser['organisation_id'];
        $vehicleId = (int) $vehicle;

        $record = $this->vehicleService
            ->getVehicleForOrganisation($organisationId, $vehicleId);

        if (!$record) {
            abort(404, 'Vehicle not found.');
        }

        $settingsService = new CompanySettingsService($organisationId);
        $regoTrackingEnabled = $settingsService->vehicleRegistrationTrackingEnabled();
        $serviceTrackingEnabled = $settingsService->vehicleServicingTrackingEnabled();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'branch_id' => ['nullable', 'integer'],
            'make' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],
            'variant' => ['nullable', 'string', 'max:100'],
            'first_registration_year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'vehicle_type' => ['nullable', 'in:sedan,hatch,suv,van,bus,ute,ex,dozer,other'],
            'vehicle_class' => ['nullable', 'string', 'max:100'],
            'wheelchair_accessible' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],

            // Optional starting odometer for first trip autofill
            'starting_km' => ['nullable', 'integer', 'min:0'],

            // Admin-managed registration + servicing details (stored on vehicles table)
            'registration_expiry' => ['nullable', 'date'],
            'service_due_date' => ['nullable', 'date'],
            'service_due_km' => ['nullable', 'integer', 'min:0'],
            'last_service_date' => ['nullable', 'date'],
            'last_service_km' => ['nullable', 'integer', 'min:0'],

            // Service status (optional; requires DB columns)
            'is_in_service' => ['nullable', 'boolean'],
            'out_of_service_reason' => ['nullable', 'string', 'max:50'],
            'out_of_service_note' => ['nullable', 'string', 'max:255'],

            // Permanent assignment (optional; requires DB columns)
            'permanent_assignment' => ['nullable', 'boolean'],
            'assigned_driver_id' => ['nullable', 'integer'],
        ]);

        $branchService = new BranchService();
        $branchesEnabled = $branchService->branchesEnabled() && $branchService->vehiclesHaveBranchSupport();
        [$branchScopeEnabled, $accessibleBranchIds] = $this->resolveVehicleBranchScope($fleetUser, $organisationId, $branchService);
        if ($branchScopeEnabled) {
            $this->assertVehicleRecordInBranches($record, $accessibleBranchIds);
        }
        if ($branchesEnabled) {
            $branchId = (int) ($validated['branch_id'] ?? 0);
            if ($branchScopeEnabled) {
                if ($branchId <= 0) {
                    $validated['branch_id'] = count($accessibleBranchIds) > 0 ? (int) $accessibleBranchIds[0] : null;
                } elseif (!in_array($branchId, $accessibleBranchIds, true)) {
                    return back()
                        ->withErrors(['branch_id' => 'Please select a valid branch.'])
                        ->withInput();
                }
            }

            $branchId = (int) ($validated['branch_id'] ?? 0);
            if ($branchId <= 0) {
                $defaultBranchId = (int) ($branchService->ensureDefaultBranch($organisationId) ?? 0);
                $validated['branch_id'] = $defaultBranchId > 0 ? $defaultBranchId : null;
            } else {
                $branch = $branchService->getBranch($organisationId, $branchId);
                if (!$branch) {
                    return back()
                        ->withErrors(['branch_id' => 'Please select a valid branch.'])
                        ->withInput();
                }
                $validated['branch_id'] = $branchId;
            }
        } else {
            unset($validated['branch_id']);
        }

        $wantsPermanentAssignment = (int) ($validated['permanent_assignment'] ?? 0) === 1;
        $vehiclesHaveAssignment = Schema::connection('sharpfleet')->hasColumn('vehicles', 'assignment_type')
            && Schema::connection('sharpfleet')->hasColumn('vehicles', 'assigned_driver_id');

        if ($wantsPermanentAssignment && !$vehiclesHaveAssignment) {
            return back()
                ->withErrors([
                    'permanent_assignment' => "Permanent assignment can't be saved yet because the database is missing vehicles.assignment_type and/or vehicles.assigned_driver_id. Run SQL (phpMyAdmin): ALTER TABLE vehicles ADD COLUMN assignment_type VARCHAR(20) NOT NULL DEFAULT 'none', ADD COLUMN assigned_driver_id INT UNSIGNED NULL;",
                ])
                ->withInput();
        }

        if ($wantsPermanentAssignment) {
            $assignedDriverId = (int) ($validated['assigned_driver_id'] ?? 0);
            if ($assignedDriverId <= 0) {
                return back()
                    ->withErrors([
                        'assigned_driver_id' => 'Driver is required when permanent assignment is enabled.',
                    ])
                    ->withInput();
            }

            $isValidDriver = DB::connection('sharpfleet')
                ->table('users')
                ->where('organisation_id', $organisationId)
                ->where('id', $assignedDriverId)
                ->where(function ($q) {
                    $q->whereRaw('LOWER(role) = ?', ['driver']);

                    if (Schema::connection('sharpfleet')->hasColumn('users', 'is_driver')) {
                        $q->orWhere('is_driver', 1);
                    }
                })
                ->exists();

            if (!$isValidDriver) {
                return back()
                    ->withErrors([
                        'assigned_driver_id' => 'Selected driver is invalid.',
                    ])
                    ->withInput();
            }

            // Normalize for storage.
            $validated['assignment_type'] = 'permanent';
            $validated['assigned_driver_id'] = $assignedDriverId;
        } else {
            // Clearing assignment
            if ($vehiclesHaveAssignment) {
                $validated['assignment_type'] = 'none';
                $validated['assigned_driver_id'] = null;
            }
        }

        unset($validated['permanent_assignment']);

        $wantsServiceStatus = array_key_exists('is_in_service', $validated) && ((int) ($validated['is_in_service'] ?? 1) === 0);
        if ($wantsServiceStatus) {
            $allowedReasons = ['Service', 'Repair', 'Accident', 'Inspection', 'Other'];
            $reason = trim((string) ($validated['out_of_service_reason'] ?? ''));
            if ($reason === '' || !in_array($reason, $allowedReasons, true)) {
                return back()
                    ->withErrors([
                        'out_of_service_reason' => 'Reason is required (Service, Repair, Accident, Inspection, Other).',
                    ])
                    ->withInput();
            }

            if (!Schema::connection('sharpfleet')->hasColumn('vehicles', 'is_in_service')) {
                return back()
                    ->withErrors([
                        'is_in_service' => "Out-of-service status can't be saved yet because the database is missing column vehicles.is_in_service. Run SQL: ALTER TABLE vehicles ADD COLUMN is_in_service TINYINT(1) NOT NULL DEFAULT 1;",
                    ])
                    ->withInput();
            }
        }

        // If they are putting the vehicle back in service, we still require the DB column to exist.
        if (array_key_exists('is_in_service', $validated) && ((int) ($validated['is_in_service'] ?? 1) === 1)) {
            if (!Schema::connection('sharpfleet')->hasColumn('vehicles', 'is_in_service')) {
                unset($validated['is_in_service'], $validated['out_of_service_reason'], $validated['out_of_service_note']);
            }
        }

        if (
            array_key_exists('starting_km', $validated) &&
            $validated['starting_km'] !== null &&
            !Schema::connection('sharpfleet')->hasColumn('vehicles', 'starting_km')
        ) {
            return back()
                ->withErrors([
                    'starting_km' => "Starting reading can't be saved yet because the database is missing column vehicles.starting_km. Run: ALTER TABLE vehicles ADD COLUMN starting_km INT UNSIGNED NULL;",
                ])
                ->withInput();
        }

        $validated['wheelchair_accessible'] =
            (int) ($validated['wheelchair_accessible'] ?? 0);

        if (!$regoTrackingEnabled) {
            $validated['registration_expiry'] = null;
        }

        if (!$serviceTrackingEnabled) {
            $validated['service_due_date'] = null;
            $validated['service_due_km'] = null;
        }

        $this->vehicleService->updateVehicle(
            $organisationId,
            $vehicleId,
            $validated
        );

        return redirect('/app/sharpfleet/admin/vehicles')
            ->with('success', 'Vehicle updated.');
    }

    /**
     * Archive vehicle (soft archive)
     */
    public function archive(Request $request, $vehicle)
    {
        $fleetUser = $request->session()->get('sharpfleet.user');

        if (!$fleetUser || empty($fleetUser['organisation_id'])) {
            abort(403, 'No SharpFleet organisation context.');
        }

        $organisationId = (int) $fleetUser['organisation_id'];
        $vehicleId = (int) $vehicle;

        $branchService = new BranchService();
        [$branchScopeEnabled, $accessibleBranchIds] = $this->resolveVehicleBranchScope($fleetUser, $organisationId, $branchService);

        $record = $this->vehicleService
            ->getVehicleForOrganisation($organisationId, $vehicleId);

        if (!$record) {
            abort(404, 'Vehicle not found.');
        }

        if ($branchScopeEnabled) {
            $this->assertVehicleRecordInBranches($record, $accessibleBranchIds);
        }


        $entitlements = new EntitlementService($fleetUser);
        if ($entitlements->isSubscriptionActive()) {
            $request->session()->put(self::PENDING_ARCHIVE_SESSION_KEY, [
                'organisation_id' => $organisationId,
                'vehicle_id' => $vehicleId,
                'vehicle_name' => (string) ($record->name ?? ''),
            ]);

            return redirect('/app/sharpfleet/admin/vehicles/' . $vehicleId . '/archive/confirm');
        }

        $this->vehicleService->archiveVehicle(
            $organisationId,
            $vehicleId
        );

        return redirect('/app/sharpfleet/admin/vehicles')
            ->with('success', 'Vehicle archived.');
    }

    private function calculateMonthlyPrice(int $vehiclesCount): array
    {
        $vehiclesCount = max(0, $vehiclesCount);

        $tier1Vehicles = min($vehiclesCount, 10);
        $tier2Vehicles = max(0, $vehiclesCount - 10);

        $tier1Price = 3.50;
        $tier2Price = 2.50;

        $monthlyPrice = ($tier1Vehicles * $tier1Price) + ($tier2Vehicles * $tier2Price);
        $requiresContact = $vehiclesCount > 20;

        $breakdown = sprintf(
            '%d × $%.2f + %d × $%.2f',
            $tier1Vehicles,
            $tier1Price,
            $tier2Vehicles,
            $tier2Price
        );

        return [
            'monthlyPrice' => $monthlyPrice,
            'breakdown' => $breakdown,
            'requiresContact' => $requiresContact,
        ];
    }

    /**
     * Company admins bypass branch restrictions; branch admins are restricted to accessible branches.
     * Returns [branchScopeEnabled, accessibleBranchIds].
     */
    private function resolveVehicleBranchScope(array $fleetUser, int $organisationId, BranchService $branchService): array
    {
        $bypassBranchRestrictions = Roles::bypassesBranchRestrictions($fleetUser);
        $branchAccessEnabled = $branchService->branchesEnabled()
            && $branchService->vehiclesHaveBranchSupport()
            && $branchService->userBranchAccessEnabled();

        $branchScopeEnabled = $branchAccessEnabled && !$bypassBranchRestrictions;
        $accessibleBranchIds = $branchScopeEnabled
            ? $branchService->getAccessibleBranchIdsForUser($organisationId, (int) ($fleetUser['id'] ?? 0))
            : [];

        if ($branchScopeEnabled && count($accessibleBranchIds) === 0) {
            abort(403, 'No branch access.');
        }

        $accessibleBranchIds = array_values(array_unique(array_map('intval', $accessibleBranchIds)));

        return [$branchScopeEnabled, $accessibleBranchIds];
    }

    private function assertVehicleRecordInBranches(object $record, array $accessibleBranchIds): void
    {
        if (!Schema::connection('sharpfleet')->hasColumn('vehicles', 'branch_id')) {
            return;
        }

        $branchId = (int) ($record->branch_id ?? 0);
        if ($branchId <= 0 || !in_array($branchId, $accessibleBranchIds, true)) {
            abort(403, 'No branch access.');
        }
    }
}
