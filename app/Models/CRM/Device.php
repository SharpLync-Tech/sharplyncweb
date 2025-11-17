<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $connection = 'crm';
    protected $table = 'crm_devices';

    protected $fillable = [
        'customer_id',
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

    protected $casts = [
        'total_ram_gb' => 'float',
        'storage_size_gb' => 'float',
        'storage_used_percent' => 'float',
        'last_audit_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function audits()
    {
        return $this->hasMany(DeviceAudit::class);
    }

    public function apps()
    {
        return $this->hasMany(DeviceApp::class);
    }
}