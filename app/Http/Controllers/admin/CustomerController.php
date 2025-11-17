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
     * Customers list + search (CRM: customer_profiles)
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
            ]);

        if ($q !== '') {
            $like = "%{$q}%";
            $query->where(function ($w) use ($like) {
                $w->where('business_name', 'like', $like)
                  ->orWhere('authority_contact', 'like', $like)
                  ->orWhere('accounts_email', 'like', $like)
                  ->orWhere('mobile_number', 'like', $like)
                  ->orWhere('landline_number', 'like', $like);
            });
        }

        $query->orderBy('business_name');

        $customers = $query->paginate(20)->withQueryString();

        // Normalize derived fields expected by the Blade
        $customers->getCollection()->transform(function ($row) {
            $row->phone  = $row->mobile_number ?: $row->landline_number;
            // coalesce + strict cast so only 1 is active; NULL/0 => pending
            $row->status = ((int)($row->setup_completed ?? 0) === 1) ? 'active' : 'pending';
            return $row;
        });

        return view('admin.customers.index', [
            'customers' => $customers,
            'q'         => $q,
        ]);
    }

    /**
     * Read-only profile view (aliases match Blade keys)
     */
    public function show(int $id)
    {
        $customer = DB::connection('crm')
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
            ->where('id', $id)
            ->first();

        if (!$customer) {
            abort(404);
        }

        $customer->phone  = $customer->mobile_number ?: $customer->landline_number;
        $customer->status = ((int)($customer->setup_completed ?? 0) === 1) ? 'active' : 'pending';

        return view('admin.customers.show', compact('customer'));
    }

    /**
     * Edit form (pre-populate with the same aliases the Blade expects)
     */
    public function edit(int $id)
    {
        $customer = DB::connection('crm')
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
            ])
            ->where('id', $id)
            ->first();

        if (!$customer) {
            abort(404);
        }

        return view('admin.customers.edit', compact('customer'));
    }

    /**
     * Update handler (minimal example; extend as needed)
     */
    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'company_name'  => 'required|string|max:255',
            'contact_name'  => 'nullable|string|max:255',
            'email'         => 'nullable|email|max:255',
            'mobile_number' => 'nullable|string|max:50',
            'landline_number' => 'nullable|string|max:50',
            'notes'         => 'nullable|string',
            'setup_completed' => 'nullable|boolean',
        ]);

        DB::connection('crm')
            ->table('customer_profiles')
            ->where('id', $id)
            ->update([
                'business_name'   => $data['company_name'],
                'authority_contact' => $data['contact_name'] ?? null,
                'accounts_email'  => $data['email'] ?? null,
                'mobile_number'   => $data['mobile_number'] ?? null,
                'landline_number' => $data['landline_number'] ?? null,
                'notes'           => $data['notes'] ?? null,
                'setup_completed' => (int)($data['setup_completed'] ?? 0),
                'updated_at'      => now(),
            ]);

        return redirect()
            ->route('admin.customers.show', $id)
            ->with('status', 'Customer updated.');
    }

        public function sendReset(Request $request, int $id)
    {
        // pull the profile from CRM
        $customer = DB::connection('crm')->table('customer_profiles')->where('id', $id)->first();

        if (!$customer) {
            return redirect()->route('admin.customers.index')->withErrors(['general' => 'Customer not found.']);
        }

        // Decide which email to use (accounts email is what you’ve been storing)
        $email = $customer->accounts_email ?? null;

        if (!$email) {
            return back()->withErrors(['email' => 'No email on file for this customer.']);
        }

        // Uses the "customers" password broker (see config step below if you don’t have it yet)
        $status = Password::broker('customers')->sendResetLink(['email' => $email]);

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', "Password reset link sent to {$email}.");
        }

        // Bubble up the framework message (e.g. "We can't find a user with that email address.")
        return back()->withErrors(['email' => __($status)]);
    }
}