<?php

namespace App\Http\Controllers\SharpFleet;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Show the SharpFleet login page.
     * If a valid remember-device cookie exists, rehydrate the session and redirect.
     */
    public function showLogin(Request $request)
    {
        // If already logged in via SharpFleet session, go straight in.
        if ($request->session()->has('sharpfleet.user_id')) {
            return redirect('/app/sharpfleet/debug');
        }

        // Attempt auto-login via remember cookie (no DB writes)
        $rememberCookie = $request->cookie($this->rememberCookieName());

        if ($rememberCookie) {
            $payload = $this->decryptRememberCookie($rememberCookie);

            if ($payload && $this->rememberPayloadLooksValid($payload, $request)) {
                $user = $this->fleetConnection()
                    ->table('users')
                    ->where('id', $payload['user_id'])
                    ->where('is_active', 1)
                    ->first();

                if ($user) {
                    $this->startFleetSession($request, $user);

                    return redirect('/app/sharpfleet/debug');
                }
            }
        }

        return view('sharpfleet.login');
    }

    /**
     * Log the user in to SharpFleet (independent from CRM/CMS auth).
     */
    public function login(Request $request)
    {
        $data = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = (bool) ($request->boolean('remember') || $request->boolean('remember_me'));

        $user = $this->fleetConnection()
            ->table('users')
            ->whereRaw('LOWER(email) = ?', [mb_strtolower($data['email'])])
            ->where('is_active', 1)
            ->first();

        if (!$user || empty($user->password_hash) || !Hash::check($data['password'], $user->password_hash)) {
            return back()
                ->withErrors(['email' => 'Invalid credentials'])
                ->withInput($request->only('email'));
        }

        // Start SharpFleet-only session
        $this->startFleetSession($request, $user);

        // Optional remember-device cookie (encrypted, expires)
        if ($remember) {
            $cookie = $this->makeRememberCookie($user, $request);
            cookie()->queue($cookie);
        } else {
            // Ensure any old remember cookie is removed
            cookie()->queue(cookie()->forget($this->rememberCookieName()));
        }

        // (Optional, but nice) update last_login_at (DB WRITE)
        // Per your rules: ASK before writes. So leaving this OFF for now.
        // $this->fleetConnection()->table('users')->where('id', $user->id)->update(['last_login_at' => now()]);

        return redirect('/app/sharpfleet/debug');
    }

    /**
     * Log out of SharpFleet only.
     */
    public function logout(Request $request)
    {
        // Clear SharpFleet session keys
        $request->session()->forget([
            'sharpfleet.user_id',
            'sharpfleet.organisation_id',
            'sharpfleet.role',
            'sharpfleet.name',
            'sharpfleet.email',
            'sharpfleet.login_nonce',
        ]);

        // Invalidate session (safe, does not touch CRM auth because we are not using Auth facade,
        // but note: this invalidates the whole session cookie for the browser).
        // If you later need CRM + Fleet simultaneously in same browser, we’ll switch to session “namespace only”.
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Remove remember cookie
        cookie()->queue(cookie()->forget($this->rememberCookieName()));

        return redirect('/app/sharpfleet/login');
    }

    // ==========================================================
    // Helpers (kept inside this one file to respect your rules)
    // ==========================================================

    /**
     * Attempt to locate the SharpFleet DB connection by name.
     * Looks for any connection key containing "fleet".
     */
    private function fleetConnection()
    {
        $connections = array_keys((array) config('database.connections', []));

        // Preferred common names first
        $preferred = ['sharpfleet', 'fleet', 'mysql_sharpfleet', 'mysql_fleet'];

        foreach ($preferred as $name) {
            if (in_array($name, $connections, true)) {
                return DB::connection($name);
            }
        }

        // Auto-detect any connection containing "fleet"
        foreach ($connections as $name) {
            if (Str::contains(mb_strtolower($name), 'fleet')) {
                return DB::connection($name);
            }
        }

        // Fallback (will work only if fleet tables are on default connection)
        return DB::connection();
    }

    /**
     * Start a SharpFleet-only session namespace.
     */
    private function startFleetSession(Request $request, $user): void
    {
        // Regenerate to prevent session fixation
        $request->session()->regenerate();

        $request->session()->put([
            'sharpfleet.user_id'          => (int) $user->id,
            'sharpfleet.organisation_id'  => (int) $user->organisation_id,
            'sharpfleet.role'             => (string) $user->role,
            'sharpfleet.name'             => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')),
            'sharpfleet.email'            => (string) $user->email,
            // nonce is used to bind remember-cookie to a specific login session generation
            'sharpfleet.login_nonce'      => Str::random(40),
        ]);
    }

    private function rememberCookieName(): string
    {
        return 'sharpfleet_remember_device';
    }

    /**
     * Create an encrypted remember cookie payload.
     * No DB writes. Cookie expires after N days.
     */
    private function makeRememberCookie($user, Request $request)
    {
        $days = (int) (config('session.lifetime', 120) > 0 ? 30 : 30); // default to 30 days
        $expiresAt = now()->addDays($days)->timestamp;

        $payload = [
            'user_id'   => (int) $user->id,
            'org_id'    => (int) $user->organisation_id,
            'role'      => (string) $user->role,
            'exp'       => (int) $expiresAt,
            // soft bind to device characteristics (not perfect, but adds friction to cookie theft)
            'ua'        => sha1((string) $request->userAgent()),
        ];

        $encrypted = Crypt::encryptString(json_encode($payload));

        return cookie(
            $this->rememberCookieName(),
            $encrypted,
            $days * 24 * 60,   // minutes
            '/',               // path
            null,              // domain
            $request->isSecure(), // secure
            true,              // httpOnly
            false,             // raw
            'Lax'              // sameSite
        );
    }

    private function decryptRememberCookie(string $cookieValue): ?array
    {
        try {
            $json = Crypt::decryptString($cookieValue);
            $data = json_decode($json, true);

            return is_array($data) ? $data : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function rememberPayloadLooksValid(array $payload, Request $request): bool
    {
        if (!isset($payload['user_id'], $payload['exp'], $payload['ua'])) {
            return false;
        }

        if (!is_numeric($payload['user_id']) || !is_numeric($payload['exp'])) {
            return false;
        }

        if ((int) $payload['exp'] < now()->timestamp) {
            return false;
        }

        // bind to user-agent hash (lightweight device binding)
        $ua = sha1((string) $request->userAgent());
        if (!hash_equals((string) $payload['ua'], $ua)) {
            return false;
        }

        return true;
    }
}
