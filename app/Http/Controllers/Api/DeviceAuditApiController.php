<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CRM\Device;
use App\Models\CRM\DeviceAudit;
use App\Models\CRM\DeviceApp;
use App\Models\CRM\Customer;
use Illuminate\Support\Carbon;

class DeviceAuditApiController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id'           => ['nullable', 'integer'],
            'device_name'           => ['required', 'string', 'max:255'],
            'manufacturer'          => ['nullable', 'string', 'max:255'],
            'model'                 => ['nullable', 'string', 'max:255'],
            'os_version'            => ['nullable', 'string', 'max:255'],
            'total_ram_gb'          => ['nullable', 'numeric'],
            'cpu_model'             => ['nullable', 'string', 'max:255'],
            'cpu_cores'             => ['nullable', 'integer'],
            'cpu_threads'           => ['nullable', 'integer'],
            'storage_size_gb'       => ['nullable', 'numeric'],
            'storage_used_percent'  => ['nullable', 'numeric'],
            'antivirus'             => ['nullable', 'string', 'max:255'],
            'raw_audit'             => ['required', 'array'],
        ]);

        // try to find existing device by name + model + manufacturer
        $device = Device::where('device_name', $data['device_name'])
            ->where('model', $data['model'])
            ->where('manufacturer', $data['manufacturer'])
            ->first();

        if (! $device) {
            $device = new Device();
        }

        if (! empty($data['customer_id'])) {
            // optional: verify customer exists
            if (Customer::on('crm')->where('id', $data['customer_id'])->exists()) {
                $device->customer_id = $data['customer_id'];
            }
        }

        $device->device_name          = $data['device_name'];
        $device->manufacturer         = $data['manufacturer'] ?? null;
        $device->model                = $data['model'] ?? null;
        $device->os_version           = $data['os_version'] ?? null;
        $device->total_ram_gb         = $data['total_ram_gb'] ?? null;
        $device->cpu_model            = $data['cpu_model'] ?? null;
        $device->cpu_cores            = $data['cpu_cores'] ?? null;
        $device->cpu_threads          = $data['cpu_threads'] ?? null;
        $device->storage_size_gb      = $data['storage_size_gb'] ?? null;
        $device->storage_used_percent = $data['storage_used_percent'] ?? null;
        $device->antivirus            = $data['antivirus'] ?? null;
        $device->last_audit_at        = Carbon::now();

        $device->save();

        // store full audit
        $audit = DeviceAudit::create([
            'device_id'  => $device->id,
            'audit_json' => $data['raw_audit'],
        ]);

        // refresh installed apps
        if (isset($data['raw_audit']['applications']) && is_array($data['raw_audit']['applications'])) {
            DeviceApp::where('device_id', $device->id)->delete();

            foreach ($data['raw_audit']['applications'] as $app) {
                DeviceApp::create([
                    'device_id'   => $device->id,
                    'name'        => $app['DisplayName'] ?? 'Unknown',
                    'version'     => $app['DisplayVersion'] ?? null,
                    'publisher'   => $app['Publisher'] ?? null,
                    'installed_on'=> isset($app['InstallDate']) && $app['InstallDate']
                        ? $this->parseInstallDate($app['InstallDate'])
                        : null,
                ]);
            }
        }

        return response()->json([
            'status'   => 'ok',
            'device_id'=> $device->id,
            'audit_id' => $audit->id,
        ]);
    }

    protected function parseInstallDate($value)
    {
        // your audit uses yyyymmdd or yyyymmdd-style strings in some cases
        if (preg_match('/^\d{8}$/', $value)) {
            return Carbon::createFromFormat('Ymd', $value)->startOfDay();
        }

        // fallback – let Carbon try
        try {
            return Carbon::parse($value);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
