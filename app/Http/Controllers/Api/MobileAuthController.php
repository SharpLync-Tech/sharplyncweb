<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\SharpFleet\User;

class MobileAuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
            'device'   => 'nullable|string|max:100',
        ]);

        /** @var User|null $user */
        $user = User::query()
            ->where('email', $data['email'])
            ->whereNull('archived_at')
            ->first();

        // SharpFleet stores hashes in users.password_hash
        if (!$user || empty($user->password_hash) || !Hash::check($data['password'], $user->password_hash)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        if ($user->account_status !== 'active') {
            return response()->json([
                'message' => 'Account is not active',
            ], 403);
        }

        // Extra safety: handle the separate is_active flag when present.
        if (property_exists($user, 'is_active') && (int) ($user->is_active ?? 1) === 0) {
            return response()->json([
                'message' => 'Account is not active',
            ], 403);
        }

        // Optional: revoke old tokens for this device/app
        // $user->tokens()->delete();

        $tokenName = $data['device'] ?? 'sharpfleet-mobile';

        $token = $user->createToken($tokenName)->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => [
                'id'         => $user->id,
                'email'      => $user->email,
                'name'       => $user->full_name,
                'profile_ok' => $user->is_profile_complete,
            ],
        ]);
    }
}
