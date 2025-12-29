<?php

namespace App\Services\SharpFleet;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EntitlementService
{
    /**
     * The SharpFleet session user (session('sharpfleet.user')).
     */
    protected ?array $user;

    public function __construct(?array $sharpFleetUser)
    {
        $this->user = $sharpFleetUser;
    }

    /**
     * Trial rules:
     * - ACTIVE: all SharpFleet actions allowed.
     * - EXPIRED: allow read-only access (reports) + login/logout; deny write actions.
     */

    public function isTrialExpired(): bool
    {
        $trialEndsAt = $this->getUserTrialEndDate();

        if (!$trialEndsAt) {
            return true;
        }

        return Carbon::now()->isAfter($trialEndsAt);
    }

    public function isTrialActive(): bool
    {
        return !$this->isTrialExpired();
    }

    public function canLogin(): bool
    {
        return true;
    }

    public function canLogout(): bool
    {
        return true;
    }

    public function canViewReports(): bool
    {
        return true;
    }

    public function canStartTrip(): bool
    {
        return $this->isTrialActive();
    }

    public function canEndTrip(): bool
    {
        return $this->isTrialActive();
    }

    public function canAddVehicle(): bool
    {
        return $this->isTrialActive();
    }

    public function canEditVehicle(): bool
    {
        return $this->isTrialActive();
    }

    public function canAddDriver(): bool
    {
        return $this->isTrialActive();
    }

    public function canCreateBooking(): bool
    {
        return $this->isTrialActive();
    }

    public function canManageSettings(): bool
    {
        return $this->isTrialActive();
    }

    /**
     * Central decision point used by middleware.
     *
     * IMPORTANT: We do not rely on route names (SharpFleet routes are mostly unnamed).
     * We also intentionally keep the "trial expired" behavior consistent with existing
     * middleware: when expired, only reports and logout are allowed.
     */
    public function canAccessRequest(Request $request): bool
    {
        if ($this->isTrialActive()) {
            return true;
        }

        $uri = $request->getRequestUri();

        if (str_contains($uri, '/logout')) {
            return $this->canLogout();
        }

        if (str_contains($uri, '/reports')) {
            return $this->canViewReports();
        }

        // Trial expired: everything else denied (write actions + non-report pages).
        return false;
    }

    private function getUserTrialEndDate(): ?Carbon
    {
        try {
            $userId = (int) ($this->user['id'] ?? 0);
            $organisationId = (int) ($this->user['organisation_id'] ?? 0);

            // Check user table for trial_ends_at
            if ($userId > 0) {
                $userRecord = DB::connection('sharpfleet')
                    ->table('users')
                    ->where('id', $userId)
                    ->first();

                if ($userRecord && $userRecord->trial_ends_at) {
                    return Carbon::parse($userRecord->trial_ends_at);
                }
            }

            // Fallback to organisation trial end date
            if ($organisationId > 0) {
                $orgRecord = DB::connection('sharpfleet')
                    ->table('organisations')
                    ->where('id', $organisationId)
                    ->first();

                if ($orgRecord && $orgRecord->trial_ends_at) {
                    return Carbon::parse($orgRecord->trial_ends_at);
                }
            }

            // If no trial data found, assume trial has expired (for existing users)
            return Carbon::now()->subDay();
        } catch (\Exception $e) {
            // If database query fails, allow access (fail open)
            return Carbon::now()->addDays(30);
        }
    }
}
