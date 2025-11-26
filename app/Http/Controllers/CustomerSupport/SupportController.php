<?php

namespace App\Http\Controllers\CustomerSupport;

use App\Http\Controllers\Controller;
use App\Models\Support\Ticket;

class SupportController extends Controller
{
    /**
     * Show list of tickets for the logged-in customer.
     */
    public function index()
    {
        $customer = auth()->guard('customer')->user();

        $tickets = Ticket::forCustomer($customer->id)
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('customers.support.index', [
            'customer' => $customer,
            'tickets' => $tickets,
        ]);
    }
}
