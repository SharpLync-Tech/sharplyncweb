<?php

namespace App\Http\Controllers\SharpFleet\Admin;

use App\Http\Controllers\Controller;
use App\Services\SharpFleet\CustomerService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class CustomerController extends Controller
{
    protected CustomerService $customerService;

    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }

    public function index()
    {
        $user = session('sharpfleet.user');

        if (!$user || ($user['role'] ?? null) !== 'admin') {
            abort(403, 'Admin access only');
        }

        $organisationId = (int) $user['organisation_id'];

        $customersTableExists = $this->customerService->customersTableExists();
        $customers = $this->customerService->getCustomers($organisationId);

        return view('sharpfleet.admin.customers.index', [
            'customers'            => $customers,
            'customersTableExists' => $customersTableExists,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = session('sharpfleet.user');

        if (!$user || ($user['role'] ?? null) !== 'admin') {
            abort(403, 'Admin access only');
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

            return back()->with('success', "Imported {$result['imported']} customers. Skipped {$result['skipped']}.");
        }

        // Single add
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
        ]);

        $this->customerService->createCustomer($organisationId, $validated['name']);

        return back()->with('success', 'Customer saved.');
    }
}
