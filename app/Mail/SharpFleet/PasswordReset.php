<?php

namespace App\Mail\SharpFleet;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordReset extends Mailable
{
    use Queueable, SerializesModels;

    public $payload;

    public function __construct($payload)
    {
        $this->payload = $payload;
    }

    public function build()
    {
        return $this->subject('Reset your SharpFleet password')
            ->view('emails.sharpfleet.password-reset')
            ->with([
                'name' => $this->payload->first_name ?? null,
                'resetUrl' => $this->payload->reset_url,
                'expiresMinutes' => $this->payload->expires_minutes ?? 30,
            ]);
    }
}
