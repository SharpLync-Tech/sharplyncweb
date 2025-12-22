<?php

namespace App\Http\Controllers\SharpFleet\Admin;

use App\Http\Controllers\Controller;
use App\Services\SharpFleet\VehicleService;

class VehicleController extends Controller
{
    protected VehicleService $vehicleService;

    public function __construct(VehicleService $vehicleService)
    {
        $this->vehicleService = $vehicleService;
    }

    public function index()
    {
        // $this->vehicleService->getAvailableVehicles()
    }

    public function store()
    {
        // $this->vehicleService->createVehicle()
    }

    public function archive($vehicle)
    {
        // $this->vehicleService->archiveVehicle()
    }
}
