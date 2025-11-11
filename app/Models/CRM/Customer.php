<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $table = 'customers'; // This matches the table in your screenshot

    protected $fillable = [
        'user_id',
        'account_number',
        'business_name',
        'abn',
        'authority_contact',
        'accounts_email',
        'mobile_number',
        'landline_number',
        'preferred_contact_method',
        'marketing_opt_in',
        'preferred_support_window',
        'timezone',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'postcode',
        'country',
        'notes',
        'documents_path',
        'setup_completed',
    ];
}