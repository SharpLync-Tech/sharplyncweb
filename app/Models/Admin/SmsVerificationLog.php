<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class SmsVerificationLog extends Model
{
    protected $table = 'sms_verification_logs';

    protected $fillable = [
        'admin_id',
        'customer_profile_id',
        'phone',
        'message',
        'verification_code',
        'message_id',
        'status',
    ];

    public function admin()
    {
        return $this->belongsTo(\App\Models\User::class, 'admin_id');
    }

    public function customerProfile()
    {
        return $this->belongsTo(\App\Models\CRM\CustomerProfile::class, 'customer_profile_id');
    }
}
