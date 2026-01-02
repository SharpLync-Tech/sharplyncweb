<?php

namespace App\Services\SharpFleet;

use Carbon\Carbon;

class StripeSubscriptionAdminService
{
    /**
     * Cancels a Stripe subscription.
     *
     * We default to cancel-at-period-end to avoid cutting off access mid-cycle.
     *
     * @return array{subscription_id:string,cancel_at_period_end:bool,current_period_end_utc:?string}
     */
    public function cancelSubscription(string $stripeSubscriptionId, bool $cancelAtPeriodEnd = true): array
    {
        $stripeSubscriptionId = trim($stripeSubscriptionId);

        if ($stripeSubscriptionId === '') {
            throw new \InvalidArgumentException('Stripe subscription id is required.');
        }

        $stripeSecret = (string) env('STRIPE_SECRET_TEST');
        if ($stripeSecret === '') {
            throw new \RuntimeException('Stripe is not configured.');
        }

        \Stripe\Stripe::setApiKey($stripeSecret);

        if ($cancelAtPeriodEnd) {
            $sub = \Stripe\Subscription::update($stripeSubscriptionId, [
                'cancel_at_period_end' => true,
            ]);
        } else {
            $sub = \Stripe\Subscription::cancel($stripeSubscriptionId, [
                'prorate' => false,
                'invoice_now' => false,
            ]);
        }

        $currentPeriodEnd = is_numeric($sub->current_period_end ?? null) ? (int) $sub->current_period_end : null;
        $currentPeriodEndUtc = $currentPeriodEnd ? Carbon::createFromTimestamp($currentPeriodEnd, 'UTC')->toDateTimeString() : null;

        return [
            'subscription_id' => $stripeSubscriptionId,
            'cancel_at_period_end' => $cancelAtPeriodEnd,
            'current_period_end_utc' => $currentPeriodEndUtc,
        ];
    }
}
