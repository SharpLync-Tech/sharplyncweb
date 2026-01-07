<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\CRM\User;

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
        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        if ($user->account_status !== 'active') {
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
