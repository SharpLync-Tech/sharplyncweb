<?php

namespace App\Services\SharpFleet;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

    /**
     * Get a vehicle by id for an organisation (active or archived)
     */
    public function getVehicleForOrganisation(int $organisationId, int $vehicleId)
    {
        return DB::connection('sharpfleet')
            ->table('vehicles')
            ->where('organisation_id', $organisationId)
            ->where('id', $vehicleId)
            ->first();
    }

    /**
     * Create a vehicle for an organisation
     */
    public function createVehicle(int $organisationId, array $data): int
    {
        $hasStartingKm = Schema::connection('sharpfleet')->hasColumn('vehicles', 'starting_km');
        $hasRegistrationExpiry = Schema::connection('sharpfleet')->hasColumn('vehicles', 'registration_expiry');
        $hasServiceDueDate = Schema::connection('sharpfleet')->hasColumn('vehicles', 'service_due_date');
        $hasServiceDueKm = Schema::connection('sharpfleet')->hasColumn('vehicles', 'service_due_km');

        return (int) DB::connection('sharpfleet')
            ->table('vehicles')
            ->insertGetId([
                'organisation_id'      => $organisationId,
                'name'                 => $data['name'],
                'is_road_registered'    => (int) ($data['is_road_registered'] ?? 1),
                'registration_number'  => $data['registration_number'],
                'tracking_mode'         => $data['tracking_mode'] ?? 'distance',
                'make'                 => $data['make'] ?? null,
                'model'                => $data['model'] ?? null,
                'vehicle_type'         => $data['vehicle_type'],
                'vehicle_class'        => $data['vehicle_class'] ?? null,
                'wheelchair_accessible'=> !empty($data['wheelchair_accessible']) ? 1 : 0,
                'registration_expiry'   => $hasRegistrationExpiry ? ($data['registration_expiry'] ?? null) : null,
                'service_due_date'      => $hasServiceDueDate ? ($data['service_due_date'] ?? null) : null,
                'service_due_km'        => $hasServiceDueKm ? ($data['service_due_km'] ?? null) : null,
                'notes'                => $data['notes'] ?? null,
                'starting_km'           => $hasStartingKm ? ($data['starting_km'] ?? null) : null,
                'is_active'            => 1,
            ]);
    }

    /**
     * Update a vehicle (rego is intentionally NOT updated)
     */
    public function updateVehicle(int $organisationId, int $vehicleId, array $data): void
    {
        $hasStartingKm = Schema::connection('sharpfleet')->hasColumn('vehicles', 'starting_km');
        $hasRegistrationExpiry = Schema::connection('sharpfleet')->hasColumn('vehicles', 'registration_expiry');
        $hasServiceDueDate = Schema::connection('sharpfleet')->hasColumn('vehicles', 'service_due_date');
        $hasServiceDueKm = Schema::connection('sharpfleet')->hasColumn('vehicles', 'service_due_km');

        $update = [
            'name'                  => $data['name'],
            'make'                  => $data['make'] ?? null,
            'model'                 => $data['model'] ?? null,
            'vehicle_type'          => $data['vehicle_type'],
            'vehicle_class'         => $data['vehicle_class'] ?? null,
            'wheelchair_accessible' => !empty($data['wheelchair_accessible']) ? 1 : 0,
            'notes'                 => $data['notes'] ?? null,
            'starting_km'            => $hasStartingKm ? ($data['starting_km'] ?? null) : null,
        ];

        if ($hasRegistrationExpiry && array_key_exists('registration_expiry', $data)) {
            $update['registration_expiry'] = $data['registration_expiry'] ?? null;
        }

        if ($hasServiceDueDate && array_key_exists('service_due_date', $data)) {
            $update['service_due_date'] = $data['service_due_date'] ?? null;
        }

        if ($hasServiceDueKm && array_key_exists('service_due_km', $data)) {
            $update['service_due_km'] = $data['service_due_km'] ?? null;
        }

        DB::connection('sharpfleet')
            ->table('vehicles')
            ->where('organisation_id', $organisationId)
            ->where('id', $vehicleId)
            ->update($update);
    }

    /**
     * Archive a vehicle (soft archive)
     */
    public function archiveVehicle(int $organisationId, int $vehicleId): void
    {
        DB::connection('sharpfleet')
            ->table('vehicles')
            ->where('organisation_id', $organisationId)
            ->where('id', $vehicleId)
            ->update([
                'is_active' => 0,
            ]);
    }
}
