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
     * Onboarding screen
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
     * Store profile during onboarding
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
            array_merge($validated, ['setup_completed' => true])
        );

        // Optional Xero sync
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
            \Log::error('Xero sync failed: ' . $e->getMessage());
        }

        return redirect()->route('onboard.complete');
    }

    /**
     * Edit profile
     */
    public function edit()
    {
        $user = Auth::user();

        if (!$user->profile) {
            $user->profile()->create([
                'account_number' => 'SL' . rand(100000, 999999),
                'business_name'  => $user->first_name . ' ' . $user->last_name,
                'mobile_number'  => $user->phone,
                'country'        => 'Australia',
                'setup_completed' => 0,
            ]);
        }

        return view('customers.edit-profile', [
            'user' => $user,
            'profile' => $user->profile,
        ]);
    }

    /**
     * Update profile
     */
    public function update(Request $request)
    {
        $user = Auth::user();

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

        $user->profile->update($validated);

        // Optional Xero sync
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
            \Log::error('Xero sync failed: ' . $e->getMessage());
        }

        return back()->with('success', 'Profile updated successfully.');
    }


    // ======================================================
    // PROFILE PHOTO — UPLOAD
    // ======================================================

    public function updatePhoto(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'profile_photo' => 'required|image|max:2048',
        ]);

        if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
            Storage::disk('public')->delete($user->profile_photo);
        }

        $path = $request->file('profile_photo')->store('profile-photos', 'public');

        $user->update([
            'profile_photo' => $path
        ]);

        return response()->json([
            'success' => true,
            'path' => asset('storage/' . $path),
        ]);
    }

    // ======================================================
    // PROFILE PHOTO — REMOVE
    // ======================================================

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
