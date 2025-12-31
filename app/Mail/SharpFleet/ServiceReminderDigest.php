<?php

namespace App\Mail\SharpFleet;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ServiceReminderDigest extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $organisationName,
        public array $serviceDateOverdue,
        public array $serviceDateDueSoon,
        public array $serviceReadingOverdue,
        public array $serviceReadingDueSoon
    ) {
    }

    public function envelope(): Envelope
    {
        $org = trim($this->organisationName);
        $subjectOrg = $org !== '' ? (" - {$org}") : '';

        return new Envelope(
            subject: 'SharpFleet servicing reminders' . $subjectOrg,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.sharpfleet.service-reminder',
            with: [
                'organisationName' => $this->organisationName,
                'serviceDateOverdue' => $this->serviceDateOverdue,
                'serviceDateDueSoon' => $this->serviceDateDueSoon,
                'serviceReadingOverdue' => $this->serviceReadingOverdue,
                'serviceReadingDueSoon' => $this->serviceReadingDueSoon,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
