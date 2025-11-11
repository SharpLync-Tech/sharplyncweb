<?php

namespace App\Models\Facilities;

use Illuminate\Database\Eloquent\Model;

class FacilityAsset extends Model
{
    protected $connection = 'sharplync_facilities';
    protected $table = 'facility_assets';

    protected $fillable = [
        'site_id',
        'asset_name',
        'asset_type',
        'serial_number',
        'purchase_date',
        'warranty_expiry',
        'status',
        'notes',
    ];

    public $timestamps = true;
}