<?php

namespace App\Http\Controllers\Admin\SharpFleet;

use App\Http\Controllers\Controller;
use App\Services\SharpFleet\AuditLogService;
use App\Services\SharpFleet\StripeInvoiceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PlatformController extends Controller
{
    private const DISPLAY_TIMEZONE = 'Australia/Brisbane';

    private AuditLogService $audit;
    private StripeInvoiceService $stripeInvoices;

    public function __construct(AuditLogService $audit, StripeInvoiceService $stripeInvoices)
    {
        $this->audit = $audit;
        $this->stripeInvoices = $stripeInvoices;
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
        ]);

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
        ]);

        return redirect()->route('admin.sharpfleet.organisations.show', $organisationId)
            ->with('success', 'Subscriber updated.');
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
