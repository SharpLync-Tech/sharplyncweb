<?php

namespace App\Http\Controllers\SharpFleet\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SharpFleet\Admin\Vehicles\StoreVehicleRequest;
use App\Models\SharpFleet\Vehicle;

class VehicleController extends Controller
{
    public function index()
    {
        $orgId = session('sharpfleet.user.organisation_id');

        $vehicles = Vehicle::query()
            ->where('organisation_id', $orgId)
            ->orderBy('is_active', 'desc')
            ->orderBy('name')
            ->get();

        return view('sharpfleet.admin.vehicles.index', compact('vehicles'));
    }

    public function store(StoreVehicleRequest $request)
    {
        $data = $request->validated();

        // Attach org
        $data['organisation_id'] = session('sharpfleet.user.organisation_id');

        // Normalise booleans
        $data['is_road_registered'] = (int) $data['is_road_registered'];
        $data['wheelchair_accessible'] = (int) ($data['wheelchair_accessible'] ?? 0);

        // If not road registered, ensure rego is NULL
        if ($data['is_road_registered'] === 0) {
            $data['registration_number'] = null;
        }

        Vehicle::create($data);

        return redirect('/app/sharpfleet/admin/vehicles')
            ->with('success', 'Asset added successfully.');
    }

    public function archive($vehicle)
    {
        $orgId = session('sharpfleet.user.organisation_id');

        $v = Vehicle::query()
            ->where('organisation_id', $orgId)
            ->where('id', $vehicle)
            ->firstOrFail();

        $v->is_active = 0;
        $v->save();

        return redirect('/app/sharpfleet/admin/vehicles')
            ->with('success', 'Asset archived.');
    }
}
