<?php

namespace App\Http\Controllers\CustomerSupport;

use App\Http\Controllers\Controller;
use App\Mail\Support\TicketCreatedCustomer;
use App\Mail\Support\TicketCreatedSupport;
use App\Models\Support\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

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
     * Store a new support ticket.
     */
    public function store(Request $request)
    {
        $customer = auth()->guard('customer')->user();

        $data = $request->validate([
            'subject'  => ['required', 'string', 'max:255'],
            'priority' => ['required', 'in:low,medium,high'],
            'message'  => ['required', 'string'],
        ]);

        $ticket = Ticket::create([
            'customer_id'  => $customer->id,
            'reference'    => Ticket::generateReference(),
            'subject'      => $data['subject'],
            'priority'     => $data['priority'],
            'message'      => $data['message'],
            'status'       => Ticket::STATUS_OPEN,
            'created_via'  => 'portal',
            'last_reply_at'=> now(),
        ]);

        // --- Email notifications ---
        try {
            // Customer confirmation
            if ($customer && $customer->email) {
                Mail::to($customer->email)
                    ->send(new TicketCreatedCustomer($ticket, $customer));
            }

            // Internal notification
            $supportAddress = config('mail.support_address', env('SUPPORT_EMAIL', config('mail.from.address')));
            if ($supportAddress) {
                Mail::to($supportAddress)
                    ->send(new TicketCreatedSupport($ticket, $customer));
            }
        } catch (\Throwable $e) {
            // Ticket is still created – we don't want email failures to break the UX
            report($e);
        }

        return redirect()
            ->route('customer.support.tickets.show', $ticket)
            ->with('success', 'Your support request has been submitted. Reference: ' . $ticket->reference);
    }

    /**
     * Show a single ticket with replies.
     */
    public function show(Ticket $ticket)
    {
        $customer = auth()->guard('customer')->user();

        // Security: ensure ticket belongs to this customer
        abort_unless($ticket->customer_id === $customer->id, 404);

        $ticket->load(['replies' => function ($query) {
            $query->orderBy('created_at', 'asc');
        }]);

        return view('customers.support.show', [
            'customer' => $customer,
            'ticket'   => $ticket,
        ]);
    }
}
