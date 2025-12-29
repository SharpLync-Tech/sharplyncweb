<?php

namespace App\Services\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AuditLogService
{
    /**
     * Best-effort audit log: writes to DB table `admin_audit_logs` when available,
     * otherwise falls back to file logging.
     */
    public function log(Request $request, string $action, array $context = []): void
    {
        $adminUser = (array) $request->session()->get('admin_user', []);

        $payload = [
            'action' => $action,
            'actor_upn' => (string) ($adminUser['userPrincipalName'] ?? ''),
            'actor_name' => (string) ($adminUser['displayName'] ?? ''),
            'ip' => (string) $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'method' => (string) $request->method(),
            'path' => (string) $request->path(),
            'context_json' => json_encode($context),
            'created_at' => now(),
        ];

        try {
            if (!Schema::hasTable('admin_audit_logs')) {
                Log::info('[ADMIN_AUDIT] ' . $action, $payload);
                return;
            }

            DB::table('admin_audit_logs')->insert($payload);
        } catch (\Throwable $e) {
            Log::warning('[ADMIN_AUDIT_FALLBACK] ' . $action . ' — ' . $e->getMessage(), $payload);
        }
    }

    /**
     * Convenience wrapper for platform-admin actions that optionally target a SharpFleet subscriber.
     *
     * This keeps the existing best-effort logging behavior (DB table when present; file fallback),
     * while standardizing how we attach organisation context.
     */
    public function logPlatformAdmin(Request $request, string $action, ?int $organisationId = null, array $context = []): void
    {
        if (!is_null($organisationId)) {
            $context = array_merge(['organisation_id' => $organisationId], $context);
        }

        $this->log($request, $action, $context);
    }
}
