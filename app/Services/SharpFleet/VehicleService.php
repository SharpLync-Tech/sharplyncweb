<?php

namespace App\Services\SharpFleet;

use Illuminate\Support\Facades\DB;

class VehicleService
{
    /**
     * Get all active vehicles for an organisation
     */
    public function getAvailableVehicles(int $organisationId)
    {
        return DB::connection('sharpfleet')
            ->table('vehicles')
            ->where('organisation_id', $organisationId)
            ->where('is_active', 1)
            ->orderBy('name')
            ->get();
    }

    public function createVehicle(array $data)
    {
        // Not implemented yet
    }

    public function archiveVehicle(int $vehicleId)
    {
        // Not implemented yet
    }
}
