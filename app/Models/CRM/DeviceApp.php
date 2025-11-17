<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;

class DeviceApp extends Model
{
    protected $connection = 'crm';
    protected $table = 'crm_device_apps';

    protected $fillable = [
        'device_id',
        'name',
        'version',
        'publisher',
        'installed_on',
    ];

    protected $casts = [
        'installed_on' => 'date',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}
