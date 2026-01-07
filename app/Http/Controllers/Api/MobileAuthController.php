<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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

        // Minimal request logging for diagnostics (never log passwords).
        Log::info('[MobileAuth] Login attempt', [
            'email' => (string) ($data['email'] ?? ''),
            'device' => (string) ($data['device'] ?? ''),
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

        // Extra safety: handle the separate is_active flag (column exists in sharpfleet.users).
        if ((int) ($user->is_active ?? 1) === 0) {
            return response()->json([
                'message' => 'Account is not active',
            ], 403);
        }

        // Optional: revoke old tokens for this device/app
        // $user->tokens()->delete();

        $tokenName = $data['device'] ?? 'sharpfleet-mobile';

        try {
            $token = $user->createToken($tokenName)->plainTextToken;
        } catch (\Throwable $e) {
            Log::error('[MobileAuth] Token creation failed', [
                'email' => (string) ($data['email'] ?? ''),
                'device' => (string) ($data['device'] ?? ''),
                'exception' => get_class($e),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Token creation failed',
            ], 500);
        }

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
