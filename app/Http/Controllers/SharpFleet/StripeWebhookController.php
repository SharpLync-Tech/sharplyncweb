<?php

namespace App\Http\Controllers\SharpFleet;

use App\Http\Controllers\Controller;
use App\Mail\SharpFleet\SubscriptionWelcome;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class StripeWebhookController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $signatureHeader = (string) $request->header('Stripe-Signature');
        $endpointSecret = (string) env('STRIPE_WEBHOOK_SECRET_TEST');

        if ($endpointSecret === '') {
            return response()->json(['error' => 'Stripe is not configured.'], 500);
        }

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $signatureHeader,
                $endpointSecret
            );
        } catch (\UnexpectedValueException $e) {
            return response()->json(['error' => 'Invalid payload.'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return response()->json(['error' => 'Invalid signature.'], 400);
        }

        if (($event->type ?? null) !== 'checkout.session.completed') {
            return response()->json(['received' => true]);
        }

        $session = $event->data->object ?? null;
        $clientReferenceId = is_object($session) ? ($session->client_reference_id ?? null) : null;

        if (!is_string($clientReferenceId) || !preg_match('/^org_\d+$/', $clientReferenceId)) {
            return response()->json(['received' => true]);
        }

        $organisationId = (int) substr($clientReferenceId, 4);
        if ($organisationId < 1) {
            return response()->json(['received' => true]);
        }

        $stripeCustomerId = is_object($session) ? ($session->customer ?? null) : null;
        $stripeSubscriptionId = is_object($session) ? ($session->subscription ?? null) : null;

        $stripePriceId = (string) env('STRIPE_PRICE_TEST');

        try {
            DB::connection('sharpfleet')->transaction(function () use (
                $organisationId,
                $stripeCustomerId,
                $stripeSubscriptionId,
                $stripePriceId
            ): void {
                $organisation = DB::connection('sharpfleet')
                    ->table('organisations')
                    ->where('id', $organisationId)
                    ->first();

                if (!$organisation) {
                    Log::warning('Stripe webhook: organisation not found', [
                        'organisation_id' => $organisationId,
                    ]);
                    return;
                }

                $settings = [];
                if (!empty($organisation->settings)) {
                    $decoded = json_decode((string) $organisation->settings, true);
                    if (is_array($decoded)) {
                        $settings = $decoded;
                    }
                }

                $settings['subscription_status'] = 'active';
                $settings['subscription_started_at'] = Carbon::now()->toIso8601String();

                if (is_string($stripeCustomerId) && $stripeCustomerId !== '') {
                    $settings['stripe_customer_id'] = $stripeCustomerId;
                }

                if (is_string($stripeSubscriptionId) && $stripeSubscriptionId !== '') {
                    $settings['stripe_subscription_id'] = $stripeSubscriptionId;
                }

                $settings['stripe_price_id'] = $stripePriceId;

                unset($settings['trial_cancel_requested_at']);
                unset($settings['subscription_cancel_requested_at']);

                DB::connection('sharpfleet')
                    ->table('organisations')
                    ->where('id', $organisationId)
                    ->update([
                        'settings' => json_encode($settings),
                        'updated_at' => now(),
                    ]);

                $contact = $this->resolveSubscriptionContact($organisationId, $organisation);
                if ($contact['email']) {
                    try {
                        Mail::to($contact['email'])->send(new SubscriptionWelcome(
                            firstName: $contact['first_name'] ?: 'there'
                        ));
                    } catch (\Throwable $e) {
                        Log::warning('Stripe webhook: failed sending subscription welcome email', [
                            'organisation_id' => $organisationId,
                            'recipient' => $contact['email'],
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            });
        } catch (\Throwable $e) {
            Log::error('Stripe webhook: failed processing checkout.session.completed', [
                'organisation_id' => $organisationId,
                'exception' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Unable to process webhook.'], 500);
        }

        return response()->json(['received' => true]);
    }

    private function resolveSubscriptionContact(int $organisationId, object $organisation): array
    {
        $contact = [
            'email' => null,
            'first_name' => '',
        ];

        $settings = [];
        if (!empty($organisation->settings)) {
            $decoded = json_decode((string) $organisation->settings, true);
            if (is_array($decoded)) {
                $settings = $decoded;
            }
        }

        if (!empty($organisation->billing_email) && filter_var((string) $organisation->billing_email, FILTER_VALIDATE_EMAIL)) {
            $contact['email'] = (string) $organisation->billing_email;
        } elseif (Schema::connection('sharpfleet')->hasTable('users')) {
            $admin = DB::connection('sharpfleet')
                ->table('users')
                ->where('organisation_id', $organisationId)
                ->orderByRaw("CASE WHEN role = 'admin' THEN 0 WHEN role = 'company_admin' THEN 1 ELSE 2 END")
                ->orderBy('id')
                ->first(['email', 'first_name']);

            $email = is_string($admin->email ?? null) ? trim((string) $admin->email) : '';
            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $contact['email'] = $email;
                $contact['first_name'] = (string) ($admin->first_name ?? '');
            }
        }

        if (empty($contact['first_name']) && !empty($organisation->name)) {
            $contact['first_name'] = (string) $organisation->name;
        }

        return $contact;
    }
}
