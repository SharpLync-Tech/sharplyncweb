<?php

namespace App\Http\Controllers\SharpFleet\Admin;

use App\Http\Controllers\Controller;
use App\Services\SharpFleet\VehicleService;
use Illuminate\Http\Request;

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

    public function store()
    {
        // Not implemented yet
        abort(501);
    }

    public function archive($vehicle)
    {
        // Not implemented yet
        abort(501);
    }
}
