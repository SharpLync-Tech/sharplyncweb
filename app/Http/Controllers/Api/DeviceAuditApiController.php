<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CRM\Device;
use App\Models\CRM\DeviceAudit;
use App\Models\CRM\DeviceApp;
use App\Models\CRM\CustomerProfile;
use Illuminate\Support\Carbon;

class DeviceAuditApiController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_profile_id'    => ['nullable', 'integer'],
            'device_name'            => ['required', 'string'],
            'manufacturer'           => ['nullable', 'string'],
            'model'                  => ['nullable', 'string'],
            'os_version'             => ['nullable', 'string'],
            'total_ram_gb'           => ['nullable', 'numeric'],
            'cpu_model'              => ['nullable', 'string'],
            'cpu_cores'              => ['nullable', 'integer'],
            'cpu_threads'            => ['nullable', 'integer'],
            'storage_size_gb'        => ['nullable', 'numeric'],
            'storage_used_percent'   => ['nullable', 'numeric'],
            'antivirus'              => ['nullable', 'string'],
            'raw_audit'              => ['required', 'array'],
        ]);

        // Try to find matching device
        $device = Device::where('device_name', $data['device_name'])
            ->where('model', $data['model'])
            ->where('manufacturer', $data['manufacturer'])
            ->first();

        if (! $device) {
            $device = new Device();
        }

        // Customer link
        if (!empty($data['customer_profile_id'])) {
            if (CustomerProfile::on('crm')->where('id', $data['customer_profile_id'])->exists()) {
                $device->customer_profile_id = $data['customer_profile_id'];
            }
        }

        // Populate device info
        $device->device_name = $data['device_name'];
        $device->manufacturer = $data['manufacturer'] ?? null;
        $device->model = $data['model'] ?? null;
        $device->os_version = $data['os_version'] ?? null;
        $device->total_ram_gb = $data['total_ram_gb'] ?? null;
        $device->cpu_model = $data['cpu_model'] ?? null;
        $device->cpu_cores = $data['cpu_cores'] ?? null;
        $device->cpu_threads = $data['cpu_threads'] ?? null;
        $device->storage_size_gb = $data['storage_size_gb'] ?? null;
        $device->storage_used_percent = $data['storage_used_percent'] ?? null;
        $device->antivirus = $data['antivirus'] ?? null;
        $device->last_audit_at = Carbon::now();

        $device->save();

        // Store full audit
        $audit = DeviceAudit::create([
            'device_id' => $device->id,
            'audit_json' => $data['raw_audit'],
        ]);

        // Store apps
        if (isset($data['raw_audit']['applications']) && is_array($data['raw_audit']['applications'])) {
            DeviceApp::where('device_id', $device->id)->delete();

            foreach ($data['raw_audit']['applications'] as $app) {
                DeviceApp::create([
                    'device_id' => $device->id,
                    'name' => $app['DisplayName'] ?? 'Unknown',
                    'version' => $app['DisplayVersion'] ?? null,
                    'publisher' => $app['Publisher'] ?? null,
                    'installed_on' => isset($app['InstallDate'])
                        ? $this->parseInstallDate($app['InstallDate'])
                        : null,
                ]);
            }
        }

        return response()->json([
            'status' => 'ok',
            'device_id' => $device->id,
            'audit_id' => $audit->id,
        ]);
    }

    protected function parseInstallDate($value)
    {
        try {
            if (preg_match('/^\d{8}$/', $value)) {
                return Carbon::createFromFormat('Ymd', $value);
            }
            return Carbon::parse($value);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
