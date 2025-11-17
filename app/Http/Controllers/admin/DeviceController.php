<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CRM\Device;
use App\Models\CRM\CustomerProfile;
use App\Models\CRM\User;
use App\Models\CRM\DeviceAudit;
use App\Models\CRM\DeviceApp;
use Illuminate\Http\Request;
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
            'audits' => fn($q) => $q->latest()->limit(10),
            'apps'
        ]);

        $customers = CustomerProfile::on('crm')
            ->orderBy('business_name')
            ->get(['id', 'business_name']);

        return view('admin.devices.show', compact('device', 'customers'));
    }

    public function assign(Request $request, Device $device)
    {
        //
        // CASE 1 — CREATE NEW CUSTOMER FROM MODAL
        //
        if ($request->customer_profile_id === '__new__') {

            $request->validate([
                'cust_type' => 'required|in:individual,business',
            ]);

            //
            // INDIVIDUAL CUSTOMER
            //
            if ($request->cust_type === 'individual') {

                $request->validate([
                    'ind_first_name' => 'required|string|max:50',
                    'ind_last_name'  => 'required|string|max:50',
                    'ind_email'      => 'required|email|max:150',
                ]);

                // Create CRM user
                $user = User::on('crm')->create([
                    'first_name'     => $request->ind_first_name,
                    'last_name'      => $request->ind_last_name,
                    'email'          => $request->ind_email,
                    'auth_provider'  => 'local',
                    'account_status' => 'pending',
                ]);

                // Create profile
                $profile = CustomerProfile::on('crm')->create([
                    'user_id'         => $user->id,
                    'business_name'   => $request->ind_first_name . ' ' . $request->ind_last_name,
                    'accounts_email'  => $request->ind_email,
                    'mobile_number'   => null,
                    'landline_number' => null,
                    'address_line1'   => null,
                    'city'            => null,
                    'postcode'        => null,
                    'setup_completed' => 0,
                ]);

                // Primary contact entry
                $profile->contacts()->create([
                    'contact_name' => $request->ind_first_name . ' ' . $request->ind_last_name,
                    'email'        => $request->ind_email,
                    'is_primary'   => 1,
                ]);
            }

            //
            // BUSINESS CUSTOMER
            //
            if ($request->cust_type === 'business') {

                $request->validate([
                    'biz_name'        => 'required|string|max:150',
                    'biz_first_name'  => 'required|string|max:50',
                    'biz_last_name'   => 'required|string|max:50',
                    'biz_email'       => 'required|email|max:150',
                ]);

                // Create CRM user (primary contact)
                $user = User::on('crm')->create([
                    'first_name'     => $request->biz_first_name,
                    'last_name'      => $request->biz_last_name,
                    'email'          => $request->biz_email,
                    'auth_provider'  => 'local',
                    'account_status' => 'pending',
                ]);

                // Create business customer profile
                $profile = CustomerProfile::on('crm')->create([
                    'user_id'         => $user->id,
                    'business_name'   => $request->biz_name,
                    'accounts_email'  => $request->biz_email,
                    'mobile_number'   => null,
                    'landline_number' => null,
                    'address_line1'   => null,
                    'city'            => null,
                    'postcode'        => null,
                    'setup_completed' => 0,
                ]);

                // Primary contact entry
                $profile->contacts()->create([
                    'contact_name' => $request->biz_first_name . ' ' . $request->biz_last_name,
                    'email'        => $request->biz_email,
                    'is_primary'   => 1,
                ]);
            }

            // ASSIGN DEVICE
            $device->customer_profile_id = $profile->id;
            $device->save();

            return redirect()
                ->route('admin.devices.show', $device->id)
                ->with('status', "Customer created & device assigned to {$profile->business_name}");
        }

        //
        // CASE 2 — ASSIGN TO EXISTING CUSTOMER
        //
        $data = $request->validate([
            'customer_profile_id' => ['required', 'integer', 'exists:customer_profiles,id'],
        ]);

        $customer = CustomerProfile::on('crm')->find($data['customer_profile_id']);

        $device->customer_profile_id = $customer->id;
        $device->save();

        return redirect()
            ->route('admin.devices.show', $device->id)
            ->with('status', "Device assigned to {$customer->business_name}.");
    }


    // ---------------------------------------------------------
    // DEVICE IMPORT FORM
    // ---------------------------------------------------------
    public function importForm()
    {
        return view('admin.devices.import');
    }

    // ---------------------------------------------------------
    // DEVICE IMPORT PROCESS
    // ---------------------------------------------------------
    public function importProcess(Request $request)
    {
        $request->validate([
            'audit_file' => 'required|file|mimes:json,txt',
        ]);

        $file = $request->file('audit_file');

        $raw = file_get_contents($file->getRealPath());
        $raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw);

        $data = json_decode($raw, true, 512, JSON_BIGINT_AS_STRING);

        if ($data === null) {
            return back()->withErrors(['Invalid JSON file']);
        }

        $device = Device::create([
            'customer_profile_id'  => $data['customer_id'] ?? null,
            'device_name'          => $data['device_name'] ?? 'Unknown',
            'manufacturer'         => $data['manufacturer'] ?? '',
            'model'                => $data['model'] ?? '',
            'os_version'           => $data['os_version'] ?? '',
            'total_ram_gb'         => $data['total_ram_gb'] ?? 0,
            'cpu_model'            => $data['cpu_model'] ?? '',
            'cpu_cores'            => $data['cpu_cores'] ?? 0,
            'cpu_threads'          => $data['cpu_threads'] ?? 0,
            'storage_size_gb'      => $data['storage_size_gb'] ?? 0,
            'storage_used_percent' => $data['storage_used_percent'] ?? 0,
            'antivirus'            => $data['antivirus'] ?? '',
            'last_audit_at'        => now(),
        ]);

        DeviceAudit::create([
            'device_id' => $device->id,
            'audit_json'=> $raw,
        ]);

        foreach ($data['raw_audit']['applications'] ?? [] as $app) {
            DeviceApp::create([
                'device_id'    => $device->id,
                'name'         => $app['DisplayName'] ?? '',
                'version'      => $app['DisplayVersion'] ?? '',
                'publisher'    => $app['Publisher'] ?? '',
                'installed_on' => $app['InstallDate'] ?? null,
            ]);
        }

        return redirect()
            ->route('admin.devices.index')
            ->with('status', 'Device audit imported successfully!');
    }

    // ---------------------------------------------------------
    // DELETE DEVICE
    // ---------------------------------------------------------
    public function destroy(Device $device)
    {
        $device->audits()->delete();
        $device->apps()->delete();
        $device->delete();

        return redirect()
            ->route('admin.devices.index')
            ->with('status', 'Device and all audit data deleted.');
    }
}
