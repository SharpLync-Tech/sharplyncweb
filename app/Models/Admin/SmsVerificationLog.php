<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use App\Models\CRM\CustomerProfile;

class SmsVerificationLog extends Model
{
    protected $table = 'sms_verification_logs';

    protected $fillable = [
        'admin_id',
        'admin_name',
        'customer_profile_id',
        'recipient_name',
        'phone',
        'message',
        'verification_code',
        'message_id',
        'status',
    ];

    /**
     * Related customer profile (from CRM DB).
     * Not a foreign key constraint — just a reference.
     */
    public function customerProfile()
    {
        return $this->belongsTo(CustomerProfile::class, 'customer_profile_id');
    }
}
