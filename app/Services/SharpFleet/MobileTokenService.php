<?php

namespace App\Services\SharpFleet;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MobileTokenService
{
    public function issueToken(int $organisationId, int $userId, string $deviceId, ?string $userAgent = null, ?string $ip = null): string
    {
        $rawToken = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $rawToken);

        $payload = [
            'organisation_id' => $organisationId,
            'user_id' => $userId,
            'device_id' => $deviceId,
            'token_hash' => $tokenHash,
        ];

        $now = now();
        if ($this->hasColumn('sharpfleet_mobile_tokens', 'created_at')) {
            $payload['created_at'] = $now;
        }
        if ($this->hasColumn('sharpfleet_mobile_tokens', 'updated_at')) {
            $payload['updated_at'] = $now;
        }
        if ($this->hasColumn('sharpfleet_mobile_tokens', 'last_used_at')) {
            $payload['last_used_at'] = $now;
        }
        if ($this->hasColumn('sharpfleet_mobile_tokens', 'user_agent_hash') && $userAgent !== null) {
            $payload['user_agent_hash'] = hash('sha256', $userAgent);
        }
        if ($this->hasColumn('sharpfleet_mobile_tokens', 'ip_last') && $ip !== null) {
            $payload['ip_last'] = $ip;
        }

        // Revoke any existing tokens for this user + device (single active token per device).
        $this->revokeTokensForDevice($organisationId, $userId, $deviceId, 'replaced');

        DB::connection('sharpfleet')
            ->table('sharpfleet_mobile_tokens')
            ->insert($payload);

        return $rawToken;
    }

    public function validateToken(string $token, string $deviceId): ?object
    {
        $tokenHash = hash('sha256', $token);

        $query = DB::connection('sharpfleet')
            ->table('sharpfleet_mobile_tokens as t')
            ->join('users as u', 't.user_id', '=', 'u.id')
            ->select(
                't.id as token_id',
                't.user_id',
                't.organisation_id',
                't.device_id',
                't.revoked_at',
                'u.id as user_id',
                'u.email',
                'u.first_name',
                'u.last_name',
                'u.role',
                'u.is_driver',
                'u.organisation_id as user_organisation_id'
            )
            ->where('t.token_hash', $tokenHash)
            ->whereNull('t.revoked_at')
            ->where('t.device_id', $deviceId);

        if ($this->hasColumn('users', 'archived_at')) {
            $query->addSelect('u.archived_at')->whereNull('u.archived_at');
        }
        if ($this->hasColumn('users', 'is_active')) {
            $query->addSelect('u.is_active')->where('u.is_active', 1);
        }

        $row = $query->first();
        if (!$row) {
            return null;
        }

        if ((int) ($row->organisation_id ?? 0) !== (int) ($row->user_organisation_id ?? 0)) {
            return null;
        }

        if ((int) ($row->is_driver ?? 0) !== 1) {
            return null;
        }

        return $row;
    }

    public function touchToken(int $tokenId, ?string $ip = null, ?string $userAgent = null): void
    {
        $updates = [];
        $now = now();

        if ($this->hasColumn('sharpfleet_mobile_tokens', 'last_used_at')) {
            $updates['last_used_at'] = $now;
        }
        if ($this->hasColumn('sharpfleet_mobile_tokens', 'updated_at')) {
            $updates['updated_at'] = $now;
        }
        if ($this->hasColumn('sharpfleet_mobile_tokens', 'ip_last') && $ip !== null) {
            $updates['ip_last'] = $ip;
        }
        if ($this->hasColumn('sharpfleet_mobile_tokens', 'user_agent_hash') && $userAgent !== null) {
            $updates['user_agent_hash'] = hash('sha256', $userAgent);
        }

        if (!empty($updates)) {
            DB::connection('sharpfleet')
                ->table('sharpfleet_mobile_tokens')
                ->where('id', $tokenId)
                ->update($updates);
        }
    }

    public function revokeTokensForUser(int $organisationId, int $userId, string $reason = 'revoked'): void
    {
        $updates = ['revoked_at' => now()];
        if ($this->hasColumn('sharpfleet_mobile_tokens', 'revoked_reason')) {
            $updates['revoked_reason'] = $reason;
        }
        if ($this->hasColumn('sharpfleet_mobile_tokens', 'updated_at')) {
            $updates['updated_at'] = now();
        }

        DB::connection('sharpfleet')
            ->table('sharpfleet_mobile_tokens')
            ->where('organisation_id', $organisationId)
            ->where('user_id', $userId)
            ->whereNull('revoked_at')
            ->update($updates);
    }

    public function revokeTokensForDevice(int $organisationId, int $userId, string $deviceId, string $reason = 'revoked'): void
    {
        $updates = ['revoked_at' => now()];
        if ($this->hasColumn('sharpfleet_mobile_tokens', 'revoked_reason')) {
            $updates['revoked_reason'] = $reason;
        }
        if ($this->hasColumn('sharpfleet_mobile_tokens', 'updated_at')) {
            $updates['updated_at'] = now();
        }

        DB::connection('sharpfleet')
            ->table('sharpfleet_mobile_tokens')
            ->where('organisation_id', $organisationId)
            ->where('user_id', $userId)
            ->where('device_id', $deviceId)
            ->whereNull('revoked_at')
            ->update($updates);
    }

    private function hasColumn(string $table, string $column): bool
    {
        try {
            return Schema::connection('sharpfleet')->hasColumn($table, $column);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
