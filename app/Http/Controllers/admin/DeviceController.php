<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CRM\Device;
use App\Models\CRM\CustomerProfile;
use Illuminate\Http\Request;
use App\Models\CRM\DeviceAudit;
use App\Models\CRM\DeviceApp;


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

    public function importForm()
{
    return view('admin.devices.import');
}

        public function importProcess(Request $request)
        {
            $request->validate([
                'audit_file' => 'required|file|mimes:json,txt',
            ]);

            $file = $request->file('audit_file');

            // --- New: strip UTF-8 BOM and handle large files ---
            $raw = file_get_contents($file->getRealPath());

            // Remove BOM if present
            $raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw);

            // Decode with max depth + BIG data flags
            $data = json_decode($raw, true, 512, JSON_BIGINT_AS_STRING);

            if ($data === null) {
                return back()->withErrors(['Invalid JSON file.']);
            }

            // --- Normal import logic continues here ---
            // Create device
            $device = Device::create([
                'customer_profile_id' => $data['customer_id'] ?? null,
                'device_name'         => $data['device_name'] ?? 'Unknown',
                'manufacturer'        => $data['manufacturer'] ?? '',
                'model'               => $data['model'] ?? '',
                'os_version'          => $data['os_version'] ?? '',
                'total_ram_gb'        => $data['total_ram_gb'] ?? 0,
                'cpu_model'           => $data['cpu_model'] ?? '',
                'cpu_cores'           => $data['cpu_cores'] ?? 0,
                'cpu_threads'         => $data['cpu_threads'] ?? 0,
                'storage_size_gb'     => $data['storage_size_gb'] ?? 0,
                'storage_used_percent'=> $data['storage_used_percent'] ?? 0,
                'antivirus'           => $data['antivirus'] ?? '',
                'last_audit_at'       => now(),
            ]);

            // Store audit
            DeviceAudit::create([
                'device_id' => $device->id,
                'audit_json'=> $raw, // Store raw JSON for later inspection
            ]);

            // Store apps
            foreach ($data['raw_audit']['applications'] ?? [] as $app) {
                DeviceApp::create([
                    'device_id' => $device->id,
                    'name'      => $app['DisplayName'] ?? '',
                    'version'   => $app['DisplayVersion'] ?? '',
                    'publisher' => $app['Publisher'] ?? '',
                    'installed_on' => $app['InstallDate'] ?? null,
                ]);
            }

            return redirect()
                ->route('admin.devices.index')
                ->with('status', 'Device audit imported successfully!');
        }


}