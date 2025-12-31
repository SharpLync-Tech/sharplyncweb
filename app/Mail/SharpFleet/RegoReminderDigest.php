<?php

namespace App\Mail\SharpFleet;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RegoReminderDigest extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $organisationName,
        public array $overdue,
        public array $dueSoon
    ) {
    }

    public function envelope(): Envelope
    {
        $org = trim($this->organisationName);
        $subjectOrg = $org !== '' ? (" - {$org}") : '';

        return new Envelope(
            subject: 'SharpFleet registration reminders' . $subjectOrg,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.sharpfleet.rego-reminder',
            with: [
                'organisationName' => $this->organisationName,
                'overdue' => $this->overdue,
                'dueSoon' => $this->dueSoon,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
