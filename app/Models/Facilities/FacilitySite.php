<?php

namespace App\Models\Facilities;

use Illuminate\Database\Eloquent\Model;

class FacilitySite extends Model
{
    protected $connection = 'sharplync_facilities';
    protected $table = 'facility_sites';

    protected $fillable = [
        'crm_customer_id',
        'site_name',
        'address_line1',
        'address_line2',
        'suburb',
        'state',
        'postcode',
        'contact_person',
        'contact_phone',
        'notes',
    ];

    public $timestamps = true;
}