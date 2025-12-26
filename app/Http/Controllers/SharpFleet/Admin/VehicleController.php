<?php

namespace App\Http\Controllers\SharpFleet\Admin;

use App\Http\Controllers\Controller;
use App\Services\SharpFleet\VehicleService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

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

        $vehicles = $this->vehicleService->getAvailableVehicles(
            (int) $fleetUser['organisation_id']
        );

        return view('sharpfleet.admin.vehicles.index', [
            'vehicles' => $vehicles,
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

        return view('sharpfleet.admin.vehicles.create');
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

        /*
         |----------------------------------------------------------
         | VALIDATION
         |----------------------------------------------------------
         */
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],

            // Hidden input + checkbox ensures this always exists
            'is_road_registered' => ['required', 'boolean'],

            'registration_number' => [
                'nullable',
                'string',
                'max:20',
                Rule::requiredIf(fn () => $request->input('is_road_registered') == 1),
            ],

            'tracking_mode' => ['required', Rule::in(['distance', 'hours', 'none'])],

            'make' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],

            'vehicle_type' => ['nullable', 'in:sedan,hatch,suv,van,bus,other'],
            'vehicle_class' => ['nullable', 'string', 'max:100'],

            'wheelchair_accessible' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        /*
         |----------------------------------------------------------
         | NORMALISE DATA
         |----------------------------------------------------------
         */
        $validated['organisation_id'] = $organisationId;
        $validated['is_road_registered'] = (int) $validated['is_road_registered'];
        $validated['wheelchair_accessible'] = (int) ($validated['wheelchair_accessible'] ?? 0);

        // If NOT road registered, force rego to NULL
        if ($validated['is_road_registered'] === 0) {
            $validated['registration_number'] = null;
        }

        /*
         |----------------------------------------------------------
         | UNIQUE REGO CHECK (ONLY WHEN ROAD REGISTERED)
         |----------------------------------------------------------
         */
        if (
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

        return view('sharpfleet.admin.vehicles.edit', [
            'vehicle' => $record,
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

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'make' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],
            'vehicle_type' => ['nullable', 'in:sedan,hatch,suv,van,bus,other'],
            'vehicle_class' => ['nullable', 'string', 'max:100'],
            'wheelchair_accessible' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['wheelchair_accessible'] =
            (int) ($validated['wheelchair_accessible'] ?? 0);

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
