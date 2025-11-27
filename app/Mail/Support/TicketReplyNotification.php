<?php

namespace App\Mail\Support;

use App\Models\Support\Ticket;
use App\Models\Support\TicketReply;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TicketReplyNotification extends Mailable
{
    use Queueable, SerializesModels;

    public Ticket $ticket;
    public TicketReply $reply;
    public $customer;

    public function __construct(Ticket $ticket, TicketReply $reply, $customer)
    {
        $this->ticket   = $ticket;
        $this->reply    = $reply;
        $this->customer = $customer;
    }

    public function build()
    {
        $customerName = $this->customer->name ?? ($this->customer->full_name ?? ('Customer #' . $this->customer->id));
        $subject = 'Customer Reply — Ticket ' . $this->ticket->reference . ' — ' . $customerName;

        return $this
            ->subject($subject)
            ->view('emails.support.ticket-reply-internal')
            ->with([
                'ticket'   => $this->ticket,
                'reply'    => $this->reply,
                'customer' => $this->customer,
            ]);
    }
}
