<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;

class RegistrationLog extends Model
{
    protected $connection = 'crm';
    protected $table = 'registration_logs';

    protected $fillable = [
        'ip_address',
        'email',
        'user_agent',
        'status',
        'reason',
    ];

    public $timestamps = false;
}