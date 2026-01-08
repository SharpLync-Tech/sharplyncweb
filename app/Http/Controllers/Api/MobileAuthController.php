<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
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
            'email'  => (string) ($data['email'] ?? ''),
            'device' => (string) ($data['device'] ?? ''),
        ]);

        /** @var User|null $user */
        $user = User::query()
            ->where('email', $data['email'])
            ->whereNull('archived_at')
            ->first();

        // SharpFleet stores hashes in users.password_hash
        if (
            ! $user ||
            empty($user->password_hash) ||
            ! Hash::check($data['password'], $user->password_hash)
        ) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        if ($user->account_status !== 'active') {
            return response()->json([
                'message' => 'Account is not active',
            ], 403);
        }

        // Extra safety: handle the separate is_active flag
        if ((int) ($user->is_active ?? 1) === 0) {
            return response()->json([
                'message' => 'Account is not active',
            ], 403);
        }

        /**
         * 🔐 API KEY HANDLING (Option A)
         *
         * This key MUST be the same one used by the api.key middleware.
         * Do NOT generate a new key on every login.
         */
        if (empty($user->api_key)) {
            $user->api_key = 'sk_live_' . Str::random(48);
            $user->save();

            Log::info('[MobileAuth] API key generated', [
                'user_id' => $user->id,
            ]);
        }

        return response()->json([
            'api_key' => $user->api_key,
            'user' => [
                'id'         => $user->id,
                'email'      => $user->email,
                'name'       => $user->full_name,
                'profile_ok' => $user->is_profile_complete,
            ],
        ]);
    }
}
