<?php

namespace App\Http\Controllers\SharpFleet;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Show SharpFleet login page
     */
    public function showLogin(Request $request)
    {
        // Already logged into SharpFleet
        if ($request->session()->has('sharpfleet.user')) {
            return $this->redirectByRole(
                $request->session()->get('sharpfleet.user.role')
            );
        }

        // Attempt remember-me login
        if ($token = $request->cookie('sharpfleet_remember')) {
            $user = $this->getUserByRememberToken($token);

            if ($user) {
                $this->startSession($request, $user);
                return $this->redirectByRole($user->role);
            }
        }

        return view('sharpfleet.login');
    }

    /**
     * Handle SharpFleet login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = DB::connection('sharpfleet')
            ->table('users')
            ->where('email', $request->email)
            ->first();

        if (
            !$user ||
            empty($user->password_hash) ||
            !Hash::check($request->password, $user->password_hash)
        ) {
            return back()->withErrors([
                'email' => 'Invalid credentials',
            ]);
        }

        $this->startSession($request, $user);

        // Remember this device
        if ($request->boolean('remember')) {
            $token = Str::random(64);

            DB::connection('sharpfleet')
                ->table('users')
                ->where('id', $user->id)
                ->update([
                    'remember_token' => hash('sha256', $token),
                ]);

            Cookie::queue(
                'sharpfleet_remember',
                $token,
                60 * 24 * 30 // 30 days
            );
        }

        return $this->redirectByRole($user->role);
    }

    /**
     * Logout SharpFleet user only
     */
    public function logout(Request $request)
    {
        $request->session()->forget('sharpfleet');
        Cookie::queue(Cookie::forget('sharpfleet_remember'));

        return redirect('/app/sharpfleet/login');
    }

    // ======================================================
    // Internal helpers (SharpFleet only)
    // ======================================================

    private function startSession(Request $request, $user): void
    {
        $request->session()->regenerate();

        $request->session()->put('sharpfleet.user', [
            'id'              => $user->id,
            'organisation_id' => $user->organisation_id,
            'email'           => $user->email,
            'name'            => trim($user->first_name . ' ' . $user->last_name),
            'role'            => $user->role, // admin | driver
            'logged_in'       => true,
        ]);
    }

    private function redirectByRole(string $role)
    {
        return match ($role) {
            'admin'  => redirect('/app/sharpfleet/admin'),
            'driver' => redirect('/app/sharpfleet/driver'),
            default  => abort(403, 'Unknown SharpFleet role'),
        };
    }

    private function getUserByRememberToken(string $token)
    {
        return DB::connection('sharpfleet')
            ->table('users')
            ->where('remember_token', hash('sha256', $token))
            ->first();
    }
}
