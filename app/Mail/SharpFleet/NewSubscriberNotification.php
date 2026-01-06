<?php

namespace App\Mail\SharpFleet;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewSubscriberNotification extends Mailable
{
    use Queueable, SerializesModels;

        public string $email;
        public ?string $businessType;

    /**
     * Create a new message instance.
     */
        public function __construct(string $email, ?string $businessType)
    {
        $this->email = $email;
            $this->businessType = $businessType !== null ? strtolower(trim($businessType)) : null;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
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
                    'businessTypeLabel' => $this->businessType === 'sole_trader' ? 'Sole Trader' : ($this->businessType === 'company' ? 'Company' : 'Not selected (setup wizard)'),
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