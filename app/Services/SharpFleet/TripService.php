<?php

namespace App\Services\SharpFleet;

use App\Models\SharpFleet\Trip;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TripService
{
    public function startTrip(array $data): Trip
    {
        $user = Auth::user();

        // Find last completed trip for this vehicle
        $lastTrip = Trip::where('vehicle_id', $data['vehicle_id'])
            ->whereNotNull('end_km')
            ->orderByDesc('ended_at')
            ->first();

        $startKm = $lastTrip ? $lastTrip->end_km : $data['start_km'];

        return Trip::create([
            'organisation_id' => $user->organisation_id,
            'user_id'         => $user->id,
            'vehicle_id'      => $data['vehicle_id'],
            'customer_id'     => $data['customer_id'] ?? null,
            'customer_name'   => $data['customer_name'] ?? null,
            'trip_mode'       => $data['trip_mode'],
            'start_km'        => $startKm,
            'distance_method' => $data['distance_method'] ?? 'odometer',
            'started_at'      => Carbon::now(),
        ]);
    }

    public function endTrip() {}
    public function editTrip() {}
}
