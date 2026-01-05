<?php

namespace App\Http\Controllers\SharpFleet;

use App\Http\Controllers\Controller;
use App\Support\SharpFleet\Roles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    private const ARCHIVED_LOGIN_MESSAGE = 'This account has been archived. Please contact your administrator.';

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
        $token = $request->cookie('sharpfleet_remember');
        if ($token && Schema::connection('sharpfleet')->hasColumn('users', 'remember_token')) {
            $user = $this->getUserByRememberToken($token);

            if ($user) {
                if ($this->isArchived($user)) {
                    Cookie::queue(Cookie::forget('sharpfleet_remember'));
                    return view('sharpfleet.login')->withErrors([
                        'email' => self::ARCHIVED_LOGIN_MESSAGE,
                    ]);
                }
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

        if ($user && $this->isArchived($user)) {
            return back()->withErrors([
                'email' => self::ARCHIVED_LOGIN_MESSAGE,
            ]);
        }

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
        if ($request->boolean('remember') && Schema::connection('sharpfleet')->hasColumn('users', 'remember_token')) {
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
        // SharpFleet auth is stored under the 'sharpfleet.user' session key.
        // Only clear SharpFleet session data (do not invalidate the entire Laravel session).
        $request->session()->forget('sharpfleet.user');
        Cookie::queue(Cookie::forget('sharpfleet_remember'));

        // Redirect to the public SharpFleet landing page on the canonical host (no :8080).
        return redirect()->away('https://' . $request->getHost() . '/sharpfleet');
    }

    // ======================================================
    // Internal helpers (SharpFleet only)
    // ======================================================

    private function startSession(Request $request, $user): void
    {
        $request->session()->regenerate();

        $archivedAt = null;
        if (Schema::connection('sharpfleet')->hasColumn('users', 'archived_at')) {
            $archivedAt = $user->archived_at ?? null;
        }

        $request->session()->put('sharpfleet.user', [
            'id'              => $user->id,
            'organisation_id' => $user->organisation_id,
            'email'           => $user->email,
            'first_name'      => $user->first_name ?? '',
            'last_name'       => $user->last_name ?? '',
            'name'            => trim($user->first_name . ' ' . $user->last_name),
            'role'            => Roles::normalize($user->role ?? null),
            'is_driver'       => (int) ($user->is_driver ?? 0),
            'archived_at'     => $archivedAt,
            'logged_in'       => true,
        ]);
    }

    private function redirectByRole(string $role)
    {
        $role = Roles::normalize($role);

        return match ($role) {
            Roles::COMPANY_ADMIN,
            Roles::BRANCH_ADMIN,
            Roles::BOOKING_ADMIN => redirect('/app/sharpfleet/admin'),
            Roles::DRIVER => redirect('/app/sharpfleet/driver'),
            default => abort(403, 'Unknown SharpFleet role'),
        };
    }

    private function getUserByRememberToken(string $token)
    {
        return DB::connection('sharpfleet')
            ->table('users')
            ->where('remember_token', hash('sha256', $token))
            ->first();
    }

    private function isArchived($user): bool
    {
        if (!Schema::connection('sharpfleet')->hasColumn('users', 'archived_at')) {
            return false;
        }

        return !empty($user->archived_at);
    }
}
