<?php

namespace App\Mail\SharpFleet;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AccountActivation extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $activationUrl;

    public function __construct($user)
    {
        $this->user = $user;
        $this->activationUrl = url("/app/sharpfleet/activate/{$user->activation_token}");

        Log::info("[SHARPFLEET EMAIL SENT] {$user->email} | URL={$this->activationUrl}");
    }

    public function build()
    {
        return $this->subject('Activate your SharpFleet account')
                    ->view('emails.sharpfleet.account-activation')
                    ->with([
                        'name' => $this->user->first_name,
                        'activationUrl' => $this->activationUrl,
                    ]);
    }
}