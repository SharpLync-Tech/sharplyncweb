<?php

namespace App\Mail\Support;

use App\Models\Support\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TicketCreatedCustomer extends Mailable
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
        return $this
            ->subject('Support Request Received — ' . $this->ticket->reference)
            ->view('emails.support.ticket-created-customer')
            ->with([
                'ticket'   => $this->ticket,
                'customer' => $this->customer,
            ]);
    }
}
