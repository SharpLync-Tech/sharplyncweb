<?php

namespace App\Http\Controllers\SharpFleet\Admin;

use App\Http\Controllers\Controller;
use App\Services\SharpFleet\AuditLogService;
use App\Services\SharpFleet\CompanySettingsService;
use App\Services\SharpFleet\EntitlementService;
use App\Services\SharpFleet\StripeSubscriptionSyncService;
use App\Services\SharpFleet\VehicleService;
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

        $entitlements = new EntitlementService($fleetUser);
        $isSubscribed = $entitlements->isSubscriptionActive();

        $vehicles = $this->vehicleService->getAvailableVehicles($organisationId);

        $activeTrips = DB::connection('sharpfleet')
            ->table('trips')
            ->join('users', 'trips.user_id', '=', 'users.id')
            ->where('trips.organisation_id', $organisationId)
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

        return view('sharpfleet.admin.vehicles.create', [
            'vehicleRegistrationTrackingEnabled' => $settingsService->vehicleRegistrationTrackingEnabled(),
            'vehicleServicingTrackingEnabled' => $settingsService->vehicleServicingTrackingEnabled(),
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

            'vehicle_type' => ['nullable', 'in:sedan,hatch,suv,van,bus,other'],
            'vehicle_class' => ['nullable', 'string', 'max:100'],

            'wheelchair_accessible' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],

            // Optional starting odometer for first trip autofill
            'starting_km' => ['nullable', 'integer', 'min:0'],

            // Admin-managed registration + servicing details (stored on vehicles table)
            'registration_expiry' => ['nullable', 'date'],
            'service_due_date' => ['nullable', 'date'],
            'service_due_km' => ['nullable', 'integer', 'min:0'],

            // Service status (optional; requires DB columns)
            'is_in_service' => ['nullable', 'boolean'],
            'out_of_service_reason' => ['nullable', 'string', 'max:50'],
            'out_of_service_note' => ['nullable', 'string', 'max:255'],
        ]);

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

        $drivers = DB::connection('sharpfleet')
            ->table('users')
            ->where('organisation_id', $organisationId)
            ->where(function ($q) {
                $q
                    ->where(function ($qq) {
                        $qq
                            ->where('role', 'driver')
                            ->where(function ($q2) {
                                $q2->whereNull('is_driver')->orWhere('is_driver', 1);
                            });
                    })
                    ->orWhere(function ($qq) {
                        $qq
                            ->where('role', 'admin')
                            ->where('is_driver', 1);
                    });
            })
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return view('sharpfleet.admin.vehicles.edit', [
            'vehicle' => $record,
            'vehicleRegistrationTrackingEnabled' => $settingsService->vehicleRegistrationTrackingEnabled(),
            'vehicleServicingTrackingEnabled' => $settingsService->vehicleServicingTrackingEnabled(),
            'drivers' => $drivers,
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
            'make' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],
            'vehicle_type' => ['nullable', 'in:sedan,hatch,suv,van,bus,other'],
            'vehicle_class' => ['nullable', 'string', 'max:100'],
            'wheelchair_accessible' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],

            // Optional starting odometer for first trip autofill
            'starting_km' => ['nullable', 'integer', 'min:0'],

            // Admin-managed registration + servicing details (stored on vehicles table)
            'registration_expiry' => ['nullable', 'date'],
            'service_due_date' => ['nullable', 'date'],
            'service_due_km' => ['nullable', 'integer', 'min:0'],

            // Service status (optional; requires DB columns)
            'is_in_service' => ['nullable', 'boolean'],
            'out_of_service_reason' => ['nullable', 'string', 'max:50'],
            'out_of_service_note' => ['nullable', 'string', 'max:255'],

            // Permanent assignment (optional; requires DB columns)
            'permanent_assignment' => ['nullable', 'boolean'],
            'assigned_driver_id' => ['nullable', 'integer'],
        ]);

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
                    $q
                        ->where(function ($qq) {
                            $qq
                                ->where('role', 'driver')
                                ->where(function ($q2) {
                                    $q2->whereNull('is_driver')->orWhere('is_driver', 1);
                                });
                        })
                        ->orWhere(function ($qq) {
                            $qq
                                ->where('role', 'admin')
                                ->where('is_driver', 1);
                        });
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

        $record = $this->vehicleService
            ->getVehicleForOrganisation($organisationId, $vehicleId);

        if (!$record) {
            abort(404, 'Vehicle not found.');
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
}
