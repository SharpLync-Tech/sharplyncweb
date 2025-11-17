<?php
/**
 * SharpLync Admin: CustomerController
 * Version: 1.2.1 (Use 'crm' connection + Field mapping layer)
 * Last updated: 17 Nov 2025 by Max (ChatGPT)
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Models\CRM\Customer;
use Illuminate\Support\Facades\DB;


class CustomerController extends Controller
{
    /**
     * Customer index (search + paginate) using CRM connection + customer_profiles table.
     */
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $query = DB::connection('crm')
            ->table('customer_profiles')
            ->select([
                'id',
                'business_name as company_name',
                'authority_contact as contact_name',
                'accounts_email as email',
                'mobile_number',
                'landline_number',
                'notes',
                'setup_completed',
                'updated_at',
            ])
            ->when($q !== '', function ($qry) use ($q) {
                $like = '%' . $q . '%';
                $qry->where(function ($w) use ($like) {
                    $w->where('business_name', 'like', $like)
                      ->orWhere('authority_contact', 'like', $like)
                      ->orWhere('accounts_email', 'like', $like)
                      ->orWhere('mobile_number', 'like', $like)
                      ->orWhere('landline_number', 'like', $like);
                });
            })
            ->orderBy('business_name');

        $customers = $query->paginate(20)->withQueryString();

        // post-process: unify phone/status fields for the view
        $customers->getCollection()->transform(function ($row) {
            $row->phone  = $row->mobile_number ?: $row->landline_number;
            $row->status = $row->setup_completed ? 'active' : 'pending';
            return $row;
        });

        return view('admin.customers.index', [
            'customers' => $customers,
            'q'         => $q,
        ]);
    }

    /**
     * Read-only profile view for now (same table/conn).
     */
    public function show(int $id)
    {
        $customer = DB::connection('crm')
            ->table('customer_profiles')
            ->where('id', $id)
            ->first();

        abort_if(!$customer, 404);

        return view('admin.customers.show', compact('customer'));
    }

    /**
     * Edit form (loads one record).
     */
    public function edit(int $id)
    {
        $customer = DB::connection('crm')
            ->table('customer_profiles')
            ->where('id', $id)
            ->first();

        abort_if(!$customer, 404);

        return view('admin.customers.edit', compact('customer'));
    }

    /**
     * Update handler (minimal safe fields to start).
     */
    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'business_name'   => ['required', 'string', 'max:255'],
            'authority_contact' => ['nullable', 'string', 'max:255'],
            'accounts_email'  => ['nullable', 'email', 'max:255'],
            'mobile_number'   => ['nullable', 'string', 'max:50'],
            'landline_number' => ['nullable', 'string', 'max:50'],
            'notes'           => ['nullable', 'string'],
            'setup_completed' => ['nullable', 'boolean'],
        ]);

        $affected = DB::connection('crm')
            ->table('customer_profiles')
            ->where('id', $id)
            ->update([
                'business_name'    => $validated['business_name'],
                'authority_contact'=> $validated['authority_contact'] ?? null,
                'accounts_email'   => $validated['accounts_email'] ?? null,
                'mobile_number'    => $validated['mobile_number'] ?? null,
                'landline_number'  => $validated['landline_number'] ?? null,
                'notes'            => $validated['notes'] ?? null,
                'setup_completed'  => (int) ($validated['setup_completed'] ?? 0),
                'updated_at'       => now(),
            ]);

        return redirect()
            ->route('admin.customers.show', $id)
            ->with('status', $affected ? 'Customer updated.' : 'No changes detected.');
    }
}