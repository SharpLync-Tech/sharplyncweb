<?php

namespace App\Models\SharpFleet;

use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
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
        'started_at',
        'ended_at'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at'   => 'datetime',
    ];
}
