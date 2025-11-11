<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\CRM\User;

class PasswordController extends Controller
{
    /**
     * Show the password creation form.
     */
    public function showCreateForm($id)
    {
        $user = User::on('crm')->find($id);

        if (!$user) {
            Log::warning("[PASSWORD FORM] Invalid user id={$id}");
            abort(404, 'User not found.');
        }

        Log::info("[PASSWORD FORM] Loaded for id={$id} email={$user->email}");
        return view('auth.set-password', ['user' => $user]);
    }

    /**
     * Handle password submission.
     */
    public function store(Request $request, $id)
    {
        $request->validate([
            'password' => 'required|min:8|confirmed',
        ]);

        $user = User::on('crm')->find($id);

        if (!$user) {
            Log::warning("[PASSWORD SAVE FAILED] User not found id={$id}");
            return redirect()->back()->withErrors(['error' => 'User not found.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
            'account_status' => 'active',
        ]);

        Log::info("[PASSWORD SET] id={$id} email={$user->email}");
        return redirect('/login')->with('status', 'Password created successfully! You can now log in.');
    }
}