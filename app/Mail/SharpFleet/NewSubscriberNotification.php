<?php

namespace App\Mail\SharpFleet;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\\Mail\\Mailables\\Envelope;
use Illuminate\\Mail\\Mailables\\Address;
use Illuminate\Queue\SerializesModels;

class NewSubscriberNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $email;
    public $businessType;

    /**
     * Create a new message instance.
     */
    public function __construct($email, $businessType)
    {
        $this->email = $email;
        $this->businessType = $businessType;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), 'SharpFleet'),
            subject: 'New SharpFleet Subscriber',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.sharpfleet.new-subscriber',
            with: [
                'email' => $this->email,
                'businessType' => $this->businessType,
                'businessTypeLabel' => $this->businessType === 'sole_trader' ? 'Sole Trader' : 'Company',
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
