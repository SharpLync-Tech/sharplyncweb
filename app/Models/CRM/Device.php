<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $connection = 'crm';
    protected $table = 'crm_devices';

    protected $fillable = [
        'customer_profile_id',
        'device_name',
        'device_type',
        'manufacturer',
        'model',
        'serial_number',
        'os_version',
        'total_ram_gb',
        'cpu_model',
        'cpu_cores',
        'cpu_threads',
        'storage_size_gb',
        'storage_used_percent',
        'antivirus',
        'last_audit_at',
    ];

    public function customerProfile()
    {
        return $this->belongsTo(CustomerProfile::class, 'customer_profile_id');
    }

    public function audits()
    {
        return $this->hasMany(DeviceAudit::class, 'device_id');
    }

    public function apps()
    {
        return $this->hasMany(DeviceApp::class, 'device_id');
    }
}
