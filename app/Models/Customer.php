<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'company_name',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'postcode',
        'xero_contact_id',
        'xero_sync_status',
    ];

    // Relationship to support_pins
    public function supportPins()
    {
        return $this->hasMany(\DB::class, 'customer_id');
    }
}