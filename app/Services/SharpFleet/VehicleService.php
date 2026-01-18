<?php

namespace App\Services\SharpFleet;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class VehicleService
{
    private ?array $vehicleTypeAllowedCache = null;

    private function getVehicleTypeAllowedValues(): ?array
    {
        if ($this->vehicleTypeAllowedCache !== null) {
            return $this->vehicleTypeAllowedCache;
        }

        try {
            $row = DB::connection('sharpfleet')->selectOne(
                "select column_type from information_schema.columns where table_schema = database() and table_name = 'vehicles' and column_name = 'vehicle_type'"
            );
            $columnType = isset($row->column_type) ? (string) $row->column_type : '';
            if (!str_starts_with($columnType, 'enum(')) {
                $this->vehicleTypeAllowedCache = null;
                return null;
            }
            preg_match_all("/'((?:\\\\'|[^'])*)'/", $columnType, $matches);
            $values = array_map(
                static fn ($val) => str_replace("\\'", "'", (string) $val),
                $matches[1] ?? []
            );
            $this->vehicleTypeAllowedCache = $values ?: null;
            return $this->vehicleTypeAllowedCache;
        } catch (\Throwable $e) {
            $this->vehicleTypeAllowedCache = null;
            return null;
        }
    }

    private function normalizeVehicleType(?string $value): ?string
    {
        $raw = strtolower(trim((string) $value));
        if ($raw === '') {
            return null;
        }

        $allowed = $this->getVehicleTypeAllowedValues();
        if ($allowed === null) {
            return $raw;
        }

        if (in_array($raw, $allowed, true)) {
            return $raw;
        }

        $aliasMap = [
            'ute' => ['ute', 'pickup', 'light_truck', 'truck', 'bakkie', 'other'],
            'pickup' => ['pickup', 'ute', 'light_truck', 'truck', 'bakkie', 'other'],
            'light_truck' => ['light_truck', 'pickup', 'ute', 'truck', 'bakkie', 'other'],
            'bakkie' => ['bakkie', 'ute', 'pickup', 'light_truck', 'truck', 'other'],
        ];

        if (isset($aliasMap[$raw])) {
            foreach ($aliasMap[$raw] as $candidate) {
                if (in_array($candidate, $allowed, true)) {
                    return $candidate;
                }
            }
        }

        if (in_array('other', $allowed, true)) {
            return 'other';
        }

        return $allowed[0] ?? null;
    }
    /**
     * Get all active vehicles for an organisation
     */
    public function getAvailableVehicles(int $organisationId, ?array $branchIds = null)
    {
        $query = DB::connection('sharpfleet')
            ->table('vehicles')
            ->where('organisation_id', $organisationId)
            ->where('is_active', 1)
            ->when(
                $branchIds !== null && Schema::connection('sharpfleet')->hasColumn('vehicles', 'branch_id'),
                fn ($q) => $q->whereIn('branch_id', $branchIds)
            )
            ->orderBy('name')
            ;

        return $query->get();
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
        $hasBranchId = Schema::connection('sharpfleet')->hasColumn('vehicles', 'branch_id');
        $hasStartingKm = Schema::connection('sharpfleet')->hasColumn('vehicles', 'starting_km');
        $hasRegistrationExpiry = Schema::connection('sharpfleet')->hasColumn('vehicles', 'registration_expiry');
        $hasServiceDueDate = Schema::connection('sharpfleet')->hasColumn('vehicles', 'service_due_date');
        $hasServiceDueKm = Schema::connection('sharpfleet')->hasColumn('vehicles', 'service_due_km');
        $hasFirstRegistrationYear = Schema::connection('sharpfleet')->hasColumn('vehicles', 'first_registration_year');
        $hasVariant = Schema::connection('sharpfleet')->hasColumn('vehicles', 'variant');
        $hasLastServiceDate = Schema::connection('sharpfleet')->hasColumn('vehicles', 'last_service_date');
        $hasLastServiceKm = Schema::connection('sharpfleet')->hasColumn('vehicles', 'last_service_km');

        $hasIsInService = Schema::connection('sharpfleet')->hasColumn('vehicles', 'is_in_service');
        $hasOutOfServiceReason = Schema::connection('sharpfleet')->hasColumn('vehicles', 'out_of_service_reason');
        $hasOutOfServiceNote = Schema::connection('sharpfleet')->hasColumn('vehicles', 'out_of_service_note');
        $hasOutOfServiceAt = Schema::connection('sharpfleet')->hasColumn('vehicles', 'out_of_service_at');

        $isInService = isset($data['is_in_service']) ? (int) ($data['is_in_service'] ? 1 : 0) : 1;
        $outOfServiceReason = isset($data['out_of_service_reason']) ? trim((string) $data['out_of_service_reason']) : null;
        $outOfServiceNote = isset($data['out_of_service_note']) ? trim((string) $data['out_of_service_note']) : null;
        if ($outOfServiceReason === '') {
            $outOfServiceReason = null;
        }
        if ($outOfServiceNote === '') {
            $outOfServiceNote = null;
        }

        $vehicleType = $this->normalizeVehicleType($data['vehicle_type'] ?? null);

        return (int) DB::connection('sharpfleet')
            ->table('vehicles')
            ->insertGetId([
                'organisation_id'      => $organisationId,
                'name'                 => $data['name'],
                'branch_id'            => $hasBranchId ? ((($data['branch_id'] ?? null) === null || ($data['branch_id'] ?? null) === '') ? null : (int) $data['branch_id']) : null,
                'is_road_registered'    => (int) ($data['is_road_registered'] ?? 1),
                'registration_number'  => $data['registration_number'],
                'tracking_mode'         => $data['tracking_mode'] ?? 'distance',
                'make'                 => $data['make'] ?? null,
                'model'                => $data['model'] ?? null,
                'variant'              => $hasVariant ? ($data['variant'] ?? null) : null,
                'vehicle_type'         => $vehicleType,
                'vehicle_class'        => $data['vehicle_class'] ?? null,
                'wheelchair_accessible'=> !empty($data['wheelchair_accessible']) ? 1 : 0,
                'registration_expiry'   => $hasRegistrationExpiry ? ($data['registration_expiry'] ?? null) : null,
                'first_registration_year' => $hasFirstRegistrationYear ? ($data['first_registration_year'] ?? null) : null,
                'last_service_date'     => $hasLastServiceDate ? ($data['last_service_date'] ?? null) : null,
                'last_service_km'       => $hasLastServiceKm ? ($data['last_service_km'] ?? null) : null,
                'service_due_date'      => $hasServiceDueDate ? ($data['service_due_date'] ?? null) : null,
                'service_due_km'        => $hasServiceDueKm ? ($data['service_due_km'] ?? null) : null,
                'notes'                => $data['notes'] ?? null,
                'starting_km'           => $hasStartingKm ? ($data['starting_km'] ?? null) : null,
                'is_in_service'         => $hasIsInService ? $isInService : null,
                'out_of_service_reason' => ($hasOutOfServiceReason && $hasIsInService && $isInService === 0) ? $outOfServiceReason : null,
                'out_of_service_note'   => ($hasOutOfServiceNote && $hasIsInService && $isInService === 0) ? $outOfServiceNote : null,
                'out_of_service_at'     => ($hasOutOfServiceAt && $hasIsInService && $isInService === 0) ? now() : null,
                'is_active'            => 1,
            ]);
    }

    /**
     * Update a vehicle (rego is intentionally NOT updated)
     */
    public function updateVehicle(int $organisationId, int $vehicleId, array $data): void
    {
        $hasBranchId = Schema::connection('sharpfleet')->hasColumn('vehicles', 'branch_id');
        $hasStartingKm = Schema::connection('sharpfleet')->hasColumn('vehicles', 'starting_km');
        $hasRegistrationExpiry = Schema::connection('sharpfleet')->hasColumn('vehicles', 'registration_expiry');
        $hasServiceDueDate = Schema::connection('sharpfleet')->hasColumn('vehicles', 'service_due_date');
        $hasServiceDueKm = Schema::connection('sharpfleet')->hasColumn('vehicles', 'service_due_km');
        $hasFirstRegistrationYear = Schema::connection('sharpfleet')->hasColumn('vehicles', 'first_registration_year');
        $hasVariant = Schema::connection('sharpfleet')->hasColumn('vehicles', 'variant');
        $hasLastServiceDate = Schema::connection('sharpfleet')->hasColumn('vehicles', 'last_service_date');
        $hasLastServiceKm = Schema::connection('sharpfleet')->hasColumn('vehicles', 'last_service_km');

        $hasIsInService = Schema::connection('sharpfleet')->hasColumn('vehicles', 'is_in_service');
        $hasOutOfServiceReason = Schema::connection('sharpfleet')->hasColumn('vehicles', 'out_of_service_reason');
        $hasOutOfServiceNote = Schema::connection('sharpfleet')->hasColumn('vehicles', 'out_of_service_note');
        $hasOutOfServiceAt = Schema::connection('sharpfleet')->hasColumn('vehicles', 'out_of_service_at');

        $hasAssignmentType = Schema::connection('sharpfleet')->hasColumn('vehicles', 'assignment_type');
        $hasAssignedDriverId = Schema::connection('sharpfleet')->hasColumn('vehicles', 'assigned_driver_id');

        $vehicleType = $this->normalizeVehicleType($data['vehicle_type'] ?? null);

        $update = [
            'name'                  => $data['name'],
            'make'                  => $data['make'] ?? null,
            'model'                 => $data['model'] ?? null,
            'vehicle_type'          => $vehicleType,
            'vehicle_class'         => $data['vehicle_class'] ?? null,
            'wheelchair_accessible' => !empty($data['wheelchair_accessible']) ? 1 : 0,
            'notes'                 => $data['notes'] ?? null,
            'starting_km'            => $hasStartingKm ? ($data['starting_km'] ?? null) : null,
        ];

        if ($hasVariant && array_key_exists('variant', $data)) {
            $update['variant'] = $data['variant'] ?? null;
        }

        if ($hasLastServiceDate && array_key_exists('last_service_date', $data)) {
            $update['last_service_date'] = $data['last_service_date'] ?? null;
        }

        if ($hasLastServiceKm && array_key_exists('last_service_km', $data)) {
            $update['last_service_km'] = $data['last_service_km'] ?? null;
        }

        if ($hasFirstRegistrationYear && array_key_exists('first_registration_year', $data)) {
            $update['first_registration_year'] = $data['first_registration_year'] ?? null;
        }

        if ($hasBranchId && array_key_exists('branch_id', $data)) {
            $raw = $data['branch_id'] ?? null;
            $update['branch_id'] = ($raw === null || $raw === '') ? null : (int) $raw;
        }

        if ($hasAssignmentType && array_key_exists('assignment_type', $data)) {
            $raw = strtolower(trim((string) ($data['assignment_type'] ?? 'none')));
            $update['assignment_type'] = $raw === 'permanent' ? 'permanent' : 'none';
        }

        if ($hasAssignedDriverId && array_key_exists('assigned_driver_id', $data)) {
            $id = $data['assigned_driver_id'] ?? null;
            $update['assigned_driver_id'] = ($id === null || $id === '') ? null : (int) $id;
        }

        if ($hasRegistrationExpiry && array_key_exists('registration_expiry', $data)) {
            $update['registration_expiry'] = $data['registration_expiry'] ?? null;
        }

        if ($hasServiceDueDate && array_key_exists('service_due_date', $data)) {
            $update['service_due_date'] = $data['service_due_date'] ?? null;
        }

        if ($hasServiceDueKm && array_key_exists('service_due_km', $data)) {
            $update['service_due_km'] = $data['service_due_km'] ?? null;
        }

        if ($hasIsInService && array_key_exists('is_in_service', $data)) {
            $isInService = (int) ($data['is_in_service'] ? 1 : 0);
            $update['is_in_service'] = $isInService;

            if ($isInService === 1) {
                if ($hasOutOfServiceReason) {
                    $update['out_of_service_reason'] = null;
                }
                if ($hasOutOfServiceNote) {
                    $update['out_of_service_note'] = null;
                }
                if ($hasOutOfServiceAt) {
                    $update['out_of_service_at'] = null;
                }
            } else {
                $reason = isset($data['out_of_service_reason']) ? trim((string) $data['out_of_service_reason']) : null;
                $note = isset($data['out_of_service_note']) ? trim((string) $data['out_of_service_note']) : null;
                if ($reason === '') {
                    $reason = null;
                }
                if ($note === '') {
                    $note = null;
                }

                if ($hasOutOfServiceReason) {
                    $update['out_of_service_reason'] = $reason;
                }
                if ($hasOutOfServiceNote) {
                    $update['out_of_service_note'] = $note;
                }
                if ($hasOutOfServiceAt) {
                    $update['out_of_service_at'] = now();
                }
            }
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
