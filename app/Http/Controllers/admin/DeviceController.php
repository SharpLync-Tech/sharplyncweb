<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CRM\Device;
use App\Models\CRM\CustomerProfile;
use App\Models\CRM\User; // ⭐ MUST import CRM User
use Illuminate\Http\Request;
use App\Models\CRM\DeviceAudit;
use App\Models\CRM\DeviceApp;
use Illuminate\Support\Str;

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

        $customers = CustomerProfile::on('crm')
            ->orderBy('business_name')
            ->get(['id', 'business_name']);

        return view('admin.devices.show', compact('device', 'customers'));
    }

    public function assign(Request $request, Device $device)
    {
        /**
         * ============================================================
         *  CASE 1 — CREATE NEW CUSTOMER (Admin-selected)
         * ============================================================
         */
        if ($request->customer_profile_id === '__new__') {

            $request->validate([
                'new_customer_name'  => 'required|string|max:150',
                'new_customer_email' => 'required|email|max:150',
            ]);

            // Split business name into workable name fields
            $nameParts = explode(' ', trim($request->new_customer_name));
            $firstName = $nameParts[0] ?? 'Customer';
            $lastName  = $nameParts[1] ?? 'Account';

            // ============================================================
            // ⭐ 1. CREATE NEW CRM USER (future-proof)
            // ============================================================
            $user = User::on('crm')->create([
                'first_name'         => $firstName,
                'last_name'          => $lastName,
                'email'              => $request->new_customer_email,
                'auth_provider'      => 'local',
                'password'           => bcrypt(Str::random(20)), // random, never used
                'account_status'     => 'active',
                'email_verified_at'  => now(),
                'accepted_terms_at'  => now(),
                'sspin'              => strtoupper(Str::random(8)),
            ]);

            // Create SharpLync account number (simple but unique)
            $accountNumber = 'SL' . now()->format('dmHi');

            // ============================================================
            // ⭐ 2. CREATE CUSTOMER PROFILE (linked to user)
            // ============================================================
            $customer = CustomerProfile::on('crm')->create([
                'user_id'        => $user->id,
                'business_name'  => $request->new_customer_name,
                'accounts_email' => $request->new_customer_email,
                'account_number' => $accountNumber,
                'mobile_number'       => '',   // required by DB
                'landline_number'     => '',   // required by DB
                'setup_completed' => 0,
            ]);

            // ============================================================
            // ⭐ 3. ASSIGN DEVICE
            // ============================================================
            $device->customer_profile_id = $customer->id;
            $device->save();

            return redirect()
                ->route('admin.devices.show', $device->id)
                ->with('status', 'Device assigned to NEW customer: ' . $customer->business_name);
        }

        /**
         * ============================================================
         *  CASE 2 — ASSIGN EXISTING CUSTOMER
         * ============================================================
         */
        $data = $request->validate([
            'customer_profile_id' => ['required', 'integer', 'exists:customer_profiles,id'],
        ]);

        $customer = CustomerProfile::on('crm')->find($data['customer_profile_id']);

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

        // Strip BOM
        $raw = file_get_contents($file->getRealPath());
        $raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw);

        // Decode JSON safely
        $data = json_decode($raw, true, 512, JSON_BIGINT_AS_STRING);

        if ($data === null) {
            return back()->withErrors(['Invalid JSON file.']);
        }

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
            'audit_json'=> $raw,
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

    public function destroy(Device $device)
    {
        $device->audits()->delete();
        $device->apps()->delete();
        $device->delete();

        return redirect()
            ->route('admin.devices.index')
            ->with('status', 'Device and all related audit data deleted successfully.');
    }
}
