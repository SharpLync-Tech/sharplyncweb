<?php

namespace App\Mail\Support;

use App\Models\Support\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TicketStatusUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public Ticket $ticket;
    public string $oldStatus;
    public string $newStatus;
    public $customer;

    public function __construct(Ticket $ticket, string $oldStatus, string $newStatus, $customer)
    {
        $this->ticket    = $ticket;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
        $this->customer  = $customer;
    }

    public function build()
    {
        $subject = 'Your Support Ticket ' . $this->ticket->reference . ' status has changed';

        return $this
            ->subject($subject)
            ->view('emails.support.ticket-status-updated')
            ->with([
                'ticket'    => $this->ticket,
                'oldStatus' => $this->oldStatus,
                'newStatus' => $this->newStatus,
                'customer'  => $this->customer,
            ]);
    }
}
