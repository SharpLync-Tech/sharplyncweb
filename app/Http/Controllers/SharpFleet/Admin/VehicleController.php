<?php

namespace App\Http\Controllers\SharpFleet\Admin;

use App\Http\Controllers\Controller;
use App\Services\SharpFleet\CompanySettingsService;
use App\Services\SharpFleet\VehicleService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class VehicleController extends Controller
{
    protected VehicleService $vehicleService;

    public function __construct(VehicleService $vehicleService)
    {
        $this->vehicleService = $vehicleService;
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
        ]);
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
     * Store new vehicle / asset
     */
    public function store(Request $request)
    {
        $fleetUser = $request->session()->get('sharpfleet.user');

        if (!$fleetUser || empty($fleetUser['organisation_id'])) {
            abort(403, 'No SharpFleet organisation context.');
        }

        $organisationId = (int) $fleetUser['organisation_id'];

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
         | CREATE VEHICLE
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

        return view('sharpfleet.admin.vehicles.edit', [
            'vehicle' => $record,
            'vehicleRegistrationTrackingEnabled' => $settingsService->vehicleRegistrationTrackingEnabled(),
            'vehicleServicingTrackingEnabled' => $settingsService->vehicleServicingTrackingEnabled(),
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

        $this->vehicleService->archiveVehicle(
            $organisationId,
            $vehicleId
        );

        return redirect('/app/sharpfleet/admin/vehicles')
            ->with('success', 'Vehicle archived.');
    }
}
