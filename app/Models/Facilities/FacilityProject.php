<?php

namespace App\Models\Facilities;

use Illuminate\Database\Eloquent\Model;

class FacilityProject extends Model
{
    protected $connection = 'sharplync_facilities';
    protected $table = 'facility_projects';

    protected $fillable = [
        'site_id',
        'project_name',
        'description',
        'status',
        'start_date',
        'end_date',
        'budget',
    ];

    public $timestamps = true;
}