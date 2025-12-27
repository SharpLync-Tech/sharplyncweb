<?php

namespace App\Services\SharpFleet;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class BookingService
{
    public function createBooking(int $organisationId, array $data): int
    {
        if (!Schema::connection('sharpfleet')->hasTable('bookings')) {
            throw ValidationException::withMessages([
                'bookings' => 'Bookings are unavailable until the database table is created.',
            ]);
        }

        $userId = (int) ($data['user_id'] ?? 0);
        $vehicleId = (int) ($data['vehicle_id'] ?? 0);

        $plannedStart = Carbon::parse((string) ($data['planned_start'] ?? ''));
        $plannedEnd = Carbon::parse((string) ($data['planned_end'] ?? ''));

        if ($userId <= 0) {
            throw ValidationException::withMessages(['user_id' => 'Driver is required.']);
        }
        if ($vehicleId <= 0) {
            throw ValidationException::withMessages(['vehicle_id' => 'Vehicle is required.']);
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

        $id = DB::connection('sharpfleet')
            ->table('bookings')
            ->insertGetId([
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
            ]);

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
            ->where('bookings.planned_end', '>=', Carbon::now()->toDateTimeString())
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

    public function getAvailableVehicles(int $organisationId, Carbon $plannedStart, Carbon $plannedEnd)
    {
        if ($plannedEnd->lessThanOrEqualTo($plannedStart)) {
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

        if (!Schema::connection('sharpfleet')->hasTable('bookings')) {
            return $vehiclesQuery->get();
        }

        // Vehicle is unavailable if any planned booking overlaps the requested window.
        $vehiclesQuery->whereNotExists(function ($sub) use ($organisationId, $plannedStart, $plannedEnd) {
            $sub->select(DB::raw(1))
                ->from('bookings')
                ->whereColumn('bookings.vehicle_id', 'vehicles.id')
                ->where('bookings.organisation_id', $organisationId)
                ->where('bookings.status', 'planned')
                ->where('bookings.planned_start', '<', $plannedEnd->toDateTimeString())
                ->where('bookings.planned_end', '>', $plannedStart->toDateTimeString());
        });

        return $vehiclesQuery->get();
    }

    /**
     * Blocks starting a trip if someone else has an active booking for this vehicle right now.
     */
    public function assertVehicleCanStartTrip(int $organisationId, int $vehicleId, int $userId, ?Carbon $now = null): void
    {
        if (!Schema::connection('sharpfleet')->hasTable('bookings')) {
            return;
        }

        $now = $now ?: Carbon::now();

        $blocking = DB::connection('sharpfleet')
            ->table('bookings')
            ->where('organisation_id', $organisationId)
            ->where('vehicle_id', $vehicleId)
            ->where('status', 'planned')
            ->where('planned_start', '<=', $now->toDateTimeString())
            ->where('planned_end', '>=', $now->toDateTimeString())
            ->where('user_id', '!=', $userId)
            ->first();

        if ($blocking) {
            throw ValidationException::withMessages([
                'vehicle_id' => 'This vehicle is currently booked by another driver for this time window.',
            ]);
        }
    }
}
