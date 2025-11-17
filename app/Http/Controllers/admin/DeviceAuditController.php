<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CRM\Device;
use App\Models\CRM\DeviceAudit;

class DeviceAuditController extends Controller
{
    public function index(Device $device)
    {
        $audits = $device->audits()->latest()->paginate(20);

        return view('admin.devices.audit-history', compact('device', 'audits'));
    }

    public function show(Device $device, DeviceAudit $audit)
    {
        // simple safety check – ensure audit belongs to device
        if ($audit->device_id !== $device->id) {
            abort(404);
        }

        return view('admin.devices.audit-show', compact('device', 'audit'));
    }
}
