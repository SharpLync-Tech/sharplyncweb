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

    /**
     * Undo a scheduled cancellation (cancel_at_period_end=false).
     *
     * @return array{subscription_id:string,cancel_at_period_end:bool,current_period_end_utc:?string,status:?string}
     */
    public function uncancelSubscription(string $stripeSubscriptionId): array
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

        $sub = \Stripe\Subscription::update($stripeSubscriptionId, [
            'cancel_at_period_end' => false,
            // Some scheduled cancellations set a cancel_at timestamp; clear it explicitly.
            'cancel_at' => null,
        ]);

        $currentPeriodEnd = is_numeric($sub->current_period_end ?? null) ? (int) $sub->current_period_end : null;
        $currentPeriodEndUtc = $currentPeriodEnd ? Carbon::createFromTimestamp($currentPeriodEnd, 'UTC')->toDateTimeString() : null;

        return [
            'subscription_id' => $stripeSubscriptionId,
            'cancel_at_period_end' => (bool) ($sub->cancel_at_period_end ?? false),
            'current_period_end_utc' => $currentPeriodEndUtc,
            'status' => is_string($sub->status ?? null) ? (string) $sub->status : null,
        ];
    }

    /**
     * Creates a Stripe Checkout URL for a new subscription.
     */
    public function createCheckoutUrl(int $organisationId, int $quantity, string $baseUrl): string
    {
        $organisationId = (int) $organisationId;
        $quantity = max(1, (int) $quantity);
        $baseUrl = rtrim(trim($baseUrl), '/');

        if ($organisationId < 1) {
            throw new \InvalidArgumentException('Organisation id is required.');
        }

        $stripeSecret = (string) env('STRIPE_SECRET_TEST');
        $stripePriceId = (string) env('STRIPE_PRICE_TEST');

        if ($stripeSecret === '' || $stripePriceId === '') {
            throw new \RuntimeException('Stripe is not configured.');
        }

        \Stripe\Stripe::setApiKey($stripeSecret);

        $successUrl = $baseUrl . '/app/sharpfleet/admin/account?checkout=success';
        $cancelUrl = $baseUrl . '/app/sharpfleet/admin/account?checkout=cancelled';

        $session = \Stripe\Checkout\Session::create([
            'mode' => 'subscription',
            'client_reference_id' => 'org_' . $organisationId,
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'line_items' => [
                [
                    'price' => $stripePriceId,
                    'quantity' => $quantity,
                ],
            ],
        ]);

        $checkoutUrl = $session?->url;
        if (!is_string($checkoutUrl) || $checkoutUrl === '') {
            throw new \RuntimeException('Unable to create Stripe Checkout session.');
        }

        return $checkoutUrl;
    }
}
