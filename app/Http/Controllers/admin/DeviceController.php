<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CRM\Device;
use App\Models\CRM\Customer;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function index()
    {
        $devices = Device::with('customer')
            ->orderByDesc('last_audit_at')
            ->paginate(20);

        return view('admin.devices.index', compact('devices'));
    }

    public function unassigned()
    {
        $devices = Device::with('customer')
            ->whereNull('customer_id')
            ->orderByDesc('last_audit_at')
            ->paginate(20);

        return view('admin.devices.unassigned', compact('devices'));
    }

    public function show(Device $device)
    {
        $device->load(['customer', 'audits' => function ($q) {
            $q->latest()->limit(10);
        }, 'apps']);

        // This assumes your CRM customers table has a 'name' column.
        // If it's 'company_name' or 'contact_name', just adjust here.
        $customers = Customer::on('crm')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.devices.show', compact('device', 'customers'));
    }

    public function assign(Request $request, Device $device)
    {
        $data = $request->validate([
            'customer_id' => ['required', 'integer'],
        ]);

        $customer = Customer::on('crm')->findOrFail($data['customer_id']);

        $device->customer_id = $customer->id;
        $device->save();

        return redirect()
            ->route('admin.devices.show', $device->id)
            ->with('status', 'Device assigned to ' . $customer->name . ' successfully.');
    }
}
