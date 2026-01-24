<?php

namespace App\Mail\SharpFleet;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\\Mail\\Mailables\\Envelope;
use Illuminate\\Mail\\Mailables\\Address;
use Illuminate\Queue\SerializesModels;

class TrialEndingSoon extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $name,
        public int $daysRemaining,
        public string $accountUrl
    ) {
    }

    public function envelope(): Envelope
    {
        $subject = $this->daysRemaining === 1
            ? 'Your SharpFleet trial ends tomorrow'
            : 'Your SharpFleet trial ends in ' . $this->daysRemaining . ' days';

        return new Envelope(
            from: new Address(config('mail.from.address'), 'SharpFleet'),
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.sharpfleet.trial-ending',
            with: [
                'name' => $this->name,
                'daysRemaining' => $this->daysRemaining,
                'accountUrl' => $this->accountUrl,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

