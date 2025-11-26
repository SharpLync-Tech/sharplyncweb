<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CRM\CustomerProfile;
use App\Services\XeroService;

class ProfileController extends Controller
{
    /**
     * Display the setup profile form (onboarding)
     */
    public function create()
    {
        $user = Auth::user();

        if ($user && $user->profile && $user->profile->setup_completed) {
            return redirect()->route('onboard.complete');
        }

        return view('customers.setup-profile', [
            'user' => $user,
            'profile' => $user->profile ?? null,
        ]);
    }

    /**
     * Store new profile data during onboarding
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'business_name'   => 'required|string|max:150',
            'mobile_number'   => 'required|string|max:20',
            'address_line1'   => 'required|string|max:150',
            'postcode'        => 'required|string|max:10',
        ]);

        $profile = CustomerProfile::updateOrCreate(
            ['user_id' => $user->id],
            array_merge($validated, [
                'setup_completed' => true,
            ])
        );

        // Optional Xero sync
        try {
            $xero = new XeroService();
            $contactId = $xero->createContact([
                'business_name' => $validated['business_name'],
                'email'         => $user->email,
                'mobile_number' => $validated['mobile_number'],
                'address_line1' => $validated['address_line1'],
            ]);

            $profile->update(['xero_contact_id' => $contactId]);
        } catch (\Exception $e) {
            \Log::error('Xero sync failed: ' . $e->getMessage());
        }

        return redirect()->route('onboard.complete');
    }

    /**
     * Display the editable profile form (post-onboarding)
     */
    public function edit()
    {
        $user = Auth::user();

        // Auto-create a blank profile if missing
        if (!$user->profile) {
            $user->profile()->create([
                'account_number' => 'SL' . rand(100000, 999999),
                'business_name'  => $user->first_name . ' ' . $user->last_name,
                'mobile_number'  => $user->phone,
                'setup_completed' => 0,
            ]);
        }

        return view('customers.edit-profile', [
            'user' => $user,
            'profile' => $user->profile,
        ]);
    }

    /**
     * Update profile data for existing customers
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'business_name'   => 'required|string|max:150',
            'mobile_number'   => 'required|string|max:20',
            'address_line1'   => 'required|string|max:150',
            'postcode'        => 'required|string|max:10',
            'preferred_contact_method' => 'nullable|string|max:20',
            'notes'           => 'nullable|string',
        ]);

        $user->profile->update($validated);

        return back()->with('success', 'Profile updated successfully.');
    }
}
