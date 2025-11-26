<?php

namespace App\Mail\Support;

use App\Models\Support\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TicketCreated extends Mailable
{
    use Queueable, SerializesModels;

    public Ticket $ticket;
    public $customer;
    public bool $isInternal;

    /**
     * @param  Ticket  $ticket
     * @param  mixed   $customer  The customer model instance
     * @param  bool    $isInternal  True if email is to internal support
     */
    public function __construct(Ticket $ticket, $customer, bool $isInternal = true)
    {
        $this->ticket = $ticket;
        $this->customer = $customer;
        $this->isInternal = $isInternal;
    }

    public function build()
    {
        $subject = $this->isInternal
            ? 'New Support Ticket: ' . $this->ticket->reference
            : 'We received your support request: ' . $this->ticket->reference;

        return $this->subject($subject)
            ->view('emails.support.ticket-created');
    }
}
