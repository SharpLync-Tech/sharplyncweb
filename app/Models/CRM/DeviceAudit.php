<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;

class DeviceAudit extends Model
{
    protected $connection = 'crm';
    protected $table = 'crm_device_audits';

    protected $fillable = [
        'device_id',
        'audit_json',
    ];

    protected $casts = [
        'audit_json' => 'array',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}
