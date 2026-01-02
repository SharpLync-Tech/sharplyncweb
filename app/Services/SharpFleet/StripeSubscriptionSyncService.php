<?php

namespace App\Services\SharpFleet;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StripeSubscriptionSyncService
{
    /**
     * Ensures the Stripe subscription quantity matches the organisation's active vehicle count.
     *
     * This updates the existing subscription item quantity with proration disabled so the
     * change is reflected on the next invoice (not immediately prorated).
     */
    public function syncVehicleQuantityToStripe(int $organisationId, int $activeVehiclesCount): array
    {
        $organisationId = (int) $organisationId;
        $activeVehiclesCount = max(0, (int) $activeVehiclesCount);

        $result = [
            'organisation_id' => $organisationId,
            'attempted' => false,
            'updated' => false,
            'stripe_subscription_id' => null,
            'stripe_price_id' => null,
            'from_quantity' => null,
            'to_quantity' => $activeVehiclesCount,
        ];

        if ($organisationId < 1) {
            return $result;
        }

        $stripeSecret = (string) env('STRIPE_SECRET_TEST');
        if ($stripeSecret === '') {
            throw new \RuntimeException('Stripe is not configured.');
        }

        $org = DB::connection('sharpfleet')
            ->table('organisations')
            ->select('id', 'settings')
            ->where('id', $organisationId)
            ->first();

        if (!$org) {
            return $result;
        }

        $settings = [];
        if (!empty($org->settings)) {
            $decoded = json_decode((string) $org->settings, true);
            if (is_array($decoded)) {
                $settings = $decoded;
            }
        }

        if (($settings['subscription_status'] ?? null) !== 'active') {
            return $result;
        }

        $stripeSubscriptionId = $settings['stripe_subscription_id'] ?? null;
        if (!is_string($stripeSubscriptionId) || $stripeSubscriptionId === '') {
            return $result;
        }

        $result['stripe_subscription_id'] = $stripeSubscriptionId;

        $expectedPriceId = $settings['stripe_price_id'] ?? null;
        if (!is_string($expectedPriceId) || $expectedPriceId === '') {
            $expectedPriceId = (string) env('STRIPE_PRICE_TEST');
        }

        if (!is_string($expectedPriceId) || $expectedPriceId === '') {
            throw new \RuntimeException('Stripe price is not configured.');
        }

        $result['stripe_price_id'] = $expectedPriceId;

        $result['attempted'] = true;

        \Stripe\Stripe::setApiKey($stripeSecret);

        $subscription = \Stripe\Subscription::retrieve($stripeSubscriptionId, [
            'expand' => ['items.data.price'],
        ]);

        $items = $subscription?->items?->data;
        if (!is_array($items) || count($items) < 1) {
            throw new \RuntimeException('Stripe subscription has no items.');
        }

        $targetItemId = null;
        $currentQuantity = null;

        foreach ($items as $item) {
            $price = is_object($item) ? ($item->price ?? null) : null;
            $priceId = is_object($price) ? ($price->id ?? null) : null;

            if (is_string($priceId) && $priceId === $expectedPriceId) {
                $targetItemId = is_object($item) ? ($item->id ?? null) : null;
                $currentQuantity = is_object($item) ? ($item->quantity ?? null) : null;
                break;
            }
        }

        if (!is_string($targetItemId) || $targetItemId === '') {
            throw new \RuntimeException('Stripe subscription item not found for expected price.');
        }

        $currentQuantityInt = is_numeric($currentQuantity) ? (int) $currentQuantity : null;
        if ($currentQuantityInt !== null && $currentQuantityInt === $activeVehiclesCount) {
            $result['from_quantity'] = $currentQuantityInt;
            $result['updated'] = false;
            return $result;
        }

        \Stripe\Subscription::update($stripeSubscriptionId, [
            'proration_behavior' => 'none',
            'items' => [
                [
                    'id' => $targetItemId,
                    'quantity' => $activeVehiclesCount,
                ],
            ],
        ]);

        $result['from_quantity'] = $currentQuantityInt;
        $result['updated'] = true;

        Log::info('SharpFleet: synced Stripe subscription quantity to vehicles', [
            'organisation_id' => $organisationId,
            'stripe_subscription_id' => $stripeSubscriptionId,
            'stripe_price_id' => $expectedPriceId,
            'from_quantity' => $currentQuantityInt,
            'to_quantity' => $activeVehiclesCount,
        ]);

        return $result;
    }
}
