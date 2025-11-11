<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CRM\Customer;
use App\Models\CRM\OnboardingSession;

class CustomerController extends Controller
{
    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|unique:customers,email',
        ]);

        $customer = Customer::create($validated);

        OnboardingSession::create([
            'customer_id' => $customer->id,
            'session_token' => bin2hex(random_bytes(16)),
            'status' => 'active',
        ]);

        return redirect()->route('customers.create')->with('success', 'Customer onboarded successfully!');
    }
}