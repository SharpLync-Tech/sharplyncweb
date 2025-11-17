<?php
/**
 * SharpLync Admin: CustomerController
 * Version: 1.1 (Read + Edit + Concurrency Guard + Audit Log)
 * Last updated: 17 Nov 2025 by Max (ChatGPT)
 *
 * Notes:
 * - Uses existing App\Models\CRM\Customer model (no schema changes).
 * - Safe field whitelist for updates to avoid accidental overwrites.
 * - Optimistic concurrency: compares form updated_at vs DB updated_at.
 * - Basic audit log to storage/logs/laravel.log (actor, id, changed fields).
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
     * List customers with simple search + pagination.
     */
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $customers = Customer::query()
            ->when($q !== '', function ($query) use ($q) {
                $like = "%{$q}%";
                $query->where(function ($q2) use ($like) {
                    $q2->where('company_name', 'like', $like)
                        ->orWhere('contact_name', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('phone', 'like', $like);
                });
            })
            ->orderBy('company_name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.customers.index', [
            'customers' => $customers,
            'q'         => $q,
        ]);
    }

    /**
     * Read-only profile view.
     */
    public function show($id)
    {
        $customer = Customer::findOrFail($id);

        return view('admin.customers.show', [
            'customer' => $customer,
        ]);
    }

    /**
     * Edit form.
     */
    public function edit($id)
    {
        $customer = Customer::findOrFail($id);

        return view('admin.customers.edit', [
            'customer' => $customer,
        ]);
    }

    /**
     * Update handler with validation and optimistic concurrency.
     */
    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        // ----- Optimistic concurrency guard -----
        $formUpdatedAt = $request->input('updated_at'); // ISO or DB format
        $currentUpdatedAt = optional($customer->updated_at)->format('Y-m-d H:i:s');

        if ($formUpdatedAt && $currentUpdatedAt && $formUpdatedAt !== $currentUpdatedAt) {
            return back()
                ->withInput()
                ->withErrors(['general' => 'This record was changed by someone else. Please reload and try again.']);
        }

        // ----- Validation -----
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'email'        => ['nullable', 'string', 'email', 'max:255'],
            'phone'        => ['nullable', 'string', 'max:50'],
            'status'       => ['nullable', Rule::in(['active', 'inactive', 'prospect'])],
            'notes'        => ['nullable', 'string', 'max:5000'],
        ]);

        // ----- Safe whitelist of fields we allow editing -----
        $allowed = [
            'company_name',
            'contact_name',
            'email',
            'phone',
            'status',
            'notes',
        ];

        $before = $customer->only($allowed);

        $customer->fill(collect($validated)->only($allowed)->toArray());

        // If nothing changed, short-circuit politely
        if (!$customer->isDirty()) {
            return redirect()
                ->route('admin.customers.show', $customer->id)
                ->with('status', 'No changes detected.');
        }

        $customer->save();

        // ----- Audit log (basic) -----
        $after   = $customer->only($allowed);
        $changes = [];
        foreach ($allowed as $key) {
            $prev = $before[$key] ?? null;
            $next = $after[$key] ?? null;
            if ($prev !== $next) {
                $changes[$key] = ['from' => $prev, 'to' => $next];
            }
        }

        $actor = [
            'displayName' => data_get(session('admin_user'), 'displayName'),
            'email'       => data_get(session('admin_user'), 'userPrincipalName') ?? data_get(session('admin_user'), 'mail'),
        ];

        Log::info('Admin updated customer', [
            'actor'     => $actor,
            'customer'  => ['id' => $customer->id, 'company_name' => $customer->company_name],
            'changes'   => $changes,
            'timestamp' => now()->toDateTimeString(),
        ]);

        return redirect()
            ->route('admin.customers.show', $customer->id)
            ->with('status', 'Customer updated successfully.');
    }
}