<?php

namespace App\Models\Facilities;

use Illuminate\Database\Eloquent\Model;

class FleetVehicle extends Model
{
    protected $connection = 'sharplync_facilities';
    protected $table = 'fleet_vehicles';

    protected $fillable = [
        'crm_customer_id',
        'registration_number',
        'make',
        'model',
        'year',
        'vin_number',
        'odometer',
        'service_due_date',
        'assigned_driver',
        'status',
        'notes',
    ];

    public $timestamps = true;
}