<?php

namespace App\Http\Controllers\CustomerSupport;

use App\Http\Controllers\Controller;
use App\Mail\Support\TicketCreatedCustomer;
use App\Mail\Support\TicketCreatedSupport;
use App\Mail\Support\TicketPriorityUpdated;
use App\Mail\Support\TicketStatusUpdated;
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
            'customer_id'   => $customer->id,
            'reference'     => Ticket::generateReference(),
            'subject'       => $data['subject'],
            'priority'      => $data['priority'],
            'message'       => $data['message'],
            'status'        => Ticket::STATUS_OPEN,
            'created_via'   => 'portal',
            'last_reply_at' => now(),
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
            report($e); // ticket created anyway
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

        // Ensure ticket belongs to this customer
        abort_unless($ticket->customer_id === $customer->id, 404);

        $ticket->load(['replies' => fn ($q) => $q->orderBy('created_at', 'asc')]);

        return view('customers.support.show', [
            'customer' => $customer,
            'ticket'   => $ticket,
        ]);
    }

    /**
     * Update ticket STATUS (Open, Pending, etc.)
     */
    public function updateStatus(Request $request, Ticket $ticket)
    {
        $customer = auth()->guard('customer')->user();
        abort_unless($ticket->customer_id === $customer->id, 404);

        $request->validate([
            'status' => ['required', 'in:open,pending,waiting_customer,waiting_third_party,waiting_stock,resolved,closed']
        ]);

        $oldStatus = $ticket->status;
        $ticket->status = $request->status;
        $ticket->save();

        // Notify internal support always
        $supportAddress = config('mail.support_address', env('SUPPORT_EMAIL', config('mail.from.address')));

        try {
            Mail::to($supportAddress)->send(new TicketStatusUpdated($ticket, $customer, $oldStatus));
        } catch (\Throwable $e) {
            report($e);
        }

        // Customer SHOULD get email? In your rules:
        // A) Customer should NOT receive email for their own replies, but status updates are different.
        // You decide:
        // For now **YES** customer gets notified on status changes.
        try {
            Mail::to($customer->email)->send(new TicketStatusUpdated($ticket, $customer, $oldStatus));
        } catch (\Throwable $e) {
            report($e);
        }

        return back()->with('success', 'Ticket status has been updated.');
    }

    /**
     * Update ticket PRIORITY (Low, Medium, High)
     */
    public function updatePriority(Request $request, Ticket $ticket)
    {
        $customer = auth()->guard('customer')->user();
        abort_unless($ticket->customer_id === $customer->id, 404);

        $request->validate([
            'priority' => ['required', 'in:low,medium,high']
        ]);

        $oldPriority = $ticket->priority;
        $ticket->priority = $request->priority;
        $ticket->save();

        // Notify internal support
        $supportAddress = config('mail.support_address', env('SUPPORT_EMAIL', config('mail.from.address')));

        try {
            Mail::to($supportAddress)->send(new TicketPriorityUpdated($ticket, $customer, $oldPriority));
        } catch (\Throwable $e) {
            report($e);
        }

        // Notify customer
        try {
            Mail::to($customer->email)->send(new TicketPriorityUpdated($ticket, $customer, $oldPriority));
        } catch (\Throwable $e) {
            report($e);
        }

        return back()->with('success', 'Ticket priority has been updated.');
    }
}
