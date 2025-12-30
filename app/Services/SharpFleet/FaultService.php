<?php

namespace App\Services\SharpFleet;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class FaultService
{
    private const ALLOWED_SEVERITIES = ['minor', 'major', 'critical'];
    private const ALLOWED_STATUSES = ['open', 'in_review', 'resolved', 'dismissed', 'archived'];
    private const ALLOWED_TYPES = ['issue', 'accident'];

    private function assertFaultsTableExists(): void
    {
        if (!Schema::connection('sharpfleet')->hasTable('faults')) {
            abort(503, 'Vehicle issue/accident reporting is enabled, but the faults table is missing. Please run the tenant SQL script in phpMyAdmin.');
        }
    }

    private function faultsHasReportTypeColumn(): bool
    {
        return Schema::connection('sharpfleet')->hasColumn('faults', 'report_type');
    }

    private function normalizeSeverity(?string $severity): string
    {
        $val = strtolower(trim((string) ($severity ?? '')));
        if (in_array($val, self::ALLOWED_SEVERITIES, true)) {
            return $val;
        }

        return 'minor';
    }

    private function normalizeStatus(string $status): string
    {
        $val = strtolower(trim($status));
        if (!in_array($val, self::ALLOWED_STATUSES, true)) {
            throw ValidationException::withMessages([
                'status' => 'Invalid status.',
            ]);
        }
        return $val;
    }

    private function normalizeReportType(?string $type): string
    {
        $val = strtolower(trim((string) ($type ?? '')));
        if (in_array($val, self::ALLOWED_TYPES, true)) {
            return $val;
        }

        // Backwards-compatible default.
        return 'issue';
    }

    private function normalizeOccurredAt(mixed $occurredAt): ?string
    {
        $raw = is_string($occurredAt) ? trim($occurredAt) : '';
        if ($raw === '') {
            return null;
        }

        try {
            return Carbon::parse($raw)->toDateTimeString();
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** @return array<string, mixed> */
    private function getTripForUser(int $organisationId, int $userId, int $tripId): array
    {
        $trip = DB::connection('sharpfleet')
            ->table('trips')
            ->select('id', 'organisation_id', 'user_id', 'vehicle_id', 'started_at', 'ended_at')
            ->where('organisation_id', $organisationId)
            ->where('user_id', $userId)
            ->where('id', $tripId)
            ->first();

        if (!$trip) {
            abort(404, 'Trip not found.');
        }

        return [
            'id' => (int) $trip->id,
            'vehicle_id' => (int) $trip->vehicle_id,
            'ended_at' => $trip->ended_at,
        ];
    }

    /**
     * Driver: create a fault linked to a trip.
     *
     * @param array<string, mixed> $user
     * @param array<string, mixed> $data
     */
    public function createFaultFromTrip(array $user, array $data): int
    {
        $organisationId = (int) ($user['organisation_id'] ?? 0);
        $userId = (int) ($user['id'] ?? 0);
        if ($organisationId <= 0 || $userId <= 0) {
            abort(401, 'Not authenticated');
        }

        $settings = new CompanySettingsService($organisationId);
        if (!$settings->faultsEnabled()) {
            abort(403, 'Vehicle issue/accident reporting is not enabled for this company.');
        }

        $this->assertFaultsTableExists();

        $tripId = (int) ($data['trip_id'] ?? 0);
        if ($tripId <= 0) {
            throw ValidationException::withMessages([
                'trip_id' => 'Trip is required.',
            ]);
        }

        $trip = $this->getTripForUser($organisationId, $userId, $tripId);

        $isTripActive = $trip['ended_at'] === null;
        if ($isTripActive && !$settings->allowFaultsDuringTrip()) {
            abort(403, 'Vehicle issue/accident reporting is not allowed during a trip.');
        }

        $vehicleId = (int) $trip['vehicle_id'];

        $severity = $this->normalizeSeverity(isset($data['severity']) ? (string) $data['severity'] : null);
        $reportType = $this->normalizeReportType(isset($data['report_type']) ? (string) $data['report_type'] : null);
        $title = isset($data['title']) ? trim((string) $data['title']) : '';
        if ($title === '') {
            $title = null;
        }
        $description = trim((string) ($data['description'] ?? ''));
        if ($description === '') {
            throw ValidationException::withMessages([
                'description' => 'Description is required.',
            ]);
        }

        $occurredAt = $this->normalizeOccurredAt($data['occurred_at'] ?? null);

        $insert = [
            'organisation_id' => $organisationId,
            'vehicle_id' => $vehicleId,
            'user_id' => $userId,
            'trip_id' => $tripId,
            'severity' => $severity,
            'title' => $title,
            'description' => $description,
            'occurred_at' => $occurredAt,
            'status' => 'open',
            'created_at' => Carbon::now()->toDateTimeString(),
        ];

        if ($this->faultsHasReportTypeColumn()) {
            $insert['report_type'] = $reportType;
        }

        try {
            $id = DB::connection('sharpfleet')
                ->table('faults')
                ->insertGetId($insert);
        } catch (\Illuminate\Database\QueryException $e) {
            abort(503, 'Vehicle issue/accident reporting is enabled, but the tenant database schema is not up to date. Please run the latest tenant SQL update script in phpMyAdmin.');
        }

        return (int) $id;
    }

    /**
     * Driver: create a standalone fault against a vehicle.
     *
     * @param array<string, mixed> $user
     * @param array<string, mixed> $data
     */
    public function createFaultStandalone(array $user, array $data): int
    {
        $organisationId = (int) ($user['organisation_id'] ?? 0);
        $userId = (int) ($user['id'] ?? 0);
        if ($organisationId <= 0 || $userId <= 0) {
            abort(401, 'Not authenticated');
        }

        $settings = new CompanySettingsService($organisationId);
        if (!$settings->faultsEnabled()) {
            abort(403, 'Vehicle issue/accident reporting is not enabled for this company.');
        }

        $this->assertFaultsTableExists();

        $vehicleId = (int) ($data['vehicle_id'] ?? 0);
        if ($vehicleId <= 0) {
            throw ValidationException::withMessages([
                'vehicle_id' => 'Vehicle is required.',
            ]);
        }

        $vehicle = DB::connection('sharpfleet')
            ->table('vehicles')
            ->select('id')
            ->where('organisation_id', $organisationId)
            ->where('is_active', 1)
            ->where('id', $vehicleId)
            ->first();

        if (!$vehicle) {
            abort(404, 'Vehicle not found.');
        }

        $severity = $this->normalizeSeverity(isset($data['severity']) ? (string) $data['severity'] : null);
        $reportType = $this->normalizeReportType(isset($data['report_type']) ? (string) $data['report_type'] : null);
        $title = isset($data['title']) ? trim((string) $data['title']) : '';
        if ($title === '') {
            $title = null;
        }
        $description = trim((string) ($data['description'] ?? ''));
        if ($description === '') {
            throw ValidationException::withMessages([
                'description' => 'Description is required.',
            ]);
        }

        $occurredAt = $this->normalizeOccurredAt($data['occurred_at'] ?? null);

        $insert = [
            'organisation_id' => $organisationId,
            'vehicle_id' => $vehicleId,
            'user_id' => $userId,
            'trip_id' => null,
            'severity' => $severity,
            'title' => $title,
            'description' => $description,
            'occurred_at' => $occurredAt,
            'status' => 'open',
            'created_at' => Carbon::now()->toDateTimeString(),
        ];

        if ($this->faultsHasReportTypeColumn()) {
            $insert['report_type'] = $reportType;
        }

        try {
            $id = DB::connection('sharpfleet')
                ->table('faults')
                ->insertGetId($insert);
        } catch (\Illuminate\Database\QueryException $e) {
            abort(503, 'Vehicle issue/accident reporting is enabled, but the tenant database schema is not up to date. Please run the latest tenant SQL update script in phpMyAdmin.');
        }

        return (int) $id;
    }

    public function listFaultsForOrganisation(int $organisationId, int $limit = 200): Collection
    {
        $this->assertFaultsTableExists();

        return DB::connection('sharpfleet')
            ->table('faults as f')
            ->leftJoin('vehicles as v', 'f.vehicle_id', '=', 'v.id')
            ->leftJoin('users as u', 'f.user_id', '=', 'u.id')
            ->select(
                'f.*',
                'v.name as vehicle_name',
                'v.registration_number as vehicle_registration_number',
                'u.first_name as user_first_name',
                'u.last_name as user_last_name',
                'u.email as user_email'
            )
            ->where('f.organisation_id', $organisationId)
            ->orderByDesc('f.created_at')
            ->limit(max(1, min(500, $limit)))
            ->get();
    }

    public function updateFaultStatus(int $organisationId, int $faultId, string $status): void
    {
        $this->assertFaultsTableExists();

        $status = $this->normalizeStatus($status);

        try {
            $updated = DB::connection('sharpfleet')
                ->table('faults')
                ->where('organisation_id', $organisationId)
                ->where('id', $faultId)
                ->update([
                    'status' => $status,
                    'updated_at' => Carbon::now()->toDateTimeString(),
                ]);
        } catch (\Illuminate\Database\QueryException $e) {
            abort(503, 'The tenant database schema is not up to date for this status. Please run the latest tenant SQL update script in phpMyAdmin.');
        }

        if ($updated <= 0) {
            abort(404, 'Fault not found.');
        }
    }
}
