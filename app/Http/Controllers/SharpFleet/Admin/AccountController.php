<?php

namespace App\Http\Controllers\SharpFleet\Admin;

use App\Http\Controllers\Controller;
use App\Services\SharpFleet\EntitlementService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || ($user['role'] ?? null) !== 'admin') {
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

        $pricing = $this->calculateMonthlyPrice($vehiclesCount);

        return view('sharpfleet.admin.account', [
            'organisation' => $organisation,
            'vehiclesCount' => $vehiclesCount,
            'isSubscribed' => $isSubscribed,
            'hasCancelRequest' => $hasCancelRequest,
            'trialEndsAt' => $trialEndsAt,
            'trialDaysRemaining' => $trialDaysRemaining,
            'monthlyPrice' => $pricing['monthlyPrice'],
            'monthlyPriceBreakdown' => $pricing['breakdown'],
            'requiresContactForPricing' => $pricing['requiresContact'],
        ]);
    }

    public function subscribe(Request $request): RedirectResponse
    {
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || ($user['role'] ?? null) !== 'admin') {
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

        $settings['subscription_status'] = 'active';
        $settings['subscription_started_at'] = Carbon::now()->toIso8601String();
        unset($settings['trial_cancel_requested_at']);

        DB::connection('sharpfleet')
            ->table('organisations')
            ->where('id', $organisationId)
            ->update([
                'settings' => json_encode($settings),
                'updated_at' => now(),
            ]);

        return redirect('/app/sharpfleet/admin/account')
            ->with('success', 'Subscription activated.');
    }

    public function cancelTrial(Request $request): RedirectResponse
    {
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || ($user['role'] ?? null) !== 'admin') {
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

        if (!$user || ($user['role'] ?? null) !== 'admin') {
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
