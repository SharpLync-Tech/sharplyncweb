<?php

namespace App\Http\Controllers\CustomerSupport;

use App\Http\Controllers\Controller;
use App\Mail\Support\TicketReplyNotification;
use App\Models\Support\Ticket;
use App\Models\Support\TicketReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

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

        // Validate text + optional file
        $data = $request->validate([
            'message'    => ['required', 'string'],
            'attachment' => ['nullable', 'file', 'max:5120'], // 5MB
        ]);

        // Create reply object
        $reply = new TicketReply();
        $reply->ticket_id   = $ticket->id;
        $reply->user_type   = 'customer';
        $reply->user_id     = $customer->id;
        $reply->message     = $data['message'];
        $reply->is_internal = false;

        /**
         * ============================
         * FILE UPLOAD HANDLING
         * ============================
         */
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');

            // Private storage path: storage/app/support_attachments/{ticket_id}/
            $path = $file->store("support_attachments/{$ticket->id}", 'local');

            $reply->attachment_path          = $path;
            $reply->attachment_original_name = $file->getClientOriginalName();
            $reply->attachment_mime          = $file->getClientMimeType();
        }

        $reply->save();


        /**
         * ===================================
         * Update "last_reply_at" for sorting
         * ===================================
         */
        $ticket->last_reply_at = now();
        $ticket->save();


        /**
         * =================================================
         * Notify internal support team (customer never gets email)
         * =================================================
         */
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



    /**
     * Download attachment (PRIVATE)
     */
    public function download(TicketReply $reply)
    {
        $customer = auth()->guard('customer')->user();

        // Ticket must belong to this customer
        if (!$reply->ticket || $reply->ticket->customer_id !== $customer->id) {
            abort(404);
        }

        if (!$reply->attachment_path) {
            abort(404);
        }

        return Storage::disk('local')->download(
            $reply->attachment_path,
            $reply->attachment_original_name ?? 'attachment'
        );
    }
}
