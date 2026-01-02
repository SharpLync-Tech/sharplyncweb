<?php

namespace App\Services\SharpFleet;

class StripeInvoiceService
{
    /**
     * Returns a simplified list of Stripe invoices for display in the platform admin UI.
     */
    public function listInvoicesForCustomer(string $stripeCustomerId, int $limit = 10): array
    {
        $stripeCustomerId = trim($stripeCustomerId);
        $limit = max(1, min(25, (int) $limit));

        if ($stripeCustomerId === '') {
            return [];
        }

        $stripeSecret = (string) env('STRIPE_SECRET_TEST');
        if ($stripeSecret === '') {
            throw new \RuntimeException('Stripe is not configured.');
        }

        \Stripe\Stripe::setApiKey($stripeSecret);

        $resp = \Stripe\Invoice::all([
            'customer' => $stripeCustomerId,
            'limit' => $limit,
        ]);

        $items = $resp?->data;
        if (!is_array($items)) {
            return [];
        }

        $out = [];
        foreach ($items as $inv) {
            $out[] = [
                'id' => (string) ($inv->id ?? ''),
                'number' => (string) ($inv->number ?? ''),
                'status' => (string) ($inv->status ?? ''),
                'currency' => (string) ($inv->currency ?? ''),
                'total' => is_numeric($inv->total ?? null) ? (int) $inv->total : null,
                'amount_due' => is_numeric($inv->amount_due ?? null) ? (int) $inv->amount_due : null,
                'amount_paid' => is_numeric($inv->amount_paid ?? null) ? (int) $inv->amount_paid : null,
                'created' => is_numeric($inv->created ?? null) ? (int) $inv->created : null,
                'period_start' => is_numeric($inv->period_start ?? null) ? (int) $inv->period_start : null,
                'period_end' => is_numeric($inv->period_end ?? null) ? (int) $inv->period_end : null,
                'hosted_invoice_url' => (string) ($inv->hosted_invoice_url ?? ''),
                'invoice_pdf' => (string) ($inv->invoice_pdf ?? ''),
            ];
        }

        return $out;
    }
}
