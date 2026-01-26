<?php

namespace App\Http\Middleware;

use App\Services\SharpFleet\MobileTokenService;
use App\Services\SharpFleet\AuditLogService;
use App\Support\SharpFleet\Roles;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class MobileDriverTokenAuth
{
    private const ARCHIVED_LOGIN_MESSAGE = 'This account has been archived. Please contact your administrator.';

    public function handle(Request $request, Closure $next)
    {
        if (!$this->isMobileRoute($request)) {
            return $next($request);
        }

        $token = $this->extractToken($request);
        $deviceId = $this->extractDeviceId($request);

        if ($token !== '' && $deviceId !== '') {
            $service = new MobileTokenService();
            $record = $service->validateToken($token, $deviceId);

            if ($record) {
                $this->setSharpFleetUserContext($request, [
                    'id' => (int) ($record->user_id ?? 0),
                    'organisation_id' => (int) ($record->organisation_id ?? 0),
                    'email' => (string) ($record->email ?? ''),
                    'first_name' => (string) ($record->first_name ?? ''),
                    'last_name' => (string) ($record->last_name ?? ''),
                    'name' => trim((string) (($record->first_name ?? '') . ' ' . ($record->last_name ?? ''))),
                    'role' => Roles::normalize($record->role ?? null),
                    'is_driver' => 1,
                    'archived_at' => null,
                    'logged_in' => true,
                ]);

                $request->attributes->set('sharpfleet.mobile_token_valid', true);
                $request->attributes->set('sharpfleet.mobile_token_id', (int) ($record->token_id ?? 0));

                $service->touchToken(
                    (int) ($record->token_id ?? 0),
                    $request->ip(),
                    $request->header('User-Agent')
                );

                return $next($request);
            }
        }

        // Fall back to existing session auth for mobile routes (if present).
        $sessionUser = $request->session()->get('sharpfleet.user');
        if ($this->isValidSessionDriver($request, $sessionUser)) {
            $authHeader = (string) $request->header('Authorization', '');
            $authHeaderLower = strtolower($authHeader);
            $hasAuthHeader = $authHeader !== '';
            $hasBearer = $hasAuthHeader && str_starts_with($authHeaderLower, 'bearer ');
            $hasXDeviceToken = trim((string) $request->header('X-Device-Token', '')) !== '';
            $hasXDeviceId = trim((string) $request->header('X-Device-Id', '')) !== '';
            $inputDeviceId = trim((string) $request->input('device_id', ''));
            $deviceIdSource = $hasXDeviceId ? 'header' : ($inputDeviceId !== '' ? 'input' : 'none');
            $tokenLength = $token !== '' ? strlen($token) : 0;

            \Log::warning('[SharpFleet Mobile] Token missing/invalid, falling back to session auth', [
                'path' => '/' . ltrim($request->path(), '/'),
                'host' => $request->getHost(),
                'user_id' => (int) ($sessionUser['id'] ?? 0),
                'organisation_id' => (int) ($sessionUser['organisation_id'] ?? 0),
                'device_id' => $this->extractDeviceId($request) ?: null,
                'device_id_source' => $deviceIdSource,
                'has_auth_header' => $hasAuthHeader,
                'auth_header_prefix' => $hasAuthHeader ? strtok($authHeaderLower, ' ') : null,
                'has_bearer' => $hasBearer,
                'has_x_device_token' => $hasXDeviceToken,
                'has_x_device_id' => $hasXDeviceId,
                'token_length' => $tokenLength,
                'user_agent' => (string) $request->header('User-Agent', ''),
            ]);

            try {
                (new AuditLogService())->logSubscriber($request, 'mobile_token_fallback', [
                    'device_id' => $this->extractDeviceId($request) ?: null,
                    'device_id_source' => $deviceIdSource,
                    'has_auth_header' => $hasAuthHeader,
                    'auth_header_prefix' => $hasAuthHeader ? strtok($authHeaderLower, ' ') : null,
                    'has_bearer' => $hasBearer,
                    'has_x_device_token' => $hasXDeviceToken,
                    'has_x_device_id' => $hasXDeviceId,
                    'token_length' => $tokenLength,
                ]);
            } catch (\Throwable $e) {
                // Best-effort audit logging only.
            }
            $this->setSharpFleetUserContext($request, $sessionUser);
            return $next($request);
        }

        if ($this->isNonBlockingTripRequest($request)) {
            return response()->json([
                'status' => 'accepted',
                'message' => 'Trip action accepted for offline sync.',
            ], 202);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        return redirect('/app/sharpfleet/login');
    }

    private function extractToken(Request $request): string
    {
        $auth = (string) $request->header('Authorization', '');
        if ($auth !== '' && str_starts_with(strtolower($auth), 'bearer ')) {
            return trim(substr($auth, 7));
        }

        $headerToken = trim((string) $request->header('X-Device-Token', ''));
        if ($headerToken !== '') {
            return $headerToken;
        }

        return trim((string) $request->cookie('sf_device_token', ''));
    }

    private function extractDeviceId(Request $request): string
    {
        $deviceId = trim((string) $request->header('X-Device-Id', ''));
        if ($deviceId !== '') {
            return $deviceId;
        }

        $inputDeviceId = trim((string) $request->input('device_id', ''));
        if ($inputDeviceId !== '') {
            return $inputDeviceId;
        }

        return trim((string) $request->cookie('sf_device_id', ''));
    }

    private function setSharpFleetUserContext(Request $request, array $user): void
    {
        $request->attributes->set('sharpfleet.user', $user);
        $request->session()->put('sharpfleet.user', $user);
    }

    private function isValidSessionDriver(Request $request, ?array $user): bool
    {
        if (!$user || empty($user['logged_in'])) {
            return false;
        }

        if (!empty($user['archived_at'])) {
            $requestUserId = (int) ($user['id'] ?? 0);
            if ($requestUserId > 0) {
                $request->session()->forget('sharpfleet.user');
                Cookie::queue(Cookie::forget('sharpfleet_remember'));
            }
            return false;
        }

        $role = Roles::normalize($user['role'] ?? null);
        if (!in_array($role, [Roles::DRIVER, Roles::COMPANY_ADMIN, Roles::BRANCH_ADMIN, Roles::BOOKING_ADMIN], true)) {
            return false;
        }

        return !empty($user['is_driver']);
    }

    private function isMobileRoute(Request $request): bool
    {
        $path = '/' . ltrim($request->path(), '/');

        return str_starts_with($path, '/app/sharpfleet/mobile/')
            || $path === '/app/sharpfleet/mobile'
            || str_starts_with($path, '/app/sharpfleet/trips/')
            || str_starts_with($path, '/app/sharpfleet/faults/')
            || str_starts_with($path, '/app/sharpfleet/bookings/')
            || $path === '/app/sharpfleet/mobile/support'
            || $path === '/app/sharpfleet/mobile/fuel';
    }

    private function isNonBlockingTripRequest(Request $request): bool
    {
        $path = '/' . ltrim($request->path(), '/');

        return $request->isMethod('post') && in_array($path, [
            '/app/sharpfleet/trips/start',
            '/app/sharpfleet/trips/end',
            '/app/sharpfleet/trips/offline-sync',
        ], true);
    }
}
