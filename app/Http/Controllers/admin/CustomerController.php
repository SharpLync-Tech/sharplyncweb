<?php
/**
 * SharpLync Admin: CustomerController
 * Version: 1.2 (Force CRM connection + Field mapping layer)
 * Last updated: 17 Nov 2025 by Max (ChatGPT)
 *
 * - Does NOT modify your App\Models\CRM\Customer model.
 * - Uses ->on('sharplync_crm') to query the CRM DB.
 * - Maps real CRM fields to view aliases so existing blades work.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Models\CRM\Customer;

class CustomerController extends Controller
{
    /**
     * List customers with search + pagination (CRM connection).
     * Maps columns to aliases expected by the index blade.
     */
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        // Select with aliases so the blade can use company_name/contact_name/email/phone/status
        $query = Customer::on('sharplync_crm')
            ->select([
                'id',
                'business_name as company_name',
                'authority_contact as contact_name',
                'accounts_email as email',
                // prefer mobile, fall back to landline in the view
                'mobile_number',
                'landline_number',
                'notes',
                'setup_completed',
            ])
            ->when($q !== '', function ($qBuilder) use ($q) {
                $like = "%{$q}%";
                $qBuilder->where(function ($w) use ($like) {
                    $w->where('business_name', 'like', $like)
                      ->orWhere('authority_contact', 'like', $like)
                      ->orWhere('accounts_email', 'like', $like)
                      ->orWhere('mobile_number', 'like', $like)
                      ->orWhere('landline_number', 'like', $like);
                });
            })
            ->orderBy('business_name');

        $customers = $query->paginate(20)->withQueryString();

        // Normalize phone + status for the table without changing blades
        $customers->getCollection()->transform(function ($row) {
            $row->phone  = $row->mobile_number ?: $row->landline_number;
            // You don't have a "status" column; derive a simple label
            $row->status = $row->setup_completed ? 'active' : 'pending';
            return $row;
        });

        return view('admin.customers.index', [
            'customers' => $customers,
            'q'         => $q,
        ]);
    }

    /**
     * Read-only profile view (CRM connection).
     * Adds alias properties so the show blade works unchanged.
     */
    public function show($id)
    {
        $c = Customer::on('sharplync_crm')->findOrFail($id);

        // Add aliases used by the blade
        $c->company_name = $c->business_name;
        $c->contact_name = $c->authority_contact;
        $c->email        = $c->accounts_email;
        $c->phone        = $c->mobile_number ?: $c->landline_number;
        $c->status       = $c->setup_completed ? 'active' : 'pending';

        return view('admin.customers.show', ['customer' => $c]);
    }

    /**
     * Edit form (CRM connection).
     * Adds alias properties so the edit blade fields populate correctly.
     */
    public function edit($id)
    {
        $c = Customer::on('sharplync_crm')->findOrFail($id);

        // Aliases expected by the edit blade
        $c->company_name = $c->business_name;
        $c->contact_name = $c->authority_contact;
        $c->email        = $c->accounts_email;
        $c->phone        = $c->mobile_number ?: $c->landline_number;
        $c->status       = $c->setup_completed ? 'active' : 'pending';

        return view('admin.customers.edit', ['customer' => $c]);
    }

    /**
     * Update with validation + optimistic concurrency (CRM connection).
     * Maps form fields back to your real CRM columns.
     */
    public function update(Request $request, $id)
    {
        // Fetch on CRM connection
        /** @var \App\Models\CRM\Customer $customer */
        $customer = Customer::on('sharplync_crm')->findOrFail($id);

        // ----- Optimistic concurrency guard -----
        $formUpdatedAt    = $request->input('updated_at'); // Y-m-d H:i:s from the form
        $currentUpdatedAt = optional($customer->updated_at)->format('Y-m-d H:i:s');

        if ($formUpdatedAt && $currentUpdatedAt && $formUpdatedAt !== $currentUpdatedAt) {
            return back()
                ->withInput()
                ->withErrors(['general' => 'This record was changed by someone else. Please reload and try again.']);
        }

        // ----- Validate fields coming from your edit.blade (aliases) -----
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'], // maps -> business_name
            'contact_name' => ['nullable', 'string', 'max:255'], // -> authority_contact
            'email'        => ['nullable', 'string', 'email', 'max:255'], // -> accounts_email
            'phone'        => ['nullable', 'string', 'max:50'], // -> mobile_number or landline_number
            'status'       => ['nullable', Rule::in(['active', 'pending'])],
            'notes'        => ['nullable', 'string', 'max:5000'],
        ]);

        // ----- Map aliases to real columns -----
        $customer->business_name     = $validated['company_name'];
        $customer->authority_contact = $validated['contact_name'] ?? null;
        $customer->accounts_email    = $validated['email'] ?? null;

        // Simple phone mapping: prefer mobile_number; if contains non-mobile style, you can split later.
        $customer->mobile_number     = $validated['phone'] ?? null;
        // keep existing landline unless you add a separate input later

        // Derive setup_completed from status if provided
        if (array_key_exists('status', $validated) && $validated['status'] !== null) {
            $customer->setup_completed = $validated['status'] === 'active' ? 1 : 0;
        }

        $customer->notes = $validated['notes'] ?? $customer->notes;

        // If nothing changed, short-circuit
        if (!$customer->isDirty()) {
            return redirect()
                ->route('admin.customers.show', $customer->id)
                ->with('status', 'No changes detected.');
        }

        $before = $customer->getOriginal();
        $customer->save();

        // ----- Audit log -----
        $actor = [
            'displayName' => data_get(session('admin_user'), 'displayName'),
            'email'       => data_get(session('admin_user'), 'userPrincipalName') ?? data_get(session('admin_user'), 'mail'),
        ];
        Log::info('Admin updated CRM customer', [
            'actor'     => $actor,
            'customer'  => ['id' => $customer->id, 'business_name' => $customer->business_name],
            'timestamp' => now()->toDateTimeString(),
        ]);

        return redirect()
            ->route('admin.customers.show', $customer->id)
            ->with('status', 'Customer updated successfully.');
    }
}
