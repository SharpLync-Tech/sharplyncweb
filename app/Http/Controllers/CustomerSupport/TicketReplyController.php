<?php

namespace App\Http\Controllers\CustomerSupport;

use App\Http\Controllers\Controller;
use App\Mail\Support\TicketReplyNotification;
use App\Models\Support\Ticket;
use App\Models\Support\TicketReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class TicketReplyController extends Controller
{
    /**
     * Store a reply from the logged-in customer.
     */
    public function store(Request $request, Ticket $ticket)
    {
        $customer = auth()->guard('customer')->user();

        // Make sure this ticket belongs to the logged-in customer
        abort_unless($ticket->customer_id === $customer->id, 404);

        $data = $request->validate([
            'message' => ['required', 'string'],
        ]);

        $reply = TicketReply::create([
            'ticket_id'   => $ticket->id,
            'user_type'   => 'customer',
            'user_id'     => $customer->id,
            'message'     => $data['message'],
            'is_internal' => false,
        ]);


        // Update "last_reply_at" for list sorting
        $ticket->last_reply_at = now();
        $ticket->save();

        // Notify internal support team only (customer does not get email for their own reply)
        try {
            $supportAddress = config('mail.support_address', env('SUPPORT_EMAIL', config('mail.from.address')));
            if ($supportAddress) {
                Mail::to($supportAddress)
                    ->send(new TicketReplyNotification($ticket, $reply, $customer));
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return redirect()
            ->route('customer.support.tickets.show', $ticket)
            ->with('success', 'Your reply has been added to this ticket.');
    }
}
