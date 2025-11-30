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

        // Debug — validation OK
        \Log::info('DEBUG: TicketReplyController@store — Validation passed', [
            'ticket_id' => $ticket->id,
            'customer_id' => $customer->id,
            'has_attachment' => $request->hasFile('attachment'),
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
         * FILE UPLOAD HANDLING + DEBUG
         * ============================
         */
        if ($request->hasFile('attachment')) {

            $file = $request->file('attachment');

            // Debug — file received
            \Log::info('DEBUG: Upload received', [
                'is_valid'  => $file->isValid(),
                'mime'      => $file->getClientMimeType(),
                'size_kb'   => round($file->getSize() / 1024, 2),
                'original'  => $file->getClientOriginalName(),
            ]);

            // Attempt to store file
            $path = $file->store("support_attachments/{$ticket->id}", 'local');

            // Debug — after store()
            \Log::info('DEBUG: Upload stored result', [
                'path' => $path,
                'storage_exists' => Storage::exists($path),
                'absolute' => storage_path('app/' . $path),
            ]);

            $reply->attachment_path          = $path;
            $reply->attachment_original_name = $file->getClientOriginalName();
            $reply->attachment_mime          = $file->getClientMimeType();
        } else {
            \Log::info('DEBUG: No attachment provided');
        }

        $reply->save();

        // Debug — reply saved
        \Log::info('DEBUG: Reply saved to DB', [
            'reply_id' => $reply->id,
            'ticket_id' => $ticket->id,
            'attachment_path' => $reply->attachment_path,
        ]);

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
            \Log::error('DEBUG: Email Error', ['error' => $e->getMessage()]);
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

        // Debug — download attempt
        \Log::info('DEBUG: Attachment download attempt', [
            'reply_id' => $reply->id,
            'ticket_id' => $reply->ticket_id,
            'attachment_path' => $reply->attachment_path,
            'exists' => Storage::exists($reply->attachment_path),
            'absolute' => storage_path('app/' . $reply->attachment_path),
            'customer_id' => $customer->id ?? null,
            'ticket_owner' => $reply->ticket->customer_id,
        ]);

        // security check:
        abort_unless($reply->ticket->customer_id === $customer->id, 404);

        if (!$reply->attachment_path || !Storage::exists($reply->attachment_path)) {
            abort(404, 'File not found');
        }

        return Storage::download(
            $reply->attachment_path,
            $reply->attachment_original_name,
            ['Content-Type' => $reply->attachment_mime]
        );
    }
}
