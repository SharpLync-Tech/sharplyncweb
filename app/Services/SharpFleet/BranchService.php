<?php

namespace App\Services\SharpFleet;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BranchService
{
    public function userBranchAccessEnabled(): bool
    {
        return Schema::connection('sharpfleet')->hasTable('user_branch_access')
            && Schema::connection('sharpfleet')->hasColumn('user_branch_access', 'organisation_id')
            && Schema::connection('sharpfleet')->hasColumn('user_branch_access', 'user_id')
            && Schema::connection('sharpfleet')->hasColumn('user_branch_access', 'branch_id');
    }

    public function branchesEnabled(): bool
    {
        return Schema::connection('sharpfleet')->hasTable('branches')
            && Schema::connection('sharpfleet')->hasColumn('branches', 'organisation_id')
            && Schema::connection('sharpfleet')->hasColumn('branches', 'timezone');
    }

    public function vehiclesHaveBranchSupport(): bool
    {
        return Schema::connection('sharpfleet')->hasColumn('vehicles', 'branch_id');
    }

    public function bookingsHaveBranchSupport(): bool
    {
        return Schema::connection('sharpfleet')->hasColumn('bookings', 'branch_id');
    }

    public function bookingsHaveTimezoneSupport(): bool
    {
        return Schema::connection('sharpfleet')->hasColumn('bookings', 'timezone');
    }

    public function tripsHaveBranchSupport(): bool
    {
        return Schema::connection('sharpfleet')->hasColumn('trips', 'branch_id');
    }

    public function tripsHaveTimezoneSupport(): bool
    {
        return Schema::connection('sharpfleet')->hasColumn('trips', 'timezone');
    }

    public function getBranches(int $organisationId): Collection
    {
        if (!$this->branchesEnabled()) {
            return collect();
        }

        $query = DB::connection('sharpfleet')
            ->table('branches')
            ->where('organisation_id', $organisationId);

        if (Schema::connection('sharpfleet')->hasColumn('branches', 'is_active')) {
            $query->where('is_active', 1);
        }

        if (Schema::connection('sharpfleet')->hasColumn('branches', 'is_default')) {
            $query->orderByDesc('is_default');
        }

        return $query->orderBy('name')->get();
    }

    public function getBranch(int $organisationId, int $branchId): ?object
    {
        if (!$this->branchesEnabled() || $branchId <= 0) {
            return null;
        }

        $query = DB::connection('sharpfleet')
            ->table('branches')
            ->where('organisation_id', $organisationId)
            ->where('id', $branchId);

        if (Schema::connection('sharpfleet')->hasColumn('branches', 'is_active')) {
            $query->where('is_active', 1);
        }

        return $query->first();
    }

    public function getDefaultBranch(int $organisationId): ?object
    {
        if (!$this->branchesEnabled()) {
            return null;
        }

        $query = DB::connection('sharpfleet')
            ->table('branches')
            ->where('organisation_id', $organisationId);

        if (Schema::connection('sharpfleet')->hasColumn('branches', 'is_active')) {
            $query->where('is_active', 1);
        }

        if (Schema::connection('sharpfleet')->hasColumn('branches', 'is_default')) {
            $query->orderByDesc('is_default');
        }

        return $query->orderBy('id')->first();
    }

    public function ensureDefaultBranch(int $organisationId, ?string $fallbackTimezone = null): ?int
    {
        if (!$this->branchesEnabled()) {
            return null;
        }

        $existing = $this->getDefaultBranch($organisationId);
        if ($existing) {
            return (int) $existing->id;
        }

        $timezone = $fallbackTimezone ?: (new CompanySettingsService($organisationId))->timezone();

        $payload = [
            'organisation_id' => $organisationId,
            'name' => 'Main Branch',
            'timezone' => $timezone,
        ];

        if (Schema::connection('sharpfleet')->hasColumn('branches', 'is_default')) {
            $payload['is_default'] = 1;
        }
        if (Schema::connection('sharpfleet')->hasColumn('branches', 'is_active')) {
            $payload['is_active'] = 1;
        }
        if (Schema::connection('sharpfleet')->hasColumn('branches', 'created_at')) {
            $payload['created_at'] = now();
        }
        if (Schema::connection('sharpfleet')->hasColumn('branches', 'updated_at')) {
            $payload['updated_at'] = now();
        }

        return (int) DB::connection('sharpfleet')->table('branches')->insertGetId($payload);
    }

    public function getTimezoneForVehicle(int $organisationId, int $vehicleId): string
    {
        if (!$this->branchesEnabled() || !$this->vehiclesHaveBranchSupport()) {
            return (new CompanySettingsService($organisationId))->timezone();
        }

        $row = DB::connection('sharpfleet')
            ->table('vehicles')
            ->leftJoin('branches', function ($join) {
                $join->on('vehicles.branch_id', '=', 'branches.id');
            })
            ->where('vehicles.organisation_id', $organisationId)
            ->where('vehicles.id', $vehicleId)
            ->select('vehicles.branch_id', 'branches.timezone')
            ->first();

        $tz = $row && isset($row->timezone) ? trim((string) $row->timezone) : '';
        if ($tz !== '') {
            return $tz;
        }

        return (new CompanySettingsService($organisationId))->timezone();
    }

    public function getBranchIdForVehicle(int $organisationId, int $vehicleId): ?int
    {
        if (!$this->branchesEnabled() || !$this->vehiclesHaveBranchSupport()) {
            return null;
        }

        $row = DB::connection('sharpfleet')
            ->table('vehicles')
            ->where('organisation_id', $organisationId)
            ->where('id', $vehicleId)
            ->select('branch_id')
            ->first();

        if (!$row) {
            return null;
        }

        $id = $row->branch_id ?? null;
        if ($id === null || $id === '') {
            return null;
        }

        return (int) $id;
    }

    public function getAccessibleBranchIdsForUser(int $organisationId, int $userId): array
    {
        if (!$this->branchesEnabled() || !$this->userBranchAccessEnabled() || $userId <= 0) {
            return [];
        }

        $query = DB::connection('sharpfleet')
            ->table('user_branch_access')
            ->where('organisation_id', $organisationId)
            ->where('user_id', $userId);

        if (Schema::connection('sharpfleet')->hasColumn('user_branch_access', 'is_active')) {
            $query->where('is_active', 1);
        }

        return $query->pluck('branch_id')->map(fn ($v) => (int) $v)->values()->all();
    }

    public function userCanAccessBranch(int $organisationId, int $userId, int $branchId): bool
    {
        if ($branchId <= 0) {
            return false;
        }

        if (!$this->branchesEnabled() || !$this->userBranchAccessEnabled()) {
            return true;
        }

        $query = DB::connection('sharpfleet')
            ->table('user_branch_access')
            ->where('organisation_id', $organisationId)
            ->where('user_id', $userId)
            ->where('branch_id', $branchId);

        if (Schema::connection('sharpfleet')->hasColumn('user_branch_access', 'is_active')) {
            $query->where('is_active', 1);
        }

        return $query->exists();
    }

    public function getBranchesForUser(int $organisationId, int $userId): Collection
    {
        if (!$this->branchesEnabled() || !$this->userBranchAccessEnabled() || $userId <= 0) {
            return $this->getBranches($organisationId);
        }

        $query = DB::connection('sharpfleet')
            ->table('branches')
            ->join('user_branch_access', function ($join) {
                $join->on('branches.id', '=', 'user_branch_access.branch_id');
            })
            ->where('branches.organisation_id', $organisationId)
            ->where('user_branch_access.organisation_id', $organisationId)
            ->where('user_branch_access.user_id', $userId);

        if (Schema::connection('sharpfleet')->hasColumn('branches', 'is_active')) {
            $query->where('branches.is_active', 1);
        }

        if (Schema::connection('sharpfleet')->hasColumn('user_branch_access', 'is_active')) {
            $query->where('user_branch_access.is_active', 1);
        }

        if (Schema::connection('sharpfleet')->hasColumn('branches', 'is_default')) {
            $query->orderByDesc('branches.is_default');
        }

        return $query
            ->orderBy('branches.name')
            ->select('branches.*')
            ->get();
    }
}
