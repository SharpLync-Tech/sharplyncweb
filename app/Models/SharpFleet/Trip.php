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

        // Branch/timezone snapshot (optional columns; schema-guard before writing)
        'branch_id',
        'timezone',
        'customer_id',
        'customer_name',
        'trip_mode',

        'start_km',
        'end_km',

        'distance_method',

        'client_present',
        'client_address',

        'purpose_of_travel',

        // Date / time fields
        'started_at',
        'ended_at',
        'start_time',
        'end_time',

        'safety_check_confirmed',
        'safety_check_confirmed_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at'   => 'datetime',
        'safety_check_confirmed' => 'boolean',
        'safety_check_confirmed_at' => 'datetime',
    ];
}
