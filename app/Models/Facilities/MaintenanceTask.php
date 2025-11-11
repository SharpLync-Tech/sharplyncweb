<?php

namespace App\Models\Facilities;

use Illuminate\Database\Eloquent\Model;

class MaintenanceTask extends Model
{
    protected $connection = 'sharplync_facilities';
    protected $table = 'maintenance_tasks';

    protected $fillable = [
        'asset_id',
        'project_id',
        'description',
        'assigned_to',
        'status',
        'due_date',
        'completed_at',
    ];

    public $timestamps = true;
}