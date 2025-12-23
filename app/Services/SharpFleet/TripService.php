<?php

namespace App\Services\SharpFleet;

use App\Models\SharpFleet\Trip;
use Carbon\Carbon;

class TripService
{
    /**
     * Start a trip for a SharpFleet driver
     *
     * @param array $user  SharpFleet session user
     * @param array $data  Validated trip data
     */
    public function startTrip(array $user, array $data): Trip
    {
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
            'started_at'      => Carbon::now(),
        ]);
    }

    /**
     * End an active trip for a SharpFleet driver
     *
     * @param array $user  SharpFleet session user
     * @param array $data  ['trip_id', 'end_km']
     */
    public function endTrip(array $user, array $data): Trip
    {
        $trip = Trip::where('id', $data['trip_id'])
            ->where('organisation_id', $user['organisation_id'])
            ->where('user_id', $user['id'])
            ->whereNull('ended_at')
            ->firstOrFail();

        $trip->update([
            'end_km'    => $data['end_km'],
            'ended_at'  => Carbon::now(),
        ]);

        return $trip;
    }

    public function editTrip()
    {
        // to be implemented
    }
}
