<?php

namespace App\Services\SharpFleet;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AuditLogService
{
    private const CONNECTION = 'sharpfleet';
    private const TABLE = 'sharpfleet_audit_logs';

    /**
     * Log a subscriber (tenant-side) action.
     */
    public function logSubscriber(Request $request, string $action, array $context = []): void
    {
        $user = (array) $request->session()->get('sharpfleet.user', []);

        $this->write([
            'organisation_id' => (int) ($user['organisation_id'] ?? 0),
            'actor_type' => 'subscriber',
            'actor_id' => (int) ($user['id'] ?? 0) ?: null,
            'actor_email' => (string) ($user['email'] ?? ''),
            'actor_name' => (string) ($user['name'] ?? ''),
            'action' => $action,
            'ip' => (string) $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'method' => (string) $request->method(),
            'path' => '/' . ltrim((string) $request->path(), '/'),
            'status_code' => null,
            'context_json' => $this->encodeContext($context),
            'created_at' => now(),
        ]);
    }

    /**
     * Log a platform-admin action taken against a subscriber (organisation and/or user).
     */
    public function logPlatformAdmin(Request $request, string $action, ?int $organisationId = null, ?int $targetUserId = null, array $context = []): void
    {
        $adminUser = (array) $request->session()->get('admin_user', []);

        if (!is_null($organisationId)) {
            $context = array_merge(['organisation_id' => $organisationId], $context);
        }
        if (!is_null($targetUserId)) {
            $context = array_merge(['target_user_id' => $targetUserId], $context);
        }

        $this->write([
            'organisation_id' => (int) ($organisationId ?? 0),
            'actor_type' => 'platform_admin',
            'actor_id' => null,
            'actor_email' => (string) ($adminUser['userPrincipalName'] ?? ''),
            'actor_name' => (string) ($adminUser['displayName'] ?? ''),
            'action' => $action,
            'ip' => (string) $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'method' => (string) $request->method(),
            'path' => '/' . ltrim((string) $request->path(), '/'),
            'status_code' => null,
            'context_json' => $this->encodeContext($context),
            'created_at' => now(),
        ]);
    }

    /**
     * Log a request-level event (typically via middleware).
     */
    public function logSubscriberRequest(Request $request, string $action, int $statusCode, float $durationMs, array $context = []): void
    {
        $user = (array) $request->session()->get('sharpfleet.user', []);

        $context = array_merge([
            'route_name' => optional($request->route())->getName(),
            'route_uri' => optional($request->route())->uri(),
            'route_params' => optional($request->route())->parameters() ?? [],
            'duration_ms' => $durationMs,
        ], $context);

        $this->write([
            'organisation_id' => (int) ($user['organisation_id'] ?? 0),
            'actor_type' => 'subscriber',
            'actor_id' => (int) ($user['id'] ?? 0) ?: null,
            'actor_email' => (string) ($user['email'] ?? ''),
            'actor_name' => (string) ($user['name'] ?? ''),
            'action' => $action,
            'ip' => (string) $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'method' => (string) $request->method(),
            'path' => '/' . ltrim((string) $request->path(), '/'),
            'status_code' => (int) $statusCode,
            'context_json' => $this->encodeContext($context),
            'created_at' => now(),
        ]);
    }

    private function write(array $payload): void
    {
        $payload['organisation_id'] = (int) ($payload['organisation_id'] ?? 0);

        if (($payload['organisation_id'] ?? 0) <= 0) {
            // Don't write logs we can't attribute to an organisation.
            Log::info('[SHARPFLEET_AUDIT_SKIP] Missing organisation_id', $payload);
            return;
        }

        try {
            if (!Schema::connection(self::CONNECTION)->hasTable(self::TABLE)) {
                Log::info('[SHARPFLEET_AUDIT] ' . ($payload['action'] ?? 'unknown'), $payload);
                return;
            }

            DB::connection(self::CONNECTION)->table(self::TABLE)->insert($payload);
        } catch (\Throwable $e) {
            Log::warning('[SHARPFLEET_AUDIT_FALLBACK] ' . ($payload['action'] ?? 'unknown') . ' — ' . $e->getMessage(), $payload);
        }
    }

    private function encodeContext(array $context): ?string
    {
        // Avoid accidentally logging huge payloads.
        $context = Arr::except($context, [
            'password',
            'password_confirmation',
            'token',
            'remember_token',
            'authorization',
            'Authorization',
        ]);

        try {
            return json_encode($context);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
