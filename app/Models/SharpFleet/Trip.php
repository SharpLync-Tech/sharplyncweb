<?php

namespace App\Models\SharpFleet;

use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    protected $connection = 'sharpfleet';
    protected $table = 'trips';

    protected $fillable = [
        'organisation_id',
        'user_id',
        'vehicle_id',
        'customer_id',
        'customer_name',
        'trip_mode',

        'start_km',
        'end_km',

        'distance_method',

        'client_present',
        'client_address',

        // Date / time fields
        'started_at',
        'ended_at',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at'   => 'datetime',
    ];
}
