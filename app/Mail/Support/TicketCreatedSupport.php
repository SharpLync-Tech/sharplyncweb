<?php

namespace App\Mail\Support;

use App\Models\Support\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TicketCreatedSupport extends Mailable
{
    use Queueable, SerializesModels;

    public Ticket $ticket;
    public $customer;

    public function __construct(Ticket $ticket, $customer)
    {
        $this->ticket   = $ticket;
        $this->customer = $customer;
    }

    public function build()
    {
        $priority = ucfirst($this->ticket->priority);
        $customerName = $this->customer->name ?? ($this->customer->full_name ?? ('Customer #' . $this->customer->id));
        $prefix = $this->ticket->priority === 'high' ? 'URGENT — ' : '';
        $subject = $prefix . 'New Support Ticket ' . $this->ticket->reference . ' (' . $priority . ') — ' . $customerName;

        return $this
            ->subject($subject)
            ->view('emails.support.ticket-created-internal')
            ->with([
                'ticket'   => $this->ticket,
                'customer' => $this->customer,
            ]);
    }
}
