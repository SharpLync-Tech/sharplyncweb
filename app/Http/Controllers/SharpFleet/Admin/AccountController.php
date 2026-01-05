<?php

namespace App\Http\Controllers\SharpFleet\Admin;

use App\Http\Controllers\Controller;
use App\Services\SharpFleet\BillingDisplayService;
use App\Services\SharpFleet\EntitlementService;
use App\Support\SharpFleet\Roles;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AccountController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || !Roles::isCompanyAdmin($user)) {
            abort(403, 'Admin access only');
        }

        $organisationId = (int) ($user['organisation_id'] ?? 0);

        $organisation = DB::connection('sharpfleet')
            ->table('organisations')
            ->where('id', $organisationId)
            ->first();

        $vehiclesCount = (int) DB::connection('sharpfleet')
            ->table('vehicles')
            ->where('organisation_id', $organisationId)
            ->where('is_active', 1)
            ->count();

        $entitlements = new EntitlementService($user);

        $trialEndsAt = $entitlements->getTrialEndsAt();
        $trialDaysRemaining = $entitlements->trialDaysRemaining();

        $isSubscribed = $entitlements->isSubscriptionActive();
        $hasCancelRequest = $entitlements->hasTrialCancelRequest();

        $billingSummary = [];
        try {
            $billingSummary = (new BillingDisplayService())
                ->getOrganisationBillingSummary($organisationId);
        } catch (\Throwable $e) {
            $billingSummary = [];
        }

        $pricing = $this->calculateMonthlyPrice($vehiclesCount);

        $billingActivity = collect();
        $billingActivityTableMissing = false;

        try {
            $billingActivityTableMissing = !Schema::connection('sharpfleet')->hasTable('sharpfleet_audit_logs');
            if (!$billingActivityTableMissing) {
                $billingActivity = DB::connection('sharpfleet')
                    ->table('sharpfleet_audit_logs')
                    ->where('organisation_id', $organisationId)
                    ->where('action', 'like', 'Billing:%')
                    ->orderByDesc('id')
                    ->limit(10)
                    ->get();
            }
        } catch (\Throwable $e) {
            $billingActivity = collect();
            $billingActivityTableMissing = true;
        }

        return view('sharpfleet.admin.account', [
            'organisation' => $organisation,
            'vehiclesCount' => $vehiclesCount,
            'isSubscribed' => $isSubscribed,
            'hasCancelRequest' => $hasCancelRequest,
            'billingSummary' => $billingSummary,
            'trialEndsAt' => $trialEndsAt,
            'trialDaysRemaining' => $trialDaysRemaining,
            'monthlyPrice' => $pricing['monthlyPrice'],
            'monthlyPriceBreakdown' => $pricing['breakdown'],
            'requiresContactForPricing' => $pricing['requiresContact'],
            'billingActivity' => $billingActivity,
            'billingActivityTableMissing' => $billingActivityTableMissing,
        ]);
    }

    public function subscribe(Request $request): RedirectResponse
    {
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || !Roles::isCompanyAdmin($user)) {
            abort(403, 'Admin access only');
        }

        $organisationId = (int) ($user['organisation_id'] ?? 0);

        $activeVehiclesCount = (int) DB::connection('sharpfleet')
            ->table('vehicles')
            ->where('organisation_id', $organisationId)
            ->where('is_active', 1)
            ->count();

        if ($activeVehiclesCount < 1) {
            return redirect('/app/sharpfleet/admin/account')
                ->with('error', 'You must have at least one active vehicle to subscribe.');
        }

        $stripeSecret = (string) env('STRIPE_SECRET_TEST');
        $stripePriceId = (string) env('STRIPE_PRICE_TEST');

        if ($stripeSecret === '' || $stripePriceId === '') {
            return redirect('/app/sharpfleet/admin/account')
                ->with('error', 'Stripe is not configured.');
        }

        $baseUrl = $request->getSchemeAndHttpHost();
        $successUrl = $baseUrl . '/app/sharpfleet/admin/account?checkout=success';
        $cancelUrl = $baseUrl . '/app/sharpfleet/admin/account?checkout=cancelled';

        try {
            \Stripe\Stripe::setApiKey($stripeSecret);

            $session = \Stripe\Checkout\Session::create([
                'mode' => 'subscription',
                'client_reference_id' => 'org_' . $organisationId,
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
                'line_items' => [
                    [
                        'price' => $stripePriceId,
                        'quantity' => $activeVehiclesCount,
                    ],
                ],
            ]);

            $checkoutUrl = $session?->url;
            if (!is_string($checkoutUrl) || $checkoutUrl === '') {
                return redirect('/app/sharpfleet/admin/account')
                    ->with('error', 'Unable to start Stripe Checkout.');
            }

            return redirect()->away($checkoutUrl);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            return redirect('/app/sharpfleet/admin/account')
                ->with('error', 'Unable to start Stripe Checkout.');
        } catch (\Throwable $e) {
            return redirect('/app/sharpfleet/admin/account')
                ->with('error', 'Unable to start Stripe Checkout.');
        }
    }

    public function cancelTrial(Request $request): RedirectResponse
    {
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || !Roles::isCompanyAdmin($user)) {
            abort(403, 'Admin access only');
        }

        $organisationId = (int) ($user['organisation_id'] ?? 0);

        $org = DB::connection('sharpfleet')
            ->table('organisations')
            ->where('id', $organisationId)
            ->first();

        if (!$org) {
            abort(404, 'Organisation not found');
        }

        $settings = [];
        if (!empty($org->settings)) {
            $decoded = json_decode((string) $org->settings, true);
            if (is_array($decoded)) {
                $settings = $decoded;
            }
        }

        $settings['trial_cancel_requested_at'] = Carbon::now()->toIso8601String();

        DB::connection('sharpfleet')
            ->table('organisations')
            ->where('id', $organisationId)
            ->update([
                'settings' => json_encode($settings),
                'updated_at' => now(),
            ]);

        return redirect('/app/sharpfleet/admin/trial-expired')
            ->with('warning', 'Trial cancelled. Your account is now read-only (reports only).');
    }

    public function cancelSubscription(Request $request): RedirectResponse
    {
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || !Roles::isCompanyAdmin($user)) {
            abort(403, 'Admin access only');
        }

        $organisationId = (int) ($user['organisation_id'] ?? 0);

        $org = DB::connection('sharpfleet')
            ->table('organisations')
            ->where('id', $organisationId)
            ->first();

        if (!$org) {
            abort(404, 'Organisation not found');
        }

        $settings = [];
        if (!empty($org->settings)) {
            $decoded = json_decode((string) $org->settings, true);
            if (is_array($decoded)) {
                $settings = $decoded;
            }
        }

        $settings['subscription_status'] = 'cancelled';
        $settings['subscription_cancel_requested_at'] = Carbon::now()->toIso8601String();

        DB::connection('sharpfleet')
            ->table('organisations')
            ->where('id', $organisationId)
            ->update([
                'settings' => json_encode($settings),
                'updated_at' => now(),
            ]);

        return redirect('/app/sharpfleet/admin/trial-expired')
            ->with('warning', 'Subscription cancelled. Your account is now read-only (reports only).');
    }

    private function calculateMonthlyPrice(int $vehiclesCount): array
    {
        $vehiclesCount = max(0, $vehiclesCount);

        $tier1Vehicles = min($vehiclesCount, 10);
        $tier2Vehicles = max(0, $vehiclesCount - 10);

        $tier1Price = 3.50;
        $tier2Price = 2.50;

        $monthlyPrice = ($tier1Vehicles * $tier1Price) + ($tier2Vehicles * $tier2Price);

        $requiresContact = $vehiclesCount > 20;

        $breakdown = sprintf(
            '%d × $%.2f + %d × $%.2f',
            $tier1Vehicles,
            $tier1Price,
            $tier2Vehicles,
            $tier2Price
        );

        return [
            'monthlyPrice' => $monthlyPrice,
            'breakdown' => $breakdown,
            'requiresContact' => $requiresContact,
        ];
    }
}
