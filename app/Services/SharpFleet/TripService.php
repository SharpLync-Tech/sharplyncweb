<?php

namespace App\Services\SharpFleet;

use App\Models\SharpFleet\Trip;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class TripService
{
    /**
     * Start a trip for a SharpFleet driver
     */
    public function startTrip(array $user, array $data): Trip
    {
        $now = Carbon::now();

        // Find last completed trip for this vehicle (scoped to organisation)
        $lastTrip = Trip::where('vehicle_id', $data['vehicle_id'])
            ->where('organisation_id', $user['organisation_id'])
            ->whereNotNull('end_km')
            ->orderByDesc('ended_at')
            ->first();

        $startKm = $lastTrip
            ? $lastTrip->end_km
            : $data['start_km'];

        return Trip::create([
            'organisation_id' => $user['organisation_id'],
            'user_id'         => $user['id'],
            'vehicle_id'      => $data['vehicle_id'],
            'customer_id'     => $data['customer_id'] ?? null,
            'customer_name'   => $data['customer_name'] ?? null,
            'trip_mode'       => $data['trip_mode'],
            'start_km'        => $startKm,
            'distance_method' => $data['distance_method'] ?? 'odometer',
            'client_present'  => $data['client_present'] ?? null,
            'client_address'  => $data['client_address'] ?? null,

            // Datetime fields (DB expects DATETIME, not TIME)
            'started_at' => $now,
            'start_time' => $now,
        ]);
    }

    /**
     * End an active trip
     */
    public function endTrip(array $user, array $data): Trip
    {
        $now = Carbon::now();

        $trip = Trip::where('id', $data['trip_id'])
            ->where('organisation_id', $user['organisation_id'])
            ->where('user_id', $user['id'])
            ->whereNull('ended_at')
            ->firstOrFail();

        $endKm = (int) $data['end_km'];
        $startKm = $trip->start_km !== null ? (int) $trip->start_km : null;

        if ($startKm !== null && $endKm < $startKm) {
            throw ValidationException::withMessages([
                'end_km' => 'Ending reading must be the same as or greater than the starting reading.',
            ]);
        }

        $trip->update([
            'end_km'   => $endKm,
            'ended_at' => $now,
            'end_time' => $now,
        ]);

        return $trip;
    }

    public function editTrip()
    {
        // to be implemented
    }
}
