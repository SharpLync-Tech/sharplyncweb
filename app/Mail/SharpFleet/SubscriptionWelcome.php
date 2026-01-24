<?php

namespace App\Mail\SharpFleet;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\\Mail\\Mailables\\Envelope;
use Illuminate\\Mail\\Mailables\\Address;
use Illuminate\Queue\SerializesModels;

class SubscriptionWelcome extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $firstName
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), 'SharpFleet'),
            subject: 'Welcome to SharpFleet',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.sharpfleet.subscription-welcome',
            with: [
                'firstName' => $this->firstName,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

