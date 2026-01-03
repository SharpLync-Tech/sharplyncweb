<?php

namespace App\Services\SharpFleet;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class BookingService
{
    private function branchService(): BranchService
    {
        return new BranchService();
    }

    private function vehiclesHavePermanentAssignmentSupport(): bool
    {
        return Schema::connection('sharpfleet')->hasColumn('vehicles', 'assignment_type')
            && Schema::connection('sharpfleet')->hasColumn('vehicles', 'assigned_driver_id');
    }

    private function vehicleAssignmentType(?object $vehicle): string
    {
        if (!$vehicle || !property_exists($vehicle, 'assignment_type')) {
            return 'none';
        }

        $raw = strtolower(trim((string) ($vehicle->assignment_type ?? 'none')));
        return $raw === 'permanent' ? 'permanent' : 'none';
    }

    private function vehicleAssignedDriverId(?object $vehicle): ?int
    {
        if (!$vehicle || !property_exists($vehicle, 'assigned_driver_id')) {
            return null;
        }

        $id = $vehicle->assigned_driver_id ?? null;
        if ($id === null || $id === '') {
            return null;
        }

        return (int) $id;
    }

    private function vehicleOutOfServiceMessage(?object $vehicle): string
    {
        $reason = $vehicle && property_exists($vehicle, 'out_of_service_reason') ? trim((string) ($vehicle->out_of_service_reason ?? '')) : '';
        $note = $vehicle && property_exists($vehicle, 'out_of_service_note') ? trim((string) ($vehicle->out_of_service_note ?? '')) : '';

        $msg = 'This vehicle is currently out of service.';
        if ($reason !== '') {
            $msg .= ' Reason: ' . $reason . '.';
        }
        if ($note !== '') {
            $msg .= ' Note: ' . $note . '.';
        }

        return $msg;
    }

    private function assertVehicleInService(int $organisationId, int $vehicleId): void
    {
        if (!Schema::connection('sharpfleet')->hasColumn('vehicles', 'is_in_service')) {
            return; // feature not installed yet
        }

        $vehicle = DB::connection('sharpfleet')
            ->table('vehicles')
            ->select('id', 'is_in_service', 'out_of_service_reason', 'out_of_service_note')
            ->where('organisation_id', $organisationId)
            ->where('id', $vehicleId)
            ->first();

        if (!$vehicle) {
            throw ValidationException::withMessages([
                'vehicle_id' => 'Selected vehicle is invalid.',
            ]);
        }

        $isInService = property_exists($vehicle, 'is_in_service') ? (int) ($vehicle->is_in_service ?? 1) : 1;
        if ($isInService === 0) {
            throw ValidationException::withMessages([
                'vehicle_id' => $this->vehicleOutOfServiceMessage($vehicle),
            ]);
        }
    }

    /**
     * Enforce permanent vehicle assignment rules for trip start.
     *
     * - Assigned driver can use vehicle without a booking.
     * - Any other driver gets a 403.
     * - If assigned driver is inactive/missing, block trips until admin updates assignment.
     */
    public function assertVehicleAssignmentAllowsTrip(int $organisationId, int $vehicleId, int $userId): void
    {
        if (!$this->vehiclesHavePermanentAssignmentSupport()) {
            return;
        }

        $vehicle = DB::connection('sharpfleet')
            ->table('vehicles')
            ->select('id', 'assignment_type', 'assigned_driver_id')
            ->where('organisation_id', $organisationId)
            ->where('id', $vehicleId)
            ->first();

        if (!$vehicle) {
            throw ValidationException::withMessages([
                'vehicle_id' => 'Selected vehicle is invalid.',
            ]);
        }

        if ($this->vehicleAssignmentType($vehicle) !== 'permanent') {
            return;
        }

        $assignedDriverId = $this->vehicleAssignedDriverId($vehicle);
        if (!$assignedDriverId || $assignedDriverId <= 0) {
            abort(403, 'This vehicle is permanently assigned and cannot be used until an administrator updates the assignment.');
        }

        // If the assigned driver is missing/inactive, block trips until an admin updates assignment.
        $assignedDriver = null;
        if (Schema::connection('sharpfleet')->hasTable('users')) {
            $driverQuery = DB::connection('sharpfleet')
                ->table('users')
                ->where('organisation_id', $organisationId)
                ->where('id', $assignedDriverId);

            if (Schema::connection('sharpfleet')->hasColumn('users', 'is_active')) {
                $driverQuery->select('id', 'is_active');
            } else {
                $driverQuery->select('id');
            }

            $assignedDriver = $driverQuery->first();
        }

        if (!$assignedDriver) {
            abort(403, 'This vehicle is permanently assigned and cannot be used until an administrator updates the assignment.');
        }

        if (property_exists($assignedDriver, 'is_active') && (int) ($assignedDriver->is_active ?? 1) === 0) {
            abort(403, 'This vehicle is assigned to an inactive driver. Please contact an administrator.');
        }

        if ((int) $assignedDriverId !== (int) $userId) {
            abort(403, 'This vehicle is permanently assigned to another driver.');
        }
    }

    private function nowUtc(): Carbon
    {
        return Carbon::now('UTC');
    }

    private function parseLocalToUtc(string $raw, string $timezone, string $field = 'planned_start'): Carbon
    {
        $raw = trim($raw);
        if ($raw === '') {
            throw ValidationException::withMessages([
                $field => 'Planned start is required.',
            ]);
        }

        // Controllers produce: YYYY-MM-DD HH:MM:SS (no timezone). Treat as company-local.
        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $raw) === 1) {
            try {
                return Carbon::createFromFormat('Y-m-d H:i:s', $raw, $timezone)->utc();
            } catch (\Throwable $e) {
                // fall through
            }
        }

        // Fallback: parse as company-local (handles e.g. ISO strings too).
        try {
            return Carbon::parse($raw, $timezone)->utc();
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                $field => 'Invalid planned date/time.',
            ]);
        }
    }

    private function resolveBookingTimezone(int $organisationId, int $vehicleId, ?int $branchId = null): string
    {
        $branches = $this->branchService();

        if ($branches->branchesEnabled() && $branches->vehiclesHaveBranchSupport()) {
            if ($branchId && $branchId > 0) {
                $branch = $branches->getBranch($organisationId, $branchId);
                if ($branch && isset($branch->timezone) && trim((string) $branch->timezone) !== '') {
                    return (string) $branch->timezone;
                }
            }

            return $branches->getTimezoneForVehicle($organisationId, $vehicleId);
        }

        return (new CompanySettingsService($organisationId))->timezone();
    }

    public function changeBookingVehicle(int $organisationId, int $bookingId, int $newVehicleId): void
    {
        if (!Schema::connection('sharpfleet')->hasTable('bookings')) {
            throw ValidationException::withMessages([
                'bookings' => 'Bookings are unavailable until the database table is created.',
            ]);
        }

        if ($newVehicleId <= 0) {
            throw ValidationException::withMessages([
                'new_vehicle_id' => 'Vehicle is required.',
            ]);
        }

        // If the out-of-service feature exists, block changing to an out-of-service vehicle.
        $this->assertVehicleInService($organisationId, $newVehicleId);

        // Permanently assigned vehicles can never be booked.
        if ($this->vehiclesHavePermanentAssignmentSupport()) {
            $assignment = DB::connection('sharpfleet')
                ->table('vehicles')
                ->select('assignment_type')
                ->where('organisation_id', $organisationId)
                ->where('id', $newVehicleId)
                ->first();

            if ($assignment && $this->vehicleAssignmentType($assignment) === 'permanent') {
                abort(403, 'This vehicle is permanently assigned and cannot be booked.');
            }
        }

        $booking = DB::connection('sharpfleet')
            ->table('bookings')
            ->where('organisation_id', $organisationId)
            ->where('id', $bookingId)
            ->first();

        if (!$booking) {
            throw ValidationException::withMessages([
                'booking' => 'Booking not found.',
            ]);
        }

        if ((string) $booking->status !== 'planned') {
            throw ValidationException::withMessages([
                'booking' => 'Only planned bookings can be updated.',
            ]);
        }

        // Only allow changing vehicles on upcoming/active bookings.
        if (Carbon::parse($booking->planned_end)->utc()->lessThan($this->nowUtc())) {
            throw ValidationException::withMessages([
                'booking' => 'This booking has already ended and cannot be updated.',
            ]);
        }

        $vehicleExists = DB::connection('sharpfleet')
            ->table('vehicles')
            ->where('organisation_id', $organisationId)
            ->where('id', $newVehicleId)
            ->where('is_active', 1)
            ->exists();

        if (!$vehicleExists) {
            throw ValidationException::withMessages([
                'new_vehicle_id' => 'Selected vehicle is invalid or inactive.',
            ]);
        }

        $plannedStart = Carbon::parse($booking->planned_start)->utc();
        $plannedEnd = Carbon::parse($booking->planned_end)->utc();

        // Prevent overlapping planned bookings for the new vehicle (excluding this booking).
        $overlapExists = DB::connection('sharpfleet')
            ->table('bookings')
            ->where('organisation_id', $organisationId)
            ->where('vehicle_id', $newVehicleId)
            ->where('status', 'planned')
            ->where('id', '!=', (int) $booking->id)
            ->where('planned_start', '<', $plannedEnd->toDateTimeString())
            ->where('planned_end', '>', $plannedStart->toDateTimeString())
            ->exists();

        if ($overlapExists) {
            throw ValidationException::withMessages([
                'new_vehicle_id' => 'This vehicle is already booked for the selected time window.',
            ]);
        }

        DB::connection('sharpfleet')
            ->table('bookings')
            ->where('organisation_id', $organisationId)
            ->where('id', $bookingId)
            ->update([
                'vehicle_id' => $newVehicleId,
                'updated_at' => now(),
            ]);
    }

    public function createBooking(int $organisationId, array $data): int
    {
        if (!Schema::connection('sharpfleet')->hasTable('bookings')) {
            throw ValidationException::withMessages([
                'bookings' => 'Bookings are unavailable until the database table is created.',
            ]);
        }

        $userId = (int) ($data['user_id'] ?? 0);
        $vehicleId = (int) ($data['vehicle_id'] ?? 0);

        $branchId = isset($data['branch_id']) && $data['branch_id'] !== null && $data['branch_id'] !== ''
            ? (int) $data['branch_id']
            : null;

        $timezone = $this->resolveBookingTimezone($organisationId, $vehicleId, $branchId);

        $plannedStart = $this->parseLocalToUtc((string) ($data['planned_start'] ?? ''), $timezone, 'planned_start');
        $plannedEnd = $this->parseLocalToUtc((string) ($data['planned_end'] ?? ''), $timezone, 'planned_end');

        // Disallow bookings starting in the past (date + time must be in the future).
        if ($plannedStart->lessThan($this->nowUtc())) {
            throw ValidationException::withMessages([
                'planned_start_date' => 'Booking start must be in the future.',
            ]);
        }

        if ($userId <= 0) {
            throw ValidationException::withMessages(['user_id' => 'Driver is required.']);
        }
        if ($vehicleId <= 0) {
            throw ValidationException::withMessages(['vehicle_id' => 'Vehicle is required.']);
        }

        // If branches are installed, ensure the requested branch (if provided) matches the vehicle.
        $branches = $this->branchService();
        if ($branchId && $branches->branchesEnabled() && $branches->vehiclesHaveBranchSupport()) {
            $vehicleBranchId = $branches->getBranchIdForVehicle($organisationId, $vehicleId);
            if ($vehicleBranchId && (int) $vehicleBranchId !== (int) $branchId) {
                throw ValidationException::withMessages([
                    'branch_id' => 'Selected vehicle does not belong to the selected branch.',
                ]);
            }
        }

        // If the out-of-service feature exists, block bookings for out-of-service vehicles.
        $this->assertVehicleInService($organisationId, $vehicleId);

        // Permanently assigned vehicles can never be booked.
        if ($this->vehiclesHavePermanentAssignmentSupport()) {
            $assignment = DB::connection('sharpfleet')
                ->table('vehicles')
                ->select('assignment_type')
                ->where('organisation_id', $organisationId)
                ->where('id', $vehicleId)
                ->first();

            if ($assignment && $this->vehicleAssignmentType($assignment) === 'permanent') {
                abort(403, 'This vehicle is permanently assigned and cannot be booked.');
            }
        }

        if ($plannedEnd->lessThanOrEqualTo($plannedStart)) {
            throw ValidationException::withMessages(['planned_end' => 'End time must be after start time.']);
        }

        $customerId = isset($data['customer_id']) && $data['customer_id'] !== null && $data['customer_id'] !== ''
            ? (int) $data['customer_id']
            : null;

        $customerName = isset($data['customer_name']) ? trim((string) $data['customer_name']) : '';
        if ($customerName === '') {
            $customerName = null;
        }
        if ($customerName !== null && mb_strlen($customerName) > 150) {
            $customerName = mb_substr($customerName, 0, 150);
        }

        // Prevent overlapping planned bookings for the same vehicle.
        $overlapExists = DB::connection('sharpfleet')
            ->table('bookings')
            ->where('organisation_id', $organisationId)
            ->where('vehicle_id', $vehicleId)
            ->where('status', 'planned')
            ->where('planned_start', '<', $plannedEnd->toDateTimeString())
            ->where('planned_end', '>', $plannedStart->toDateTimeString())
            ->exists();

        if ($overlapExists) {
            throw ValidationException::withMessages([
                'planned_start' => 'This vehicle is already booked for the selected time window.',
            ]);
        }

        $insert = [
                'organisation_id' => $organisationId,
                'user_id' => $userId,
                'vehicle_id' => $vehicleId,
                'customer_id' => $customerId,
                'customer_name' => $customerName,
                'planned_start' => $plannedStart->toDateTimeString(),
                'planned_end' => $plannedEnd->toDateTimeString(),
                'status' => 'planned',
                'notes' => isset($data['notes']) ? trim((string) $data['notes']) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

        if ($branches->branchesEnabled() && $branches->bookingsHaveBranchSupport()) {
            $insert['branch_id'] = $branchId ?: $branches->getBranchIdForVehicle($organisationId, $vehicleId);
        }

        if ($branches->bookingsHaveTimezoneSupport()) {
            $insert['timezone'] = $timezone;
        }

        $id = DB::connection('sharpfleet')
            ->table('bookings')
            ->insertGetId($insert);

        return (int) $id;
    }

    public function getUpcomingBookings(int $organisationId): array
    {
        $hasBookings = Schema::connection('sharpfleet')->hasTable('bookings');
        $hasCustomers = Schema::connection('sharpfleet')->hasTable('customers');

        if (!$hasBookings) {
            return [
                'tableExists' => false,
                'bookings' => collect(),
            ];
        }

        $query = DB::connection('sharpfleet')
            ->table('bookings')
            ->join('users', 'bookings.user_id', '=', 'users.id')
            ->leftJoin('vehicles', 'bookings.vehicle_id', '=', 'vehicles.id');

        if ($hasCustomers) {
            $query->leftJoin('customers', 'bookings.customer_id', '=', 'customers.id');
        }

        $query->where('bookings.organisation_id', $organisationId)
            ->where('bookings.status', 'planned')
            ->where('bookings.planned_end', '>=', $this->nowUtc()->toDateTimeString())
            ->orderBy('bookings.planned_start');

        $bookings = $query->select(
            'bookings.*',
            DB::raw("CONCAT(users.first_name, ' ', users.last_name) as driver_name"),
            'vehicles.name as vehicle_name',
            'vehicles.registration_number',
            $hasCustomers
                ? DB::raw('COALESCE(customers.name, bookings.customer_name) as customer_name_display')
                : DB::raw('bookings.customer_name as customer_name_display')
        )->get();

        return [
            'tableExists' => true,
            'bookings' => $bookings,
        ];
    }

    public function cancelBooking(int $organisationId, int $bookingId, array $actor, bool $isAdmin = false): void
    {
        if (!Schema::connection('sharpfleet')->hasTable('bookings')) {
            throw ValidationException::withMessages([
                'bookings' => 'Bookings are unavailable until the database table is created.',
            ]);
        }

        $booking = DB::connection('sharpfleet')
            ->table('bookings')
            ->where('organisation_id', $organisationId)
            ->where('id', $bookingId)
            ->first();

        if (!$booking) {
            throw ValidationException::withMessages([
                'booking' => 'Booking not found.',
            ]);
        }

        if (!$isAdmin && (int) $booking->user_id !== (int) ($actor['id'] ?? 0)) {
            abort(403, 'You can only cancel your own bookings.');
        }

        DB::connection('sharpfleet')
            ->table('bookings')
            ->where('organisation_id', $organisationId)
            ->where('id', $bookingId)
            ->update([
                'status' => 'cancelled',
                'updated_at' => now(),
            ]);
    }

    public function getAvailableVehicles(int $organisationId, Carbon $plannedStart, Carbon $plannedEnd, ?int $branchId = null)
    {
        $plannedStartUtc = $plannedStart->copy()->utc();
        $plannedEndUtc = $plannedEnd->copy()->utc();

        if ($plannedStartUtc->lessThan($this->nowUtc())) {
            throw ValidationException::withMessages([
                'planned_start_date' => 'Booking start must be in the future.',
            ]);
        }

        if ($plannedEndUtc->lessThanOrEqualTo($plannedStartUtc)) {
            throw ValidationException::withMessages([
                'planned_end' => 'End time must be after start time.',
            ]);
        }

        $vehiclesQuery = DB::connection('sharpfleet')
            ->table('vehicles')
            ->select('vehicles.id', 'vehicles.name', 'vehicles.registration_number')
            ->where('vehicles.organisation_id', $organisationId)
            ->where('vehicles.is_active', 1)
            ->orderBy('vehicles.name');

        $branches = $this->branchService();
        if ($branches->branchesEnabled() && $branches->vehiclesHaveBranchSupport()) {
            $useBranchId = $branchId;
            if (!$useBranchId || $useBranchId <= 0) {
                $defaultBranch = $branches->getDefaultBranch($organisationId);
                $useBranchId = $defaultBranch ? (int) $defaultBranch->id : null;
            }
            if ($useBranchId && $useBranchId > 0) {
                $vehiclesQuery->where('vehicles.branch_id', $useBranchId);
            }
        }

        if (Schema::connection('sharpfleet')->hasColumn('vehicles', 'is_in_service')) {
            $vehiclesQuery->where('vehicles.is_in_service', 1);
        }

        // Permanently assigned vehicles cannot be booked.
        if ($this->vehiclesHavePermanentAssignmentSupport()) {
            $vehiclesQuery->where(function ($q) {
                $q->whereNull('vehicles.assignment_type')
                    ->orWhere('vehicles.assignment_type', 'none');
            });
        }

        if (!Schema::connection('sharpfleet')->hasTable('bookings')) {
            return $vehiclesQuery->get();
        }

        // Vehicle is unavailable if any planned booking overlaps the requested window.
        $vehiclesQuery->whereNotExists(function ($sub) use ($organisationId, $plannedStartUtc, $plannedEndUtc) {
            $sub->select(DB::raw(1))
                ->from('bookings')
                ->whereColumn('bookings.vehicle_id', 'vehicles.id')
                ->where('bookings.organisation_id', $organisationId)
                ->where('bookings.status', 'planned')
            ->where('bookings.planned_start', '<', $plannedEndUtc->toDateTimeString())
            ->where('bookings.planned_end', '>', $plannedStartUtc->toDateTimeString());
        });

        return $vehiclesQuery->get();
    }

    /**
     * Blocks starting a trip if someone else has an active booking for this vehicle right now.
     */
    public function assertVehicleCanStartTrip(int $organisationId, int $vehicleId, int $userId, ?Carbon $now = null): void
    {
        // Out-of-service vehicles can never be used for trips.
        $this->assertVehicleInService($organisationId, $vehicleId);

        // Permanent assignment rules.
        $this->assertVehicleAssignmentAllowsTrip($organisationId, $vehicleId, $userId);

        if (!Schema::connection('sharpfleet')->hasTable('bookings')) {
            return;
        }

        $nowUtc = ($now ?: $this->nowUtc())->copy()->utc();

        $blocking = DB::connection('sharpfleet')
            ->table('bookings')
            ->where('organisation_id', $organisationId)
            ->where('vehicle_id', $vehicleId)
            ->where('status', 'planned')
            ->where('planned_start', '<=', $nowUtc->toDateTimeString())
            ->where('planned_end', '>=', $nowUtc->toDateTimeString())
            ->where('user_id', '!=', $userId)
            ->first();

        if ($blocking) {
            throw ValidationException::withMessages([
                'vehicle_id' => 'This vehicle is currently booked by another driver for this time window.',
            ]);
        }
    }
}
