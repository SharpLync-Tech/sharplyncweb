<?php

namespace App\Mail\SharpFleet;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DriverInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public $invite;
    public $acceptUrl;

    public function __construct($invite)
    {
        $this->invite = $invite;
        $this->acceptUrl = url("/app/sharpfleet/invite/{$invite->activation_token}");
    }

    public function build()
    {
        return $this->subject('You\'re invited to SharpFleet')
            ->view('emails.sharpfleet.driver-invitation')
            ->with([
                'organisationName' => $this->invite->organisation_name ?? null,
                'acceptUrl' => $this->acceptUrl,
            ]);
    }
}
