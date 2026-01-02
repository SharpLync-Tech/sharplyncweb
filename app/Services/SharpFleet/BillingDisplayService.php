<?php

namespace App\Services\SharpFleet;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BillingDisplayService
{
    /**
     * Returns a normalized billing view model for a SharpFleet organisation.
     *
     * Effective mode precedence:
     * 1) Active access override (manual invoice / complimentary)
     * 2) Active Stripe subscription (settings.subscription_status === 'active')
     * 3) Trial (trial_ends_at may be null)
     */
    public function getOrganisationBillingSummary(int $organisationId): array
    {
        $org = DB::connection('sharpfleet')
            ->table('organisations')
            ->where('id', $organisationId)
            ->first();

        $settings = [];
        if (!empty($org?->settings)) {
            $decoded = json_decode((string) $org->settings, true);
            if (is_array($decoded)) {
                $settings = $decoded;
            }
        }

        $timezone = (string) ($org->timezone ?? 'Australia/Brisbane');

        $stripeStatus = (string) ($settings['subscription_status'] ?? '');
        $stripeCustomerId = (string) ($settings['stripe_customer_id'] ?? '');
        $stripeSubscriptionId = (string) ($settings['stripe_subscription_id'] ?? '');
        $stripePriceId = (string) ($settings['stripe_price_id'] ?? '');

        $trialEndsUtc = null;
        $trialEndsLocal = null;
        if (!empty($org?->trial_ends_at)) {
            $trialEndsUtc = Carbon::parse((string) $org->trial_ends_at, 'UTC');
            $trialEndsLocal = $trialEndsUtc->copy()->setTimezone($timezone);
        }

        $override = $settings['billing_override'] ?? null;
        $overrideMode = null;
        $overrideUntilUtc = null;
        $overrideUntilLocal = null;
        $overrideActive = false;

        if (is_array($override)) {
            $m = (string) ($override['mode'] ?? '');
            if (in_array($m, ['manual_invoice', 'comped'], true)) {
                $overrideMode = $m;
                $untilUtcRaw = (string) ($override['access_until_utc'] ?? '');
                if ($untilUtcRaw !== '') {
                    $overrideUntilUtc = Carbon::parse($untilUtcRaw, 'UTC');
                    $overrideUntilLocal = $overrideUntilUtc->copy()->setTimezone($timezone);
                    $overrideActive = Carbon::now('UTC')->lessThanOrEqualTo($overrideUntilUtc);
                }
            }
        }

        $effectiveMode = 'trial';
        if ($overrideActive) {
            $effectiveMode = $overrideMode === 'manual_invoice' ? 'manual_invoice' : 'complimentary';
        } elseif ($stripeStatus === 'active') {
            $effectiveMode = 'stripe';
        }

        return [
            'organisation_timezone' => $timezone,

            // Stripe (raw)
            'stripe_subscription_status' => $stripeStatus,
            'stripe_customer_id' => $stripeCustomerId,
            'stripe_subscription_id' => $stripeSubscriptionId,
            'stripe_price_id' => $stripePriceId,

            // Trial (raw)
            'trial_ends_utc' => $trialEndsUtc,
            'trial_ends_local' => $trialEndsLocal,

            // Override (raw)
            'access_override_active' => $overrideActive,
            'access_override_mode' => $overrideMode,
            'access_override_until_utc' => $overrideUntilUtc,
            'access_override_until_local' => $overrideUntilLocal,

            // Effective
            'effective_mode' => $effectiveMode,
        ];
    }
}
