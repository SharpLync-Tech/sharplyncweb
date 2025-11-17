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

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $query = Customer::on('crm')
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

    public function show($id)
    {
        $c = Customer::on('crm')->findOrFail($id);

        $c->company_name = $c->business_name;
        $c->contact_name = $c->authority_contact;
        $c->email        = $c->accounts_email;
        $c->phone        = $c->mobile_number ?: $c->landline_number;
        $c->status       = $c->setup_completed ? 'active' : 'pending';

        return view('admin.customers.show', ['customer' => $c]);
    }

    public function edit($id)
    {
        $c = Customer::on('crm')->findOrFail($id);

        $c->company_name = $c->business_name;
        $c->contact_name = $c->authority_contact;
        $c->email        = $c->accounts_email;
        $c->phone        = $c->mobile_number ?: $c->landline_number;
        $c->status       = $c->setup_completed ? 'active' : 'pending';

        return view('admin.customers.edit', ['customer' => $c]);
    }

    public function update(Request $request, $id)
    {
        /** @var \App\Models\CRM\Customer $customer */
        $customer = Customer::on('crm')->findOrFail($id);

        $formUpdatedAt    = $request->input('updated_at');
        $currentUpdatedAt = optional($customer->updated_at)->format('Y-m-d H:i:s');

        if ($formUpdatedAt && $currentUpdatedAt && $formUpdatedAt !== $currentUpdatedAt) {
            return back()
                ->withInput()
                ->withErrors(['general' => 'This record was changed by someone else. Please reload and try again.']);
        }

        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'], // business_name
            'contact_name' => ['nullable', 'string', 'max:255'], // authority_contact
            'email'        => ['nullable', 'string', 'email', 'max:255'], // accounts_email
            'phone'        => ['nullable', 'string', 'max:50'], // mobile_number (simple mapping)
            'status'       => ['nullable', Rule::in(['active', 'pending'])], // -> setup_completed
            'notes'        => ['nullable', 'string', 'max:5000'],
        ]);

        $customer->business_name     = $validated['company_name'];
        $customer->authority_contact = $validated['contact_name'] ?? null;
        $customer->accounts_email    = $validated['email'] ?? null;
        $customer->mobile_number     = $validated['phone'] ?? null;

        if (array_key_exists('status', $validated) && $validated['status'] !== null) {
            $customer->setup_completed = $validated['status'] === 'active' ? 1 : 0;
        }

        $customer->notes = $validated['notes'] ?? $customer->notes;

        if (!$customer->isDirty()) {
            return redirect()
                ->route('admin.customers.show', $customer->id)
                ->with('status', 'No changes detected.');
        }

        $customer->save();

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
