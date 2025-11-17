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
    //
    // CASE 1: Creating a new customer through the modal
    //
    if ($request->customer_profile_id === '__new__') {

        $request->validate([
            'cust_type' => 'required|in:individual,business',
        ]);

        // --------------------------------------------
        // INDIVIDUAL CUSTOMER
        // --------------------------------------------
        if ($request->cust_type === 'individual') {

            $request->validate([
                'ind_first_name' => 'required|string|max:50',
                'ind_last_name'  => 'required|string|max:50',
                'ind_email'      => 'required|email|max:150',
            ]);

            // 1) Create CRM user
            $user = \App\Models\CRM\User::on('crm')->create([
                'first_name' => $request->ind_first_name,
                'last_name'  => $request->ind_last_name,
                'email'      => $request->ind_email,
                'auth_provider' => 'local',
                'account_status' => 'pending',
            ]);

            // 2) Create CRM profile
            $profile = CustomerProfile::on('crm')->create([
                'user_id'        => $user->id,
                'business_name'  => $request->ind_first_name . ' ' . $request->ind_last_name,
                'accounts_email' => $request->ind_email,
                'setup_completed' => 0,
            ]);

            // 3) Create contact
            $profile->contacts()->create([
                'contact_name' => $request->ind_first_name . ' ' . $request->ind_last_name,
                'email'        => $request->ind_email,
                'is_primary'   => 1,
            ]);
        }

        // --------------------------------------------
        // BUSINESS CUSTOMER
        // --------------------------------------------
        if ($request->cust_type === 'business') {

            $request->validate([
                'biz_name'        => 'required|string|max:150',
                'biz_first_name'  => 'required|string|max:50',
                'biz_last_name'   => 'required|string|max:50',
                'biz_email'       => 'required|email|max:150',
            ]);

            // 1) Create CRM user (primary contact)
            $user = \App\Models\CRM\User::on('crm')->create([
                'first_name' => $request->biz_first_name,
                'last_name'  => $request->biz_last_name,
                'email'      => $request->biz_email,
                'auth_provider' => 'local',
                'account_status' => 'pending',
            ]);

            // 2) Create CRM customer profile
            $profile = CustomerProfile::on('crm')->create([
                'user_id'        => $user->id,
                'business_name'  => $request->biz_name,
                'accounts_email' => $request->biz_email,
                'setup_completed' => 0,
            ]);

            // 3) Primary contact entry
            $profile->contacts()->create([
                'contact_name' => $request->biz_first_name . ' ' . $request->biz_last_name,
                'email'        => $request->biz_email,
                'is_primary'   => 1,
            ]);
        }

        // --------------------------------------------
        // ASSIGN DEVICE
        // --------------------------------------------
        $device->customer_profile_id = $profile->id;
        $device->save();

        // --------------------------------------------
        // OPTIONAL: send welcome email later
        // --------------------------------------------
        if ($request->has('send_welcome_email')) {
            // TODO: send email hook here
        }

        return redirect()
            ->route('admin.devices.show', $device->id)
            ->with('status', "Customer created & device assigned to {$profile->business_name}");
    }


                //
                // CASE 2: Assign to existing customer
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

}
