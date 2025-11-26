<?php

namespace App\Mail\Support;

use App\Models\Support\Ticket;
use App\Models\Support\TicketReply;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TicketReplied extends Mailable
{
    use Queueable, SerializesModels;

    public Ticket $ticket;
    public TicketReply $reply;
    public $customer;
    public bool $isInternal;

    /**
     * @param  Ticket       $ticket
     * @param  TicketReply  $reply
     * @param  mixed        $customer   The customer model instance
     * @param  bool         $isInternal True if email is to internal support
     */
    public function __construct(Ticket $ticket, TicketReply $reply, $customer, bool $isInternal = true)
    {
        $this->ticket = $ticket;
        $this->reply = $reply;
        $this->customer = $customer;
        $this->isInternal = $isInternal;
    }

    public function build()
    {
        $subject = $this->isInternal
            ? 'Customer replied to ticket: ' . $this->ticket->reference
            : 'Update on your support ticket: ' . $this->ticket->reference;

        return $this->subject($subject)
            ->view('emails.support.ticket-replied');
    }
}
