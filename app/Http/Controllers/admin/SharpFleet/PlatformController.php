<?php

namespace App\Http\Controllers\Admin\SharpFleet;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class PlatformController extends Controller
{
    private const DISPLAY_TIMEZONE = 'Australia/Brisbane';

    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

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

        $billingKeys = $this->billingKeysForOrganisations();
        $timezone = $this->organisationTimezone($organisationId);

        return view('admin.sharpfleet.organisations.show', [
            'organisation' => $organisation,
            'billingKeys' => $billingKeys,
            'usersCount' => $usersCount,
            'vehiclesCount' => $vehiclesCount,
            'timezone' => $timezone,
            'displayTimezone' => self::DISPLAY_TIMEZONE,
        ]);
    }

    public function editOrganisation(int $organisationId)
    {
        $organisation = DB::connection('sharpfleet')
            ->table('organisations')
            ->where('id', $organisationId)
            ->first();

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

        return redirect()->route('admin.sharpfleet.organisations.show', $organisationId)
            ->with('success', 'Subscriber updated.');
    }

    public function organisationUsers(Request $request, int $organisationId)
    {
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

        return redirect()->route('admin.sharpfleet.organisations.users', $organisationId)
            ->with('success', 'User updated.');
    }

    public function organisationVehicles(Request $request, int $organisationId)
    {
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

    public function vehicle(int $vehicleId)
    {
        $vehicle = DB::connection('sharpfleet')
            ->table('vehicles')
            ->where('id', $vehicleId)
            ->first();

        if (!$vehicle) {
            abort(404, 'Vehicle not found');
        }

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
