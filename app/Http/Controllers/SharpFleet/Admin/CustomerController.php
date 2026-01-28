<?php

namespace App\Http\Controllers\SharpFleet\Admin;

use App\Http\Controllers\Controller;
use App\Services\SharpFleet\CustomerService;
use App\Services\SharpFleet\BranchService;
use App\Support\SharpFleet\Roles;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Schema;

class CustomerController extends Controller
{
    protected CustomerService $customerService;

    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }

    private function getSharpFleetUser(Request $request): ?array
    {
        $user = $request->session()->get('sharpfleet.user');

        return is_array($user) ? $user : null;
    }

    public function index(Request $request)
    {
        $user = $this->getSharpFleetUser($request);

        if (!$user || !Roles::canManageFleet($user)) {
            abort(403);
        }

        $organisationId = (int) $user['organisation_id'];

        $customersTableExists = $this->customerService->customersTableExists();
        $branchesService = new BranchService();
        $branchesEnabled = $branchesService->branchesEnabled();
        $hasCustomerBranch = $customersTableExists
            && Schema::connection('sharpfleet')->hasColumn('customers', 'branch_id');
        $isCompanyAdmin = Roles::isCompanyAdmin($user);

        $branches = $branchesEnabled
            ? ($isCompanyAdmin
                ? $branchesService->getBranches($organisationId)
                : $branchesService->getBranchesForUser($organisationId, (int) $user['id']))
            : collect();

        $selectedBranchId = $isCompanyAdmin ? (int) $request->query('branch_id', 0) : 0;
        $branchIdsForFilter = [];
        if ($branchesEnabled && $hasCustomerBranch) {
            if ($isCompanyAdmin && $selectedBranchId > 0) {
                $branchIdsForFilter = [$selectedBranchId];
            } elseif (!$isCompanyAdmin) {
                $branchIdsForFilter = $branches->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
            }
        }

        $search = trim((string) $request->query('q', ''));
        $customers = $this->customerService->getCustomers(
            $organisationId,
            500,
            $branchIdsForFilter,
            $search
        );

        return view('sharpfleet.admin.customers.index', [
            'customers'            => $customers,
            'customersTableExists' => $customersTableExists,
            'branches' => $branches,
            'branchesEnabled' => $branchesEnabled,
            'hasCustomerBranch' => $hasCustomerBranch,
            'isCompanyAdmin' => $isCompanyAdmin,
            'selectedBranchId' => $selectedBranchId,
            'searchQuery' => $search,
        ]);
    }

    public function search(Request $request)
    {
        $user = $this->getSharpFleetUser($request);

        if (!$user || !Roles::canManageFleet($user)) {
            abort(403);
        }

        $organisationId = (int) $user['organisation_id'];
        if (!$this->customerService->customersTableExists()) {
            return response()->json([]);
        }

        $branchesService = new BranchService();
        $branchesEnabled = $branchesService->branchesEnabled();
        $hasCustomerBranch = Schema::connection('sharpfleet')->hasColumn('customers', 'branch_id');
        $isCompanyAdmin = Roles::isCompanyAdmin($user);

        $branches = $branchesEnabled
            ? ($isCompanyAdmin
                ? $branchesService->getBranches($organisationId)
                : $branchesService->getBranchesForUser($organisationId, (int) $user['id']))
            : collect();

        $selectedBranchId = $isCompanyAdmin ? (int) $request->query('branch_id', 0) : 0;
        $branchIdsForFilter = [];
        if ($branchesEnabled && $hasCustomerBranch) {
            if ($isCompanyAdmin && $selectedBranchId > 0) {
                $branchIdsForFilter = [$selectedBranchId];
            } elseif (!$isCompanyAdmin) {
                $branchIdsForFilter = $branches->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
            }
        }

        $search = trim((string) $request->query('query', ''));
        if ($search === '') {
            return response()->json([]);
        }

        $customers = $this->customerService->getCustomers(
            $organisationId,
            8,
            $branchIdsForFilter,
            $search
        );

        return response()->json(
            $customers->map(fn ($c) => [
                'id' => (int) $c->id,
                'name' => (string) ($c->name ?? ''),
            ])->values()
        );
    }

    public function create(Request $request)
    {
        $user = $this->getSharpFleetUser($request);

        if (!$user || !Roles::canManageFleet($user)) {
            abort(403);
        }

        $customersTableExists = $this->customerService->customersTableExists();

        $branchesService = new BranchService();
        $branchesEnabled = $branchesService->branchesEnabled();
        $hasCustomerBranch = $customersTableExists
            && Schema::connection('sharpfleet')->hasColumn('customers', 'branch_id');

        $branches = $branchesEnabled
            ? $branchesService->getBranchesForUser((int) $user['organisation_id'], (int) $user['id'])
            : collect();

        return view('sharpfleet.admin.customers.create', [
            'customersTableExists' => $customersTableExists,
            'branches' => $branches,
            'branchesEnabled' => $branchesEnabled,
            'hasCustomerBranch' => $hasCustomerBranch,
        ]);
    }

    public function edit(Request $request, int $customerId)
    {
        $user = $this->getSharpFleetUser($request);

        if (!$user || !Roles::canManageFleet($user)) {
            abort(403);
        }

        $organisationId = (int) $user['organisation_id'];

        $customersTableExists = $this->customerService->customersTableExists();
        if (!$customersTableExists) {
            abort(404);
        }

        $customer = $this->customerService->getCustomer($organisationId, $customerId);

        if (!$customer || (int) ($customer->is_active ?? 0) !== 1) {
            abort(404);
        }

        $branchesService = new BranchService();
        $branchesEnabled = $branchesService->branchesEnabled();
        $hasCustomerBranch = $customersTableExists
            && Schema::connection('sharpfleet')->hasColumn('customers', 'branch_id');

        $branches = $branchesEnabled
            ? $branchesService->getBranchesForUser((int) $user['organisation_id'], (int) $user['id'])
            : collect();

        return view('sharpfleet.admin.customers.edit', [
            'customer' => $customer,
            'customersTableExists' => $customersTableExists,
            'branches' => $branches,
            'branchesEnabled' => $branchesEnabled,
            'hasCustomerBranch' => $hasCustomerBranch,
        ]);
    }

    public function update(Request $request, int $customerId): RedirectResponse
    {
        $user = $this->getSharpFleetUser($request);

        if (!$user || !Roles::canManageFleet($user)) {
            abort(403);
        }

        $organisationId = (int) $user['organisation_id'];

        if (!$this->customerService->customersTableExists()) {
            return back()->withErrors([
                'customers' => "Customers can't be managed yet because the database is missing the sharpfleet.customers table.",
            ]);
        }

        $customer = $this->customerService->getCustomer($organisationId, $customerId);
        if (!$customer || (int) ($customer->is_active ?? 0) !== 1) {
            abort(404);
        }

        $branchesService = new BranchService();
        $branchesEnabled = $branchesService->branchesEnabled();
        $hasCustomerBranch = Schema::connection('sharpfleet')->hasColumn('customers', 'branch_id');
        $branches = $branchesEnabled
            ? $branchesService->getBranchesForUser($organisationId, (int) $user['id'])
            : collect();
        $branchIds = $branches->pluck('id')->map(fn ($id) => (int) $id)->values()->all();

        $rules = [
            'name' => ['required', 'string', 'max:150'],
        ];

        if ($branchesEnabled && $hasCustomerBranch && count($branchIds) > 1) {
            $rules['branch_id'] = ['required', 'integer', 'in:' . implode(',', $branchIds)];
        } elseif ($branchesEnabled && $hasCustomerBranch && count($branchIds) === 1) {
            $rules['branch_id'] = ['nullable', 'integer', 'in:' . implode(',', $branchIds)];
        }

        $validated = $request->validate($rules);

        $branchId = null;
        if ($branchesEnabled && $hasCustomerBranch && $request->filled('branch_id')) {
            $branchId = (int) $request->input('branch_id');
        } elseif ($branchesEnabled && $hasCustomerBranch && count($branchIds) === 1) {
            $branchId = (int) $branchIds[0];
        }

        $this->customerService->updateCustomer($organisationId, $customerId, $validated['name'], $branchId);

        return redirect('/app/sharpfleet/admin/customers')
            ->with('success', 'Customer updated.');
    }

    public function archive(Request $request, int $customerId): RedirectResponse
    {
        $user = $this->getSharpFleetUser($request);

        if (!$user || !Roles::canManageFleet($user)) {
            abort(403);
        }

        $organisationId = (int) $user['organisation_id'];

        if (!$this->customerService->customersTableExists()) {
            return back()->withErrors([
                'customers' => "Customers can't be managed yet because the database is missing the sharpfleet.customers table.",
            ]);
        }

        $customer = $this->customerService->getCustomer($organisationId, $customerId);
        if (!$customer || (int) ($customer->is_active ?? 0) !== 1) {
            abort(404);
        }

        $this->customerService->archiveCustomer($organisationId, $customerId);

        return redirect('/app/sharpfleet/admin/customers')
            ->with('success', 'Customer archived.');
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $this->getSharpFleetUser($request);

        if (!$user || !Roles::canManageFleet($user)) {
            abort(403);
        }

        $organisationId = (int) $user['organisation_id'];

        if (!$this->customerService->customersTableExists()) {
            return back()->withErrors([
                'customers' => "Customers can't be managed yet because the database is missing the sharpfleet.customers table. Create it with: CREATE TABLE customers (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, organisation_id INT UNSIGNED NOT NULL, name VARCHAR(150) NOT NULL, is_active TINYINT(1) NOT NULL DEFAULT 1, created_at DATETIME NULL, updated_at DATETIME NULL, INDEX idx_org (organisation_id), INDEX idx_org_name (organisation_id, name));",
            ]);
        }

        // CSV import (optional)
        if ($request->hasFile('customers_csv')) {
            $request->validate([
                'customers_csv' => ['file', 'mimes:csv,txt', 'max:5120'],
            ]);

            $result = $this->customerService->importCustomersFromCsv(
                $organisationId,
                $request->file('customers_csv')
            );

            return redirect('/app/sharpfleet/admin/customers/create')
                ->with('success', "Imported {$result['imported']} customers. Skipped {$result['skipped']}.");
        }

        // Single add
        $branchesService = new BranchService();
        $branchesEnabled = $branchesService->branchesEnabled();
        $hasCustomerBranch = Schema::connection('sharpfleet')->hasColumn('customers', 'branch_id');
        $branches = $branchesEnabled
            ? $branchesService->getBranchesForUser($organisationId, (int) $user['id'])
            : collect();
        $branchIds = $branches->pluck('id')->map(fn ($id) => (int) $id)->values()->all();

        $rules = [
            'name' => ['required', 'string', 'max:150'],
        ];

        if ($branchesEnabled && $hasCustomerBranch && count($branchIds) > 1) {
            $rules['branch_id'] = ['required', 'integer', 'in:' . implode(',', $branchIds)];
        } elseif ($branchesEnabled && $hasCustomerBranch && count($branchIds) === 1) {
            $rules['branch_id'] = ['nullable', 'integer', 'in:' . implode(',', $branchIds)];
        }

        $validated = $request->validate($rules);

        $branchId = null;
        if ($branchesEnabled && $hasCustomerBranch && $request->filled('branch_id')) {
            $branchId = (int) $request->input('branch_id');
        } elseif ($branchesEnabled && $hasCustomerBranch && count($branchIds) === 1) {
            $branchId = (int) $branchIds[0];
        }

        $this->customerService->createCustomer($organisationId, $validated['name'], $branchId);

        return redirect('/app/sharpfleet/admin/customers/create')
            ->with('success', 'Customer saved.');
    }
}
