<?php

namespace App\Http\Controllers\Admin\SharpFleet;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PlatformController extends Controller
{
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

        return view('admin.sharpfleet.index', [
            'q' => $q,
            'organisations' => $organisations,
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

        return view('admin.sharpfleet.organisations.show', [
            'organisation' => $organisation,
            'billingKeys' => $billingKeys,
            'usersCount' => $usersCount,
            'vehiclesCount' => $vehiclesCount,
        ]);
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
        ]);
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
}
