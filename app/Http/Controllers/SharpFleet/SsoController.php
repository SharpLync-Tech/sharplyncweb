<?php

namespace App\Http\Controllers\SharpFleet;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SsoController extends Controller
{
    public function login(Request $request)
    {
        $email = strtolower((string) $request->query('email', ''));
        $name = (string) $request->query('name', '');
        $timestamp = (string) $request->query('ts', '');
        $nonce = (string) $request->query('nonce', '');
        $returnTo = (string) $request->query('return', '/app/sharpfleet/admin');
        $sig = (string) $request->query('sig', '');

        if ($email === '' || $timestamp === '' || $nonce === '' || $sig === '') {
            abort(400, 'Missing SSO parameters.');
        }

        if (!ctype_digit($timestamp)) {
            abort(400, 'Invalid timestamp.');
        }

        // Prevent open redirect / privilege escalation into non-admin pages.
        if (!str_starts_with($returnTo, '/app/sharpfleet/admin')) {
            $returnTo = '/app/sharpfleet/admin';
        }

        $now = time();
        $ts = (int) $timestamp;
        if ($ts < ($now - 120) || $ts > ($now + 30)) {
            abort(419, 'SSO link expired.');
        }

        if ($this->isReplay($request, $nonce)) {
            abort(419, 'SSO link already used.');
        }

        $secret = $this->getSsoSecret();
        $payload = $this->canonicalPayload($email, $name, $timestamp, $nonce, $returnTo);
        $expected = hash_hmac('sha256', $payload, $secret);

        if (!hash_equals($expected, $sig)) {
            abort(403, 'Invalid SSO signature.');
        }

        $user = DB::connection('sharpfleet')
            ->table('users')
            ->where('email', $email)
            ->first();

        if (!$user) {
            return response(
                'SSO failed: no SharpFleet user found for ' . htmlspecialchars($email) . '. Create a SharpFleet admin user with this email to enable SSO.',
                403
            );
        }

        if (($user->role ?? null) !== 'admin') {
            abort(403, 'SSO failed: SharpFleet admin access only.');
        }

        // Start SharpFleet session (mirrors AuthController::startSession)
        $request->session()->regenerate();
        $request->session()->put('sharpfleet.user', [
            'id'              => $user->id,
            'organisation_id' => $user->organisation_id,
            'email'           => $user->email,
            'first_name'      => $user->first_name ?? '',
            'last_name'       => $user->last_name ?? '',
            'name'            => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')),
            'role'            => $user->role,
            'is_driver'       => (int) ($user->is_driver ?? 0),
            'logged_in'       => true,
        ]);

        return redirect($returnTo);
    }

    private function isReplay(Request $request, string $nonce): bool
    {
        $bucket = (array) $request->session()->get('sharpfleet.sso_nonces', []);

        // prune old entries
        $now = time();
        foreach ($bucket as $n => $ts) {
            if (!is_int($ts) || $ts < ($now - 300)) {
                unset($bucket[$n]);
            }
        }

        if (isset($bucket[$nonce])) {
            return true;
        }

        $bucket[$nonce] = $now;
        $request->session()->put('sharpfleet.sso_nonces', $bucket);

        return false;
    }

    private function canonicalPayload(string $email, string $name, string $timestamp, string $nonce, string $returnTo): string
    {
        return implode("\n", [
            'v1',
            $email,
            $name,
            $timestamp,
            $nonce,
            $returnTo,
        ]);
    }

    private function getSsoSecret(): string
    {
        $secret = (string) env('SHARP_SSO_SECRET', '');

        if ($secret === '') {
            $appKey = (string) config('app.key', '');
            if (str_starts_with($appKey, 'base64:')) {
                $decoded = base64_decode(substr($appKey, 7), true);
                if ($decoded !== false) {
                    return $decoded;
                }
            }
            return $appKey;
        }

        return $secret;
    }
}
