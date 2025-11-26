<?php

namespace App\Http\Controllers\CustomerSupport;

use App\Http\Controllers\Controller;
use App\Models\Support\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\Support\TicketCreated;

class TicketController extends Controller
{
    /**
     * Show create ticket form.
     */
    public function create()
    {
        $customer = auth()->guard('customer')->user();

        return view('customers.support.create', [
            'customer' => $customer,
        ]);
    }

    /**
     * Store a newly created ticket.
     */
    public function store(Request $request)
    {
        $customer = auth()->guard('customer')->user();

        $data = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'message' => ['required', 'string'],
        ]);

        $ticket = Ticket::create([
            'customer_id' => $customer->id,
            'reference' => Ticket::generateReference(),
            'subject' => $data['subject'],
            'message' => $data['message'],
            'priority' => $data['priority'],
            'status' => 'open',
            'created_via' => 'portal',
        ]);

        // Email notification to internal support + customer (optional)
        try {
            $supportEmail = config('mail.support_address', env('SUPPORT_EMAIL', 'info@sharplync.com.au'));

            if ($supportEmail) {
                Mail::to($supportEmail)->send(new TicketCreated($ticket, $customer, true));
            }

            // Send confirmation to customer
            if ($customer->email) {
                Mail::to($customer->email)->send(new TicketCreated($ticket, $customer, false));
            }
        } catch (\Throwable $e) {
            // Fail silently for now; ticket is still created.
            report($e);
        }

        return redirect()
            ->route('customer.support.tickets.show', $ticket)
            ->with('success', 'Your support request has been submitted. Reference: ' . $ticket->reference);
    }

    /**
     * Show a single ticket + replies.
     */
    public function show(Ticket $ticket)
    {
        $customer = auth()->guard('customer')->user();

        // Security: ensure ticket belongs to this customer
        abort_unless($ticket->customer_id === $customer->id, 404);

        $ticket->load('replies');

        return view('customers.support.show', [
            'customer' => $customer,
            'ticket' => $ticket,
        ]);
    }
}
