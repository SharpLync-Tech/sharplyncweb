<?php

namespace App\Mail\SharpFleet;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\\Mail\\Mailables\\Envelope;
use Illuminate\\Mail\\Mailables\\Address;
use Illuminate\Queue\SerializesModels;

class FaultReported extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $organisationName,
        public string $reportType,
        public string $severity,
        public string $vehicleName,
        public string $vehicleRegistration,
        public string $reporterName,
        public string $reporterEmail,
        public ?string $occurredAt,
        public ?string $title,
        public string $description,
        public ?int $tripId,
        public string $reportedAt,
        public string $adminUrl
    ) {
    }

    public function envelope(): Envelope
    {
        $typeLabel = ucfirst($this->reportType);
        $subjectOrg = trim($this->organisationName) !== '' ? (' - ' . trim($this->organisationName)) : '';
        return new Envelope(
            from: new Address(config('mail.from.address'), 'SharpFleet'),
            subject: "SharpFleet {$typeLabel} reported" . $subjectOrg,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.sharpfleet.fault-reported',
            with: [
                'organisationName' => $this->organisationName,
                'reportType' => $this->reportType,
                'severity' => $this->severity,
                'vehicleName' => $this->vehicleName,
                'vehicleRegistration' => $this->vehicleRegistration,
                'reporterName' => $this->reporterName,
                'reporterEmail' => $this->reporterEmail,
                'occurredAt' => $this->occurredAt,
                'title' => $this->title,
                'description' => $this->description,
                'tripId' => $this->tripId,
                'reportedAt' => $this->reportedAt,
                'adminUrl' => $this->adminUrl,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

