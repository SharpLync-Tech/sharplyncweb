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

    public function importForm()
{
    return view('admin.devices.import');
}

        public function importProcess(Request $request)
        {
            $request->validate([
                'audit_file' => ['required', 'file', 'mimes:json', 'max:5120'] // 5MB
            ]);

            $json = json_decode(file_get_contents($request->file('audit_file')->getRealPath()), true);

            if (!$json) {
                return back()->withErrors(['audit_file' => 'Invalid JSON file.']);
            }

            // Extract required fields
            $deviceName  = $json['Device'] ?? $json['device_name'] ?? null;
            $manufacturer = $json['System Information']['Computer System']['Manufacturer'] ?? null;
            $model        = $json['System Information']['Computer System']['Model'] ?? null;
            $osVersion    = $json['System Information']['Operating System']['Caption'] ?? null;

            $totalRam     = $json['System Information']['Computer System']['TotalPhysicalMemoryGB'] ?? null;
            $cpuModel     = $json['System Information']['Processor']['Name'] ?? null;
            $cpuCores     = $json['System Information']['Processor']['NumberOfCores'] ?? null;
            $cpuThreads   = $json['System Information']['Processor']['NumberOfLogicalProcessors'] ?? null;

            $diskUsedPct  = $json['Disk Usage']['UsedPercent'] ?? null;
            $diskSize     = $json['Disk Usage']['SizeGB'] ?? null;

            $antivirus    = $json['Antivirus Status']['displayName'] ?? null;

            // Try match device
            $device = Device::where('device_name', $deviceName)
                ->where('model', $model)
                ->where('manufacturer', $manufacturer)
                ->first();

            if (!$device) {
                $device = new Device();
            }

            $device->device_name          = $deviceName;
            $device->manufacturer         = $manufacturer;
            $device->model                = $model;
            $device->os_version           = $osVersion;
            $device->total_ram_gb         = $totalRam;
            $device->cpu_model            = $cpuModel;
            $device->cpu_cores            = $cpuCores;
            $device->cpu_threads          = $cpuThreads;
            $device->storage_size_gb      = $diskSize;
            $device->storage_used_percent = $diskUsedPct;
            $device->antivirus            = $json['Antivirus']['Name'] ?? null;
            $device->last_audit_at        = now();

            $device->save();

            // Save raw audit JSON
            $audit = DeviceAudit::create([
                'device_id'  => $device->id,
                'audit_json' => $json
            ]);

            // Save apps (if present)
            if (isset($json['Installed Applications']) && is_array($json['Installed Applications'])) {
                DeviceApp::where('device_id', $device->id)->delete();

                foreach ($json['Installed Applications'] as $app) {
                    DeviceApp::create([
                        'device_id' => $device->id,
                        'name'      => $app['DisplayName'] ?? 'Unknown',
                        'version'   => $app['DisplayVersion'] ?? null,
                        'publisher' => $app['Publisher'] ?? null,
                        'installed_on' => isset($app['InstallDate'])
                            ? \Carbon\Carbon::parse($app['InstallDate'])
                            : null,
                    ]);
                }
            }

            return redirect()
                ->route('admin.devices.index')
                ->with('status', 'Device audit imported successfully.');
        }

}