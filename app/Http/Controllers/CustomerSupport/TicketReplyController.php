<?php

namespace App\Http\Controllers\CustomerSupport;

use App\Http\Controllers\Controller;
use App\Models\Support\Ticket;
use App\Models\Support\TicketReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\Support\TicketReplied;

class TicketReplyController extends Controller
{
    /**
     * Store a customer reply to a ticket.
     */
    public function store(Request $request, Ticket $ticket)
    {
        $customer = auth()->guard('customer')->user();

        // Ensure ticket belongs to this customer
        abort_unless($ticket->customer_id === $customer->id, 404);

        $data = $request->validate([
            'message' => ['required', 'string'],
        ]);

        $reply = TicketReply::create([
            'ticket_id' => $ticket->id,
            'customer_id' => $customer->id,
            'message' => $data['message'],
            'is_internal' => false,
        ]);

        $ticket->last_reply_at = now();
        $ticket->save();

        // Notify internal support about the reply
        try {
            $supportEmail = config('mail.support_address', env('SUPPORT_EMAIL', 'info@sharplync.com.au'));

            if ($supportEmail) {
                Mail::to($supportEmail)->send(new TicketReplied($ticket, $reply, $customer, true));
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return redirect()
            ->route('customer.support.show', $ticket)
            ->with('success', 'Your reply has been added to this ticket.');
    }
}
