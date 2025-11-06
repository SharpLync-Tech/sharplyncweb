<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Customer;

class CustomerController extends Controller
{
    /**
     * Show the onboarding form.
     */
    public function create()
    {
        return view('customers.customer-onboard');
    }

    /**
     * Handle form submission and create customer + SSPIN.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name'   => 'required|string|max:100',
            'last_name'    => 'required|string|max:100',
            'company_name' => 'nullable|string|max:150',
            'email'        => 'required|email|unique:customers',
            'phone'        => 'nullable|string|max:50',
            'address'      => 'nullable|string|max:255',
            'city'         => 'nullable|string|max:100',
            'state'        => 'nullable|string|max:100',
            'postcode'     => 'nullable|string|max:20',
        ]);

        try {
            DB::beginTransaction();

            // 1️⃣ Create the customer record
            $customer = Customer::create($validated);

            // 2️⃣ Generate random SharpLync Support PIN (SSPIN)
            $sspin = random_int(100000, 999999);

            // 3️⃣ Save SSPIN linked to customer
            DB::table('support_pins')->insert([
                'customer_id' => $customer->id,
                'sspin'       => $sspin,
                'is_active'   => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            DB::commit();

            // 4️⃣ Display success message
            return redirect()
                ->route('customers.create')
                ->with('success', "Your SharpLync Support PIN (SSPIN) is {$sspin}. Keep it safe — it helps us verify you during remote support.");

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Error creating record: ' . $e->getMessage());
        }
    }
}