<?php

namespace App\Http\Controllers\SharpFleet\Admin;

use App\Http\Controllers\Controller;
use App\Services\SharpFleet\BranchService;
use App\Support\SharpFleet\Roles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BranchController extends Controller
{
    private function isValidTimezone(string $timezone): bool
    {
        $timezone = trim($timezone);
        if ($timezone === '') {
            return false;
        }

        return in_array($timezone, timezone_identifiers_list(), true);
    }

    public function index(Request $request)
    {
        $fleetUser = $request->session()->get('sharpfleet.user');
        if (!$fleetUser || !Roles::canManageBranches($fleetUser)) {
            abort(403, 'Admin access only');
        }
        $organisationId = (int) ($fleetUser['organisation_id'] ?? 0);

        $branchService = new BranchService();
        $branchesEnabled = $branchService->branchesEnabled();

        $branches = collect();
        if ($branchesEnabled) {
            // Admin should see active + inactive for management
            $query = DB::connection('sharpfleet')
                ->table('branches')
                ->where('organisation_id', $organisationId);

            if (Schema::connection('sharpfleet')->hasColumn('branches', 'is_default')) {
                $query->orderByDesc('is_default');
            }

            $branches = $query->orderBy('name')->get();
        }

        return view('sharpfleet.admin.branches.index', [
            'branchesEnabled' => $branchesEnabled,
            'branches' => $branches,
        ]);
    }

    public function create(Request $request)
    {
        $fleetUser = $request->session()->get('sharpfleet.user');
        if (!$fleetUser || !Roles::canManageBranches($fleetUser)) {
            abort(403, 'Admin access only');
        }
        $organisationId = (int) ($fleetUser['organisation_id'] ?? 0);

        $branchService = new BranchService();
        if (!$branchService->branchesEnabled()) {
            return redirect('/app/sharpfleet/admin/branches')
                ->withErrors(['error' => 'Branches are not enabled yet. Run the SQL installer in docs/sharpfleet-branches.sql first.']);
        }

        $default = $branchService->getDefaultBranch($organisationId);

        return view('sharpfleet.admin.branches.create', [
            'defaultTimezone' => $default ? (string) ($default->timezone ?? '') : '',
        ]);
    }

    public function store(Request $request)
    {
        $fleetUser = $request->session()->get('sharpfleet.user');
        if (!$fleetUser || !Roles::canManageBranches($fleetUser)) {
            abort(403, 'Admin access only');
        }
        $organisationId = (int) ($fleetUser['organisation_id'] ?? 0);

        $branchService = new BranchService();
        if (!$branchService->branchesEnabled()) {
            return redirect('/app/sharpfleet/admin/branches')
                ->withErrors(['error' => 'Branches are not enabled yet. Run the SQL installer in docs/sharpfleet-branches.sql first.']);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'timezone' => ['required', 'string', 'max:100'],
            'is_default' => ['nullable', 'in:0,1'],
        ]);

        $name = trim((string) ($validated['name'] ?? ''));
        $timezone = trim((string) ($validated['timezone'] ?? ''));
        $wantsDefault = (int) ($validated['is_default'] ?? 0) === 1;

        if (!$this->isValidTimezone($timezone)) {
            return back()
                ->withErrors(['timezone' => 'Please enter a valid IANA timezone (e.g. Australia/Brisbane).'])
                ->withInput();
        }

        $exists = DB::connection('sharpfleet')
            ->table('branches')
            ->where('organisation_id', $organisationId)
            ->where('name', $name)
            ->exists();

        if ($exists) {
            return back()
                ->withErrors(['name' => 'A branch with this name already exists.'])
                ->withInput();
        }

        $branchesCount = (int) DB::connection('sharpfleet')
            ->table('branches')
            ->where('organisation_id', $organisationId)
            ->count();

        $payload = [
            'organisation_id' => $organisationId,
            'name' => $name,
            'timezone' => $timezone,
        ];

        if (Schema::connection('sharpfleet')->hasColumn('branches', 'is_active')) {
            $payload['is_active'] = 1;
        }

        $shouldBeDefault = $wantsDefault || $branchesCount === 0;
        if ($shouldBeDefault && Schema::connection('sharpfleet')->hasColumn('branches', 'is_default')) {
            $payload['is_default'] = 1;
        }

        if (Schema::connection('sharpfleet')->hasColumn('branches', 'created_at')) {
            $payload['created_at'] = now();
        }
        if (Schema::connection('sharpfleet')->hasColumn('branches', 'updated_at')) {
            $payload['updated_at'] = now();
        }

        DB::connection('sharpfleet')->transaction(function () use ($organisationId, $payload, $shouldBeDefault) {
            $branchId = (int) DB::connection('sharpfleet')->table('branches')->insertGetId($payload);

            if ($shouldBeDefault && Schema::connection('sharpfleet')->hasColumn('branches', 'is_default')) {
                DB::connection('sharpfleet')
                    ->table('branches')
                    ->where('organisation_id', $organisationId)
                    ->where('id', '!=', $branchId)
                    ->update(['is_default' => 0]);
            }
        });

        return redirect('/app/sharpfleet/admin/branches')
            ->with('success', 'Branch created.');
    }

    public function edit(Request $request, int $branchId)
    {
        $fleetUser = $request->session()->get('sharpfleet.user');
        if (!$fleetUser || !Roles::canManageBranches($fleetUser)) {
            abort(403, 'Admin access only');
        }
        $organisationId = (int) ($fleetUser['organisation_id'] ?? 0);

        $branchService = new BranchService();
        if (!$branchService->branchesEnabled()) {
            return redirect('/app/sharpfleet/admin/branches')
                ->withErrors(['error' => 'Branches are not enabled yet. Run the SQL installer in docs/sharpfleet-branches.sql first.']);
        }

        $branch = DB::connection('sharpfleet')
            ->table('branches')
            ->where('organisation_id', $organisationId)
            ->where('id', $branchId)
            ->first();

        if (!$branch) {
            abort(404);
        }

        return view('sharpfleet.admin.branches.edit', [
            'branch' => $branch,
        ]);
    }

    public function update(Request $request, int $branchId)
    {
        $fleetUser = $request->session()->get('sharpfleet.user');
        if (!$fleetUser || !Roles::canManageBranches($fleetUser)) {
            abort(403, 'Admin access only');
        }
        $organisationId = (int) ($fleetUser['organisation_id'] ?? 0);

        $branchService = new BranchService();
        if (!$branchService->branchesEnabled()) {
            return redirect('/app/sharpfleet/admin/branches')
                ->withErrors(['error' => 'Branches are not enabled yet. Run the SQL installer in docs/sharpfleet-branches.sql first.']);
        }

        $branch = DB::connection('sharpfleet')
            ->table('branches')
            ->where('organisation_id', $organisationId)
            ->where('id', $branchId)
            ->first();

        if (!$branch) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'timezone' => ['required', 'string', 'max:100'],
            'is_default' => ['nullable', 'in:0,1'],
            'is_active' => ['nullable', 'in:0,1'],
        ]);

        $name = trim((string) ($validated['name'] ?? ''));
        $timezone = trim((string) ($validated['timezone'] ?? ''));
        $wantsDefault = (int) ($validated['is_default'] ?? 0) === 1;
        $wantsActive = (int) ($validated['is_active'] ?? 1) === 1;

        if (!$this->isValidTimezone($timezone)) {
            return back()
                ->withErrors(['timezone' => 'Please enter a valid IANA timezone (e.g. Australia/Brisbane).'])
                ->withInput();
        }

        $exists = DB::connection('sharpfleet')
            ->table('branches')
            ->where('organisation_id', $organisationId)
            ->where('name', $name)
            ->where('id', '!=', $branchId)
            ->exists();

        if ($exists) {
            return back()
                ->withErrors(['name' => 'A branch with this name already exists.'])
                ->withInput();
        }

        $update = [
            'name' => $name,
            'timezone' => $timezone,
        ];

        if (Schema::connection('sharpfleet')->hasColumn('branches', 'is_active')) {
            $update['is_active'] = $wantsActive ? 1 : 0;
        }

        if (Schema::connection('sharpfleet')->hasColumn('branches', 'updated_at')) {
            $update['updated_at'] = now();
        }

        DB::connection('sharpfleet')->transaction(function () use ($organisationId, $branchId, $update, $wantsDefault) {
            DB::connection('sharpfleet')
                ->table('branches')
                ->where('organisation_id', $organisationId)
                ->where('id', $branchId)
                ->update($update);

            if ($wantsDefault && Schema::connection('sharpfleet')->hasColumn('branches', 'is_default')) {
                DB::connection('sharpfleet')
                    ->table('branches')
                    ->where('organisation_id', $organisationId)
                    ->update(['is_default' => 0]);

                DB::connection('sharpfleet')
                    ->table('branches')
                    ->where('organisation_id', $organisationId)
                    ->where('id', $branchId)
                    ->update(['is_default' => 1]);
            }
        });

        return redirect('/app/sharpfleet/admin/branches')
            ->with('success', 'Branch updated.');
    }
}
