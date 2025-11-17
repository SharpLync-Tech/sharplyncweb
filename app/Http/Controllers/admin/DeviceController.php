<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CRM\Device;
use App\Models\CRM\CustomerProfile;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function index()
    {
        $devices = Device::with('customerProfile')
            ->orderByDesc('last_audit_at')
            ->paginate(20);

        return view('admin.devices.index', compact('devices'));
    }

    public function unassigned()
    {
        $devices = Device::with('customerProfile')
            ->whereNull('customer_profile_id')
            ->orderByDesc('last_audit_at')
            ->paginate(20);

        return view('admin.devices.unassigned', compact('devices'));
    }

    public function show(Device $device)
    {
        $device->load([
            'customerProfile',
            'audits' => function ($q) {
                $q->latest()->limit(10);
            },
            'apps'
        ]);

        // Use business_name from customer_profiles table
        $customers = CustomerProfile::on('crm')
            ->orderBy('business_name')
            ->get(['id', 'business_name']);

        return view('admin.devices.show', compact('device', 'customers'));
    }

    public function assign(Request $request, Device $device)
    {
        $data = $request->validate([
            'customer_profile_id' => ['required', 'integer'],
        ]);

        $customer = CustomerProfile::on('crm')->findOrFail($data['customer_profile_id']);

        $device->customer_profile_id = $customer->id;
        $device->save();

        return redirect()
            ->route('admin.devices.show', $device->id)
            ->with('status', 'Device assigned to ' . $customer->business_name . ' successfully.');
    }
}