<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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
     * Store new profile during onboarding
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'business_name' => 'required|string|max:150',
            'mobile_number' => 'required|string|max:20',
            'address_line1' => 'required|string|max:150',
            'city'          => 'nullable|string|max:100',
            'state'         => 'nullable|string|max:100',
            'postcode'      => 'required|string|max:10',
            'country'       => 'nullable|string|max:50',
        ]);

        $profile = CustomerProfile::updateOrCreate(
            ['user_id' => $user->id],
            array_merge($validated, [
                'setup_completed' => true,
            ])
        );

        /** OPTIONAL XERO SYNC — Safe Wrapped */
        try {
            $xero = new XeroService();

            $contactId = $xero->createContact([
                'business_name' => $validated['business_name'],
                'email'         => $user->email,
                'mobile_number' => $validated['mobile_number'],
                'address_line1' => $validated['address_line1'],
                'city'          => $validated['city'] ?? '',
                'state'         => $validated['state'] ?? '',
                'postcode'      => $validated['postcode'],
                'country'       => $validated['country'] ?? 'Australia',
            ]);

            $profile->update(['xero_contact_id' => $contactId]);

        } catch (\Exception $e) {
            \Log::error('Xero sync failed (onboarding): ' . $e->getMessage());
        }

        return redirect()->route('onboard.complete');
    }

    /**
     * Display the profile edit form
     */
    public function edit()
    {
        $user = Auth::user();

        // Create blank profile if missing
        if (!$user->profile) {
            $user->profile()->create([
                'account_number' => 'SL' . rand(100000, 999999),
                'business_name'  => $user->first_name . ' ' . $user->last_name,
                'mobile_number'  => $user->phone,
                'setup_completed' => 0,
                'country' => 'Australia',
            ]);
        }

        return view('customers.edit-profile', [
            'user' => $user,
            'profile' => $user->profile,
        ]);
    }

    /**
     * Update profile for existing customers
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        /** FULL VALIDATION — Includes all address fields */
        $validated = $request->validate([
            'business_name' => 'required|string|max:150',
            'mobile_number' => 'required|string|max:20',
            'address_line1' => 'nullable|string|max:150',
            'city'          => 'nullable|string|max:100',
            'state'         => 'nullable|string|max:100',
            'postcode'      => 'required|string|max:10',
            'country'       => 'nullable|string|max:50',
            'preferred_contact_method' => 'nullable|string|max:20',
            'notes'         => 'nullable|string',
        ]);

        /** UPDATE PROFILE MODEL */
        $user->profile->update([
            'business_name' => $validated['business_name'],
            'mobile_number' => $validated['mobile_number'],
            'address_line1' => $validated['address_line1'],
            'city'          => $validated['city'] ?? null,
            'state'         => $validated['state'] ?? null,
            'postcode'      => $validated['postcode'],
            'country'       => $validated['country'] ?? 'Australia',
            'preferred_contact_method' => $validated['preferred_contact_method'] ?? null,
            'notes'         => $validated['notes'] ?? null,
        ]);

        /** OPTIONAL XERO SYNC — UPDATE EXISTING CONTACT */
        try {
            if ($user->profile->xero_contact_id) {
                $xero = new XeroService();

                $xero->updateContact($user->profile->xero_contact_id, [
                    'business_name' => $validated['business_name'],
                    'email'         => $user->email,
                    'mobile_number' => $validated['mobile_number'],
                    'address_line1' => $validated['address_line1'],
                    'city'          => $validated['city'] ?? '',
                    'state'         => $validated['state'] ?? '',
                    'postcode'      => $validated['postcode'],
                    'country'       => $validated['country'] ?? 'Australia',
                ]);
            }

        } catch (\Exception $e) {
            \Log::error('Xero sync failed (update): ' . $e->getMessage());
        }

        return back()->with('success', 'Profile updated successfully.');
    }


    /* ================================================================
       NEW: UPDATE PROFILE PHOTO (CROPPED AVATAR)
    ================================================================= */

    public function updatePhoto(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'profile_photo' => 'required|image|max:2048', // 2MB max
        ]);

        // Delete old photo if exists
        if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
            Storage::disk('public')->delete($user->profile_photo);
        }

        // Save new avatar
        $path = $request->file('profile_photo')->store('profile-photos', 'public');

        $user->update([
            'profile_photo' => $path
        ]);

        return response()->json([
            'success' => true,
            'path'    => asset('storage/' . $path),
        ]);
    }


    /* ================================================================
       NEW: REMOVE PROFILE PHOTO (RESET TO INITIALS)
    ================================================================= */

    public function removePhoto()
    {
        $user = Auth::user();

        if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
            Storage::disk('public')->delete($user->profile_photo);
        }

        $user->update([
            'profile_photo' => null
        ]);

        return response()->json(['success' => true]);
    }
}
