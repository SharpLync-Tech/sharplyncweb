<?php

namespace App\Mail\Support;

use App\Models\Support\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TicketPriorityUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public Ticket $ticket;
    public string $oldPriority;
    public string $newPriority;
    public $customer;

    public function __construct(Ticket $ticket, string $oldPriority, string $newPriority, $customer)
    {
        $this->ticket      = $ticket;
        $this->oldPriority = $oldPriority;
        $this->newPriority = $newPriority;
        $this->customer    = $customer;
    }

    public function build()
    {
        $subject = 'Your Support Ticket ' . $this->ticket->reference . ' priority has changed';

        return $this
            ->subject($subject)
            ->view('emails.support.ticket-priority-updated')
            ->with([
                'ticket'      => $this->ticket,
                'oldPriority' => $this->oldPriority,
                'newPriority' => $this->newPriority,
                'customer'    => $this->customer,
            ]);
    }
}
