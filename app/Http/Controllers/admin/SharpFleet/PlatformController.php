<?php

namespace App\Http\Controllers\Admin\SharpFleet;

use App\Http\Controllers\Controller;
use App\Services\SharpFleet\AuditLogService;
use App\Services\SharpFleet\StripeInvoiceService;
use App\Services\SharpFleet\StripeSubscriptionAdminService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class PlatformController extends Controller
{
    private const DISPLAY_TIMEZONE = 'Australia/Brisbane';

    private AuditLogService $audit;
    private StripeInvoiceService $stripeInvoices;
    private StripeSubscriptionAdminService $stripeSubscriptions;

    public function __construct(AuditLogService $audit, StripeInvoiceService $stripeInvoices, StripeSubscriptionAdminService $stripeSubscriptions)
    {
        $this->audit = $audit;
        $this->stripeInvoices = $stripeInvoices;
        $this->stripeSubscriptions = $stripeSubscriptions;
    }

    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $this->audit->logPlatformAdmin($request, 'sharpfleet.platform.index', null, null, [
            'q' => $q,
        ]);

        $orgQuery = DB::connection('sharpfleet')
            ->table('organisations')
            ->select([
                'organisations.*',
                DB::raw('(select count(*) from users where users.organisation_id = organisations.id) as users_count'),
                DB::raw('(select count(*) from vehicles where vehicles.organisation_id = organisations.id) as vehicles_count'),
            ])
            ->orderByDesc('organisations.id');

        if ($q !== '') {
            $orgQuery->where(function ($query) use ($q) {
                $query
                    ->where('organisations.name', 'like', '%' . $q . '%')
                    ->orWhere('organisations.industry', 'like', '%' . $q . '%');
            });
        }

        $organisations = $orgQuery->paginate(25)->withQueryString();

        $timezoneByOrganisationId = $this->timezoneMapForOrganisations(
            collect($organisations->items())->pluck('id')->filter()->map(fn ($v) => (int) $v)->values()->all()
        );

        return view('admin.sharpfleet.index', [
            'q' => $q,
            'organisations' => $organisations,
            'timezoneByOrganisationId' => $timezoneByOrganisationId,
            'displayTimezone' => self::DISPLAY_TIMEZONE,
        ]);
    }

    public function organisation(int $organisationId)
    {
        $organisation = DB::connection('sharpfleet')
            ->table('organisations')
            ->where('id', $organisationId)
            ->first();

        $this->audit->logPlatformAdmin(request(), 'sharpfleet.organisation.view', $organisationId, null, [
            'found' => (bool) $organisation,
        ]);

        if (!$organisation) {
            abort(404, 'Organisation not found');
        }

        $usersCount = (int) DB::connection('sharpfleet')
            ->table('users')
            ->where('organisation_id', $organisationId)
            ->count();

        $vehiclesCount = (int) DB::connection('sharpfleet')
            ->table('vehicles')
            ->where('organisation_id', $organisationId)
            ->count();

        $activeVehiclesCount = (int) DB::connection('sharpfleet')
            ->table('vehicles')
            ->where('organisation_id', $organisationId)
            ->where('is_active', 1)
            ->count();

        $billingKeys = $this->billingKeysForOrganisations();
        $timezone = $this->organisationTimezone($organisationId);

        $settings = [];
        if (!empty($organisation->settings)) {
            $decoded = json_decode((string) $organisation->settings, true);
            if (is_array($decoded)) {
                $settings = $decoded;
            }
        }

        $billingFromSettings = [
            'subscription_status' => (string) ($settings['subscription_status'] ?? ''),
            'subscription_started_at' => (string) ($settings['subscription_started_at'] ?? ''),
            'subscription_cancel_requested_at' => (string) ($settings['subscription_cancel_requested_at'] ?? ''),
            'stripe_customer_id' => (string) ($settings['stripe_customer_id'] ?? ''),
            'stripe_subscription_id' => (string) ($settings['stripe_subscription_id'] ?? ''),
            'stripe_price_id' => (string) ($settings['stripe_price_id'] ?? ''),
        ];

        $billingEstimate = $this->estimateMonthlyPrice($activeVehiclesCount);

        $recentBillingLogs = collect();
        if (Schema::connection('sharpfleet')->hasTable('sharpfleet_audit_logs')) {
            $recentBillingLogs = DB::connection('sharpfleet')
                ->table('sharpfleet_audit_logs')
                ->where('organisation_id', $organisationId)
                ->where(function ($q) {
                    $q
                        ->where('action', 'like', 'Billing:%')
                        ->orWhere('action', 'like', '%Subscription%')
                        ->orWhere('action', 'like', '%Vehicle%');
                })
                ->orderByDesc('id')
                ->limit(20)
                ->get();
        }

        $stripeInvoices = [];
        $stripeInvoicesError = null;
        $stripeCustomerId = trim((string) ($billingFromSettings['stripe_customer_id'] ?? ''));
        if ($stripeCustomerId !== '') {
            try {
                $stripeInvoices = $this->stripeInvoices->listInvoicesForCustomer($stripeCustomerId, 10);
            } catch (\Throwable $e) {
                $stripeInvoicesError = $e->getMessage();
            }
        }

        return view('admin.sharpfleet.organisations.show', [
            'organisation' => $organisation,
            'billingKeys' => $billingKeys,
            'usersCount' => $usersCount,
            'vehiclesCount' => $vehiclesCount,
            'activeVehiclesCount' => $activeVehiclesCount,
            'billingFromSettings' => $billingFromSettings,
            'billingEstimate' => $billingEstimate,
            'recentBillingLogs' => $recentBillingLogs,
            'stripeInvoices' => $stripeInvoices,
            'stripeInvoicesError' => $stripeInvoicesError,
            'timezone' => $timezone,
            'displayTimezone' => self::DISPLAY_TIMEZONE,
        ]);
    }

    private function estimateMonthlyPrice(int $vehiclesCount): array
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

    public function editOrganisation(int $organisationId)
    {
        $organisation = DB::connection('sharpfleet')
            ->table('organisations')
            ->where('id', $organisationId)
            ->first();

        $this->audit->logPlatformAdmin(request(), 'sharpfleet.organisation.edit.view', $organisationId, null, [
            'found' => (bool) $organisation,
        ]);

        if (!$organisation) {
            abort(404, 'Organisation not found');
        }

        $timezone = $this->organisationTimezone($organisationId);

        $settings = [];
        if (!empty($organisation->settings)) {
            $decoded = json_decode((string) $organisation->settings, true);
            if (is_array($decoded)) {
                $settings = $decoded;
            }
        }

        $billingOverride = [];
        if (!empty($settings['billing_override']) && is_array($settings['billing_override'])) {
            $billingOverride = $settings['billing_override'];
        }

        $billingAccessUntilBrisbane = null;
        if (!empty($billingOverride['access_until_utc'])) {
            try {
                $billingAccessUntilBrisbane = Carbon::parse((string) $billingOverride['access_until_utc'], 'UTC')
                    ->timezone(self::DISPLAY_TIMEZONE)
                    ->format('Y-m-d\\TH:i');
            } catch (\Throwable $e) {
                $billingAccessUntilBrisbane = null;
            }
        }

        $trialEndsBrisbane = null;
        if (!empty($organisation->trial_ends_at)) {
            try {
                $trialEndsBrisbane = Carbon::parse($organisation->trial_ends_at, 'UTC')
                    ->timezone(self::DISPLAY_TIMEZONE)
                    ->format('Y-m-d\\TH:i');
            } catch (\Throwable $e) {
                $trialEndsBrisbane = null;
            }
        }

        return view('admin.sharpfleet.organisations.edit', [
            'organisation' => $organisation,
            'timezone' => $timezone,
            'trialEndsBrisbane' => $trialEndsBrisbane,
            'billingMode' => (string) ($billingOverride['mode'] ?? ''),
            'billingAccessUntilBrisbane' => $billingAccessUntilBrisbane,
            'billingVehicleCapOverride' => $billingOverride['vehicle_cap_override'] ?? null,
            'billingPriceOverrideMonthly' => $billingOverride['price_override_monthly'] ?? null,
            'billingInvoiceReference' => (string) ($billingOverride['invoice_reference'] ?? ''),
            'billingNotes' => (string) ($billingOverride['notes'] ?? ''),
            'displayTimezone' => self::DISPLAY_TIMEZONE,
        ]);
    }

    public function updateOrganisation(Request $request, int $organisationId)
    {
        $organisation = DB::connection('sharpfleet')
            ->table('organisations')
            ->where('id', $organisationId)
            ->first();

        if (!$organisation) {
            abort(404, 'Organisation not found');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'industry' => 'nullable|string|max:150',
            'company_type' => 'nullable|string|max:50',

            // Admin UI inputs are in Brisbane time
            'trial_ends_at' => 'nullable|string|max:30',
            'extend_trial_days' => 'nullable|integer|min:1|max:3650',

            // Billing / access override (stored in organisations.settings)
            'billing_mode' => 'nullable|string|in:stripe,manual_invoice,comped',
            'billing_access_until' => 'nullable|string|max:30',
            'billing_vehicle_cap_override' => 'nullable|integer|min:1|max:100000',
            'billing_price_override_monthly' => 'nullable|numeric|min:0|max:100000',
            'billing_invoice_reference' => 'nullable|string|max:100',
            'billing_notes' => 'nullable|string|max:1000',

            // Stripe admin actions
            'stripe_admin_action' => 'nullable|string|in:uncancel,create_checkout',
        ]);

        $selectedBillingMode = trim((string) ($validated['billing_mode'] ?? ''));
        $rawBillingAccessUntil = trim((string) ($validated['billing_access_until'] ?? ''));
        $stripeAdminAction = trim((string) ($validated['stripe_admin_action'] ?? ''));

        if (in_array($selectedBillingMode, ['manual_invoice', 'comped'], true) && $rawBillingAccessUntil === '') {
            throw ValidationException::withMessages([
                'billing_access_until' => 'Access Until is required when Billing Mode is Manual invoice or Comped / Free.',
            ]);
        }

        // Read existing settings upfront so we can decide whether to cancel Stripe before mutating anything.
        $previousSettings = [];
        if (!empty($organisation->settings)) {
            $decoded = json_decode((string) $organisation->settings, true);
            if (is_array($decoded)) {
                $previousSettings = $decoded;
            }
        }

        // Keep an immutable copy so we can detect changes even if we mutate $previousSettings.
        $originalSettings = $previousSettings;

        $stripeCancelResult = null;
        $stripeUncancelResult = null;
        $stripeCheckoutUrl = null;

        $existingStripeSubId = trim((string) ($previousSettings['stripe_subscription_id'] ?? ''));

        if ($stripeAdminAction !== '' && !in_array($selectedBillingMode, ['', 'stripe'], true)) {
            throw ValidationException::withMessages([
                'stripe_admin_action' => 'Stripe Admin Action can only be used when Billing Mode is Default or Stripe.',
            ]);
        }

        if (in_array($selectedBillingMode, ['manual_invoice', 'comped'], true)) {
            if ($existingStripeSubId !== '') {
                try {
                    // Default: cancel at period end to avoid mid-cycle cut-off.
                    $stripeCancelResult = $this->stripeSubscriptions->cancelSubscription($existingStripeSubId, true);

                    // Mark as cancelled in settings immediately so internal logic stops treating it as active.
                    $previousSettings['subscription_status'] = 'cancelled';
                    $previousSettings['subscription_cancel_requested_at'] = Carbon::now('UTC')->toIso8601String();
                } catch (\Throwable $e) {
                    throw ValidationException::withMessages([
                        'billing_mode' => 'Unable to cancel the Stripe subscription. Billing mode was not changed. (' . $e->getMessage() . ')',
                    ]);
                }
            }
        } elseif ($stripeAdminAction === 'uncancel') {
            if ($existingStripeSubId === '') {
                throw ValidationException::withMessages([
                    'stripe_admin_action' => 'No Stripe subscription id is stored for this organisation. Use "Create Stripe Checkout link" instead.',
                ]);
            }

            try {
                $stripeUncancelResult = $this->stripeSubscriptions->uncancelSubscription($existingStripeSubId);

                // Ensure our local settings treat the subscription as active again.
                $previousSettings['subscription_status'] = 'active';
                unset($previousSettings['subscription_cancel_requested_at']);
            } catch (\Throwable $e) {
                throw ValidationException::withMessages([
                    'stripe_admin_action' => 'Unable to re-enable the Stripe subscription. (' . $e->getMessage() . ')',
                ]);
            }
        } elseif ($stripeAdminAction === 'create_checkout') {
            // Avoid creating a second subscription when our settings already say active.
            if (($previousSettings['subscription_status'] ?? null) === 'active') {
                throw ValidationException::withMessages([
                    'stripe_admin_action' => 'Subscription is already active in Stripe settings. Cancel first or use Re-enable Stripe if it was scheduled to cancel.',
                ]);
            }

            $activeVehiclesCount = (int) DB::connection('sharpfleet')
                ->table('vehicles')
                ->where('organisation_id', $organisationId)
                ->where('is_active', 1)
                ->count();

            if ($activeVehiclesCount < 1) {
                throw ValidationException::withMessages([
                    'stripe_admin_action' => 'Organisation must have at least 1 active vehicle to create a Stripe subscription checkout link.',
                ]);
            }

            try {
                $stripeCheckoutUrl = $this->stripeSubscriptions->createCheckoutUrl(
                    $organisationId,
                    $activeVehiclesCount,
                    $request->getSchemeAndHttpHost()
                );
            } catch (\Throwable $e) {
                throw ValidationException::withMessages([
                    'stripe_admin_action' => 'Unable to create a Stripe Checkout link. (' . $e->getMessage() . ')',
                ]);
            }
        }

        DB::connection('sharpfleet')
            ->table('organisations')
            ->where('id', $organisationId)
            ->update([
                'name' => $validated['name'],
                'industry' => $validated['industry'] ?? null,
                'company_type' => $validated['company_type'] ?? null,
            ]);

        $setTrialEndsUtc = null;

        $extendDays = (int) ($validated['extend_trial_days'] ?? 0);
        if ($extendDays > 0) {
            $base = Carbon::now('UTC');
            if (!empty($organisation->trial_ends_at)) {
                try {
                    $current = Carbon::parse($organisation->trial_ends_at, 'UTC');
                    if ($current->greaterThan($base)) {
                        $base = $current;
                    }
                } catch (\Throwable $e) {
                    // ignore, fall back to now
                }
            }
            $setTrialEndsUtc = $base->copy()->addDays($extendDays);
        } elseif (array_key_exists('trial_ends_at', $validated)) {
            $raw = trim((string) ($validated['trial_ends_at'] ?? ''));
            if ($raw === '') {
                $setTrialEndsUtc = null;
            } else {
                $setTrialEndsUtc = Carbon::createFromFormat('Y-m-d\\TH:i', $raw, self::DISPLAY_TIMEZONE)
                    ->timezone('UTC');
            }
        }

        if (!is_null($setTrialEndsUtc) || (isset($validated['trial_ends_at']) && trim((string) $validated['trial_ends_at']) === '')) {
            DB::connection('sharpfleet')
                ->table('organisations')
                ->where('id', $organisationId)
                ->update([
                    'trial_ends_at' => $setTrialEndsUtc?->toDateTimeString(),
                ]);
        }

        // Update settings JSON (billing override)
        $newSettings = $previousSettings;
        $previousBillingOverride = (is_array(($previousSettings['billing_override'] ?? null))) ? $previousSettings['billing_override'] : [];

        $mode = $selectedBillingMode;
        $invoiceRef = trim((string) ($validated['billing_invoice_reference'] ?? ''));
        $notes = trim((string) ($validated['billing_notes'] ?? ''));

        $accessUntilUtc = null;
        if ($rawBillingAccessUntil !== '') {
            $accessUntilUtc = Carbon::createFromFormat('Y-m-d\\TH:i', $rawBillingAccessUntil, self::DISPLAY_TIMEZONE)
                ->timezone('UTC')
                ->toDateTimeString();
        }

        $billingOverride = [
            'mode' => $mode,
            'access_until_utc' => $accessUntilUtc,
            'vehicle_cap_override' => $validated['billing_vehicle_cap_override'] ?? null,
            'price_override_monthly' => $validated['billing_price_override_monthly'] ?? null,
            'invoice_reference' => $invoiceRef !== '' ? $invoiceRef : null,
            'notes' => $notes !== '' ? $notes : null,
            'updated_at_utc' => Carbon::now('UTC')->toDateTimeString(),
        ];

        if (is_array($stripeCancelResult)) {
            $billingOverride['stripe_cancel'] = $stripeCancelResult;
        }

        if (is_array($stripeUncancelResult)) {
            $billingOverride['stripe_uncancel'] = $stripeUncancelResult;
        }

        // Keep settings tidy: if everything is empty, remove the override key.
        $hasAnyOverride = ($billingOverride['mode'] !== '')
            || !empty($billingOverride['access_until_utc'])
            || !empty($billingOverride['vehicle_cap_override'])
            || !empty($billingOverride['price_override_monthly'])
            || !empty($billingOverride['invoice_reference'])
            || !empty($billingOverride['notes']);

        if ($hasAnyOverride) {
            $newSettings['billing_override'] = $billingOverride;
        } else {
            unset($newSettings['billing_override']);
        }

        if ($newSettings !== $originalSettings) {
            DB::connection('sharpfleet')
                ->table('organisations')
                ->where('id', $organisationId)
                ->update([
                    'settings' => json_encode($newSettings, JSON_UNESCAPED_SLASHES),
                ]);
        }

        $this->audit->logPlatformAdmin($request, 'sharpfleet.organisation.update', $organisationId, null, [
            'updated' => [
                'name' => $validated['name'],
                'industry' => $validated['industry'] ?? null,
                'company_type' => $validated['company_type'] ?? null,
            ],
            'trial' => [
                'previous_trial_ends_at_utc' => $organisation->trial_ends_at ?? null,
                'set_trial_ends_at_utc' => $setTrialEndsUtc?->toDateTimeString(),
                'extend_trial_days' => (int) ($validated['extend_trial_days'] ?? 0),
            ],
            'billing_override' => [
                'previous' => $previousBillingOverride,
                'new' => $hasAnyOverride ? $billingOverride : null,
            ],
            'stripe_admin' => [
                'action' => $stripeAdminAction !== '' ? $stripeAdminAction : null,
                'uncancel_result' => $stripeUncancelResult,
                'checkout_url_created' => $stripeCheckoutUrl !== null,
            ],
        ]);

        $redirect = redirect()->route('admin.sharpfleet.organisations.show', $organisationId)
            ->with('success', 'Subscriber updated.');

        if (is_array($stripeCancelResult)) {
            $redirect->with('stripe_cancel_result', $stripeCancelResult);
        }

        if (is_array($stripeUncancelResult)) {
            $redirect->with('stripe_uncancel_result', $stripeUncancelResult);
        }

        if (is_string($stripeCheckoutUrl) && $stripeCheckoutUrl !== '') {
            $redirect->with('stripe_checkout_url', $stripeCheckoutUrl);
        }

        return $redirect;
    }

    public function organisationUsers(Request $request, int $organisationId)
    {
        $this->audit->logPlatformAdmin($request, 'sharpfleet.organisation.users.view', $organisationId);

        $organisation = DB::connection('sharpfleet')
            ->table('organisations')
            ->where('id', $organisationId)
            ->first();

        if (!$organisation) {
            abort(404, 'Organisation not found');
        }

        $users = DB::connection('sharpfleet')
            ->table('users')
            ->select([
                'id',
                'organisation_id',
                'email',
                'first_name',
                'last_name',
                'role',
                'is_driver',
                'trial_ends_at',
                'created_at',
            ])
            ->where('organisation_id', $organisationId)
            ->orderBy('role')
            ->orderBy('email')
            ->paginate(50)
            ->withQueryString();

        return view('admin.sharpfleet.organisations.users', [
            'organisation' => $organisation,
            'users' => $users,
            'displayTimezone' => self::DISPLAY_TIMEZONE,
        ]);
    }

    public function editOrganisationUser(int $organisationId, int $userId)
    {
        $this->audit->logPlatformAdmin(request(), 'sharpfleet.organisation.user.edit.view', $organisationId, $userId);

        $organisation = DB::connection('sharpfleet')
            ->table('organisations')
            ->where('id', $organisationId)
            ->first();

        if (!$organisation) {
            abort(404, 'Organisation not found');
        }

        $user = DB::connection('sharpfleet')
            ->table('users')
            ->where('id', $userId)
            ->where('organisation_id', $organisationId)
            ->first();

        if (!$user) {
            abort(404, 'User not found');
        }

        $trialEndsBrisbane = null;
        if (!empty($user->trial_ends_at)) {
            try {
                $trialEndsBrisbane = Carbon::parse($user->trial_ends_at, 'UTC')
                    ->timezone(self::DISPLAY_TIMEZONE)
                    ->format('Y-m-d\\TH:i');
            } catch (\Throwable $e) {
                $trialEndsBrisbane = null;
            }
        }

        return view('admin.sharpfleet.users.edit', [
            'organisation' => $organisation,
            'user' => $user,
            'trialEndsBrisbane' => $trialEndsBrisbane,
            'displayTimezone' => self::DISPLAY_TIMEZONE,
        ]);
    }

    public function updateOrganisationUser(Request $request, int $organisationId, int $userId)
    {
        $organisation = DB::connection('sharpfleet')
            ->table('organisations')
            ->where('id', $organisationId)
            ->first();

        if (!$organisation) {
            abort(404, 'Organisation not found');
        }

        $user = DB::connection('sharpfleet')
            ->table('users')
            ->where('id', $userId)
            ->where('organisation_id', $organisationId)
            ->first();

        if (!$user) {
            abort(404, 'User not found');
        }

        $validated = $request->validate([
            'trial_ends_at' => 'nullable|string|max:30',
            'extend_trial_days' => 'nullable|integer|min:1|max:3650',
        ]);

        $setTrialEndsUtc = null;

        $extendDays = (int) ($validated['extend_trial_days'] ?? 0);
        if ($extendDays > 0) {
            $base = Carbon::now('UTC');
            if (!empty($user->trial_ends_at)) {
                try {
                    $current = Carbon::parse($user->trial_ends_at, 'UTC');
                    if ($current->greaterThan($base)) {
                        $base = $current;
                    }
                } catch (\Throwable $e) {
                    // ignore
                }
            }
            $setTrialEndsUtc = $base->copy()->addDays($extendDays);
        } elseif (array_key_exists('trial_ends_at', $validated)) {
            $raw = trim((string) ($validated['trial_ends_at'] ?? ''));
            if ($raw === '') {
                $setTrialEndsUtc = null;
            } else {
                $setTrialEndsUtc = Carbon::createFromFormat('Y-m-d\\TH:i', $raw, self::DISPLAY_TIMEZONE)
                    ->timezone('UTC');
            }
        }

        if (!is_null($setTrialEndsUtc) || (isset($validated['trial_ends_at']) && trim((string) $validated['trial_ends_at']) === '')) {
            DB::connection('sharpfleet')
                ->table('users')
                ->where('id', $userId)
                ->where('organisation_id', $organisationId)
                ->update([
                    'trial_ends_at' => $setTrialEndsUtc?->toDateTimeString(),
                ]);
        }

        $this->audit->logPlatformAdmin($request, 'sharpfleet.organisation.user.update', $organisationId, $userId, [
            'trial' => [
                'previous_trial_ends_at_utc' => $user->trial_ends_at ?? null,
                'set_trial_ends_at_utc' => $setTrialEndsUtc?->toDateTimeString(),
                'extend_trial_days' => (int) ($validated['extend_trial_days'] ?? 0),
            ],
        ]);

        return redirect()->route('admin.sharpfleet.organisations.users', $organisationId)
            ->with('success', 'User updated.');
    }

    public function organisationVehicles(Request $request, int $organisationId)
    {
        $this->audit->logPlatformAdmin($request, 'sharpfleet.organisation.vehicles.view', $organisationId);

        $organisation = DB::connection('sharpfleet')
            ->table('organisations')
            ->where('id', $organisationId)
            ->first();

        if (!$organisation) {
            abort(404, 'Organisation not found');
        }

        $vehicles = DB::connection('sharpfleet')
            ->table('vehicles')
            ->where('organisation_id', $organisationId)
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(50)
            ->withQueryString();

        return view('admin.sharpfleet.organisations.vehicles', [
            'organisation' => $organisation,
            'vehicles' => $vehicles,
        ]);
    }

    public function vehicle(Request $request, int $vehicleId)
    {
        $vehicle = DB::connection('sharpfleet')
            ->table('vehicles')
            ->where('id', $vehicleId)
            ->first();

        if (!$vehicle) {
            abort(404, 'Vehicle not found');
        }

        $this->audit->logPlatformAdmin(
            $request,
            'sharpfleet.vehicle.view',
            !empty($vehicle->organisation_id) ? (int) $vehicle->organisation_id : null,
            null,
            ['vehicle_id' => $vehicleId]
        );

        $organisation = null;
        if (!empty($vehicle->organisation_id)) {
            $organisation = DB::connection('sharpfleet')
                ->table('organisations')
                ->where('id', (int) $vehicle->organisation_id)
                ->first();
        }

        $columns = [];
        try {
            $columns = Schema::connection('sharpfleet')->getColumnListing('vehicles');
        } catch (\Throwable $e) {
            $columns = [];
        }

        return view('admin.sharpfleet.vehicles.show', [
            'vehicle' => $vehicle,
            'organisation' => $organisation,
            'columns' => $columns,
        ]);
    }

    public function auditLogs(Request $request)
    {
        $this->audit->logPlatformAdmin($request, 'sharpfleet.audit_logs.index');

        if (!Schema::connection('sharpfleet')->hasTable('sharpfleet_audit_logs')) {
            return view('admin.sharpfleet.audit-logs.index', [
                'logs' => collect(),
                'filters' => $this->auditLogFilters($request),
                'tableMissing' => true,
                'displayTimezone' => self::DISPLAY_TIMEZONE,
            ]);
        }

        $filters = $this->auditLogFilters($request);

        $query = DB::connection('sharpfleet')->table('sharpfleet_audit_logs');

        if (!empty($filters['organisation_id'])) {
            $query->where('organisation_id', (int) $filters['organisation_id']);
        }

        if (!empty($filters['actor_type'])) {
            $query->where('actor_type', $filters['actor_type']);
        }

        if (!empty($filters['actor_email'])) {
            $query->where('actor_email', 'like', '%' . $filters['actor_email'] . '%');
        }

        if (!empty($filters['action'])) {
            $query->where('action', 'like', '%' . $filters['action'] . '%');
        }

        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from'] . ' 00:00:00');
        }

        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to'] . ' 23:59:59');
        }

        if (!empty($filters['q'])) {
            $q = $filters['q'];
            $query->where(function ($sub) use ($q) {
                $sub
                    ->where('action', 'like', '%' . $q . '%')
                    ->orWhere('actor_email', 'like', '%' . $q . '%')
                    ->orWhere('actor_name', 'like', '%' . $q . '%')
                    ->orWhere('path', 'like', '%' . $q . '%')
                    ->orWhere('context_json', 'like', '%' . $q . '%');
            });
        }

        $logs = $query->orderByDesc('id')->paginate(50)->withQueryString();

        return view('admin.sharpfleet.audit-logs.index', [
            'logs' => $logs,
            'filters' => $filters,
            'tableMissing' => false,
            'displayTimezone' => self::DISPLAY_TIMEZONE,
        ]);
    }

    private function auditLogFilters(Request $request): array
    {
        return [
            'organisation_id' => trim((string) $request->query('organisation_id', '')),
            'actor_type' => trim((string) $request->query('actor_type', '')),
            'actor_email' => trim((string) $request->query('actor_email', '')),
            'action' => trim((string) $request->query('action', '')),
            'date_from' => trim((string) $request->query('date_from', '')),
            'date_to' => trim((string) $request->query('date_to', '')),
            'q' => trim((string) $request->query('q', '')),
        ];
    }

    private function billingKeysForOrganisations(): array
    {
        $columns = [];
        try {
            $columns = Schema::connection('sharpfleet')->getColumnListing('organisations');
        } catch (\Throwable $e) {
            $columns = [];
        }

        $preferred = [
            'trial_ends_at',
            'plan',
            'plan_id',
            'status',
            'subscription_status',
            'subscription_id',
            'subscription_ends_at',
            'billing_email',
            'billing_status',
            'stripe_customer_id',
            'stripe_subscription_id',
            'stripe_price_id',
            'created_at',
            'updated_at',
        ];

        $keys = [];
        foreach ($preferred as $key) {
            if (in_array($key, $columns, true)) {
                $keys[] = $key;
            }
        }

        // Add any other billing-ish columns not in preferred list.
        foreach ($columns as $col) {
            if (in_array($col, $keys, true)) {
                continue;
            }
            if (preg_match('/(trial|plan|subscr|billing|invoice|stripe|price|customer|renew|paid|status)/i', (string) $col)) {
                $keys[] = $col;
            }
        }

        return $keys;
    }

    private function organisationTimezone(int $organisationId): string
    {
        try {
            $row = DB::connection('sharpfleet')
                ->table('company_settings')
                ->where('organisation_id', $organisationId)
                ->first();
        } catch (\Throwable $e) {
            return self::DISPLAY_TIMEZONE;
        }

        if (!$row || empty($row->settings_json)) {
            return self::DISPLAY_TIMEZONE;
        }

        $decoded = json_decode($row->settings_json, true) ?? [];
        $tz = (string) ($decoded['timezone'] ?? '');
        return $tz !== '' ? $tz : self::DISPLAY_TIMEZONE;
    }

    private function timezoneMapForOrganisations(array $organisationIds): array
    {
        if (empty($organisationIds)) {
            return [];
        }

        try {
            $rows = DB::connection('sharpfleet')
                ->table('company_settings')
                ->select(['organisation_id', 'settings_json'])
                ->whereIn('organisation_id', $organisationIds)
                ->get();
        } catch (\Throwable $e) {
            return [];
        }

        $map = [];
        foreach ($rows as $row) {
            $orgId = (int) ($row->organisation_id ?? 0);
            if ($orgId <= 0) {
                continue;
            }
            $decoded = json_decode($row->settings_json ?? '', true) ?? [];
            $tz = (string) ($decoded['timezone'] ?? '');
            if ($tz !== '') {
                $map[$orgId] = $tz;
            }
        }

        return $map;
    }
}
