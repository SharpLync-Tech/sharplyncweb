<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class VerifyEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $verifyUrl;

    public function __construct($user)
    {
        $this->user = $user;
        $this->verifyUrl = url("/verify/{$user->verification_token}");

        Log::info("[EMAIL SENT] {$user->email} | URL={$this->verifyUrl}");
    }

    public function build()
    {
        return $this->subject('Verify your SharpLync account')
                    ->view('emails.verify')
                    ->with([
                        'name' => $this->user->first_name,
                        'verifyUrl' => $this->verifyUrl,
                    ]);
    }
}