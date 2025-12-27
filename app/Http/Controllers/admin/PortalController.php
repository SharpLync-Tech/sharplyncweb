<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PortalController extends Controller
{
    public function index()
    {
        return view('admin.portal');
    }

    public function sharpfleet(Request $request)
    {
        $adminUser = (array) $request->session()->get('admin_user', []);

        $email = strtolower((string)($adminUser['userPrincipalName'] ?? ''));
        $name = (string)($adminUser['displayName'] ?? '');

        if ($email === '') {
            abort(403, 'Not logged in.');
        }

        $to = (string) $request->query('to', '/app/sharpfleet/admin');
        if (!str_starts_with($to, '/app/sharpfleet/admin')) {
            $to = '/app/sharpfleet/admin';
        }

        $timestamp = (string) time();
        $nonce = bin2hex(random_bytes(16));

        $secret = $this->getSsoSecret();
        $payload = $this->canonicalPayload($email, $name, $timestamp, $nonce, $to);
        $sig = hash_hmac('sha256', $payload, $secret);

        $query = http_build_query([
            'email' => $email,
            'name' => $name,
            'ts' => $timestamp,
            'nonce' => $nonce,
            'return' => $to,
            'sig' => $sig,
        ]);

        return redirect('/app/sharpfleet/sso?' . $query);
    }

    private function canonicalPayload(string $email, string $name, string $timestamp, string $nonce, string $returnTo): string
    {
        // Keep this stable and URL-independent.
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
