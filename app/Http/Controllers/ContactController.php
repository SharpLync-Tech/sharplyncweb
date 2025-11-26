<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function submit(Request $request)
    {
        // Check if reCAPTCHA is configured
        $recaptchaEnabled = config('services.recaptcha.key') && config('services.recaptcha.secret');
        $recaptchaScoreThreshold = (float) config('services.recaptcha.score', 0.5);

        // Validation
        $validated = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'email'           => ['required', 'email', 'max:255'],
            'phone'           => ['nullable', 'string', 'max:50'],
            'subject'         => ['required', 'string', 'max:255'],
            'message'         => ['required', 'string', 'max:5000'],
            'address_bot_trap'=> ['nullable', 'string', 'max:255'],
            'recaptcha_token' => $recaptchaEnabled
                ? ['required', 'string']
                : ['nullable', 'string'],
        ]);

        // Honeypot – if filled, pretend success but do nothing
        if (!empty($validated['address_bot_trap'])) {
            return back()
                ->with('success', "Message sent! We’ll get back to you shortly.")
                ->withInput();
        }

        // reCAPTCHA v3 verification (if enabled)
        if ($recaptchaEnabled) {
            try {
                $response = Http::asForm()->post(
                    'https://www.google.com/recaptcha/api/siteverify',
                    [
                        'secret'   => config('services.recaptcha.secret'),
                        'response' => $validated['recaptcha_token'],
                        'remoteip' => $request->ip(),
                    ]
                );

                if (!$response->successful()) {
                    Log::warning('reCAPTCHA HTTP failure for contact form', [
                        'status' => $response->status(),
                        'body'   => $response->body(),
                    ]);

                    return back()
                        ->with('error', 'Something went wrong while verifying your request. Please try again.')
                        ->withInput();
                }

                $result = $response->json();

                $score   = $result['score']   ?? 0.0;
                $success = $result['success'] ?? false;
                $action  = $result['action']  ?? null;

                // Optional: check action === 'submit_contact' if you want to be strict
                if (!$success || $score < $recaptchaScoreThreshold) {
                    Log::info('reCAPTCHA failed or low score on contact form', [
                        'score'  => $score,
                        'action' => $action,
                        'ip'     => $request->ip(),
                        'email'  => $validated['email'],
                    ]);

                    return back()
                        ->with('error', 'We could not verify your request. Please try again.')
                        ->withInput();
                }
            } catch (\Throwable $e) {
                Log::error('reCAPTCHA exception on contact form', [
                    'message' => $e->getMessage(),
                ]);

                return back()
                    ->with('error', 'Something went wrong while verifying your request. Please try again.')
                    ->withInput();
            }
        }

        // Prepare data for emails
        $data = [
            'name'    => $validated['name'],
            'email'   => $validated['email'],
            'phone'   => $validated['phone'] ?? null,
            'subject' => $validated['subject'],
            'message' => $validated['message'],
        ];

        try {
            // 1) Email to SharpLync (admin notification)
            Mail::send('emails.contact.admin-notification', $data, function ($user_message) use ($data) {
                $user_message->to('info@sharplync.com.au')
                    ->subject('New Contact Form Message from ' . $data['name'])
                    ->from(config('mail.from.address'), 'SharpLync');
            });

            // 2) Confirmation email to the user (branded)
            Mail::send('emails.contact.user-confirmation', $data, function ($user_message) use ($data) {
                $user_message->to($data['email'], $data['name'])
                    ->subject('We’ve received your message – SharpLync')
                    ->from(config('mail.from.address'), 'SharpLync');
            });

        } catch (\Throwable $e) {
            Log::error('Contact form email failure', [
                'error' => $e->getMessage(),
                'email' => $validated['email'],
            ]);

            return back()
                ->with('error', 'Something went wrong while sending your message. Please try again.')
                ->withInput();
        }

        return back()
            ->with('success', "Message sent! We’ll get back to you shortly.");
    }
}
