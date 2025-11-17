<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;

class CustomerContact extends Model
{
    protected $connection = 'crm';
    protected $table = 'customer_contacts';

    protected $fillable = [
        'customer_profile_id',
        'contact_name',
        'email',
        'phone',
        'is_primary',
    ];
}