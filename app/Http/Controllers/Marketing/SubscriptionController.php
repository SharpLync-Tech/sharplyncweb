<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Models\Marketing\EmailSubscriber;

class SubscriptionController extends Controller
{
    /**
     * Handle subscription request (creates pending subscriber).
     */
    public function subscribe(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'max:190'],
            'brand' => ['required', 'in:sl,sf'],
        ]);

        $email = strtolower(trim($request->email));
        $brand = $request->brand;

        $subscriber = EmailSubscriber::where('email', $email)
            ->where('brand', $brand)
            ->first();

        if ($subscriber) {
            if ($subscriber->status === 'unsubscribed') {
                $subscriber->status = 'pending';
                $subscriber->confirmation_token = Str::random(60);
                $subscriber->confirmed_at = null;
                $subscriber->unsubscribed_at = null;
                $subscriber->save();
            } elseif ($subscriber->status === 'subscribed') {
                return response()->json([
                    'success' => true,
                    'message' => 'You are already subscribed.',
                ]);
            }
        } else {
            $subscriber = EmailSubscriber::create([
                'email' => $email,
                'brand' => $brand,
                'status' => 'pending',
                'confirmation_token' => Str::random(60),
                'unsubscribe_token' => Str::random(60),
            ]);
        }

        if (!$subscriber->confirmation_token) {
            $subscriber->confirmation_token = Str::random(60);
            $subscriber->save();
        }

        Mail::send('emails.marketing.confirm-subscription', [
            'subscriber' => $subscriber,
        ], function ($message) use ($subscriber) {
            $message->to($subscriber->email)
                ->subject('Please confirm your subscription');
        });

        return response()->json([
            'success' => true,
            'message' => 'Please check your email to confirm your subscription.',
        ]);
    }

    /**
     * Confirm subscription.
     */
    public function confirm($token)
    {
        $subscriber = EmailSubscriber::where('confirmation_token', $token)
            ->where('status', 'pending')
            ->first();

        if (!$subscriber) {
            return view('marketing.invalid-token');
        }

        $subscriber->status = 'subscribed';
        $subscriber->confirmed_at = now();
        $subscriber->confirmation_token = null;
        $subscriber->save();

        $this->sendAdminNotice($subscriber, 'Subscription confirmed');

        return view('marketing.subscription-confirmed');
    }

    /**
     * Unsubscribe.
     */
    public function unsubscribe($token)
    {
        $subscriber = EmailSubscriber::where('unsubscribe_token', $token)
            ->where('status', 'subscribed')
            ->first();

        if (!$subscriber) {
            return view('marketing.invalid-token');
        }

        $subscriber->status = 'unsubscribed';
        $subscriber->unsubscribed_at = now();
        $subscriber->save();

        $this->sendAdminNotice($subscriber, 'Unsubscribed');

        return view('marketing.unsubscribed');
    }

    /**
     * Show manage preferences page.
     */
    public function preferences($token)
    {
        $subscriber = EmailSubscriber::where('unsubscribe_token', $token)->first();

        if (!$subscriber) {
            return view('marketing.invalid-token');
        }

        $email = $subscriber->email;

        $sl = EmailSubscriber::where('email', $email)->where('brand', 'sl')->first();
        $sf = EmailSubscriber::where('email', $email)->where('brand', 'sf')->first();

        return view('marketing.preferences', [
            'email' => $email,
            'token' => $token,
            'sl' => $sl,
            'sf' => $sf,
        ]);
    }

    /**
     * Update preferences for SL/SF.
     */
    public function updatePreferences(Request $request, $token)
    {
        $subscriber = EmailSubscriber::where('unsubscribe_token', $token)->first();

        if (!$subscriber) {
            return view('marketing.invalid-token');
        }

        $email = $subscriber->email;

        $wantsSl = $request->boolean('pref_sl');
        $wantsSf = $request->boolean('pref_sf');

        $sl = EmailSubscriber::where('email', $email)->where('brand', 'sl')->first();
        $sf = EmailSubscriber::where('email', $email)->where('brand', 'sf')->first();
        $slBefore = $sl ? $sl->status : null;
        $sfBefore = $sf ? $sf->status : null;

        if ($wantsSl) {
            if (!$sl) {
                $sl = EmailSubscriber::create([
                    'email' => $email,
                    'brand' => 'sl',
                    'status' => 'subscribed',
                    'confirmation_token' => null,
                    'unsubscribe_token' => $subscriber->unsubscribe_token,
                    'confirmed_at' => now(),
                ]);
            } else {
                $sl->status = 'subscribed';
                $sl->confirmed_at = $sl->confirmed_at ?: now();
                $sl->unsubscribed_at = null;
                $sl->save();
            }
        } elseif ($sl) {
            $sl->status = 'unsubscribed';
            $sl->unsubscribed_at = now();
            $sl->save();
        }

        if ($wantsSf) {
            if (!$sf) {
                $sf = EmailSubscriber::create([
                    'email' => $email,
                    'brand' => 'sf',
                    'status' => 'subscribed',
                    'confirmation_token' => null,
                    'unsubscribe_token' => $subscriber->unsubscribe_token,
                    'confirmed_at' => now(),
                ]);
            } else {
                $sf->status = 'subscribed';
                $sf->confirmed_at = $sf->confirmed_at ?: now();
                $sf->unsubscribed_at = null;
                $sf->save();
            }
        } elseif ($sf) {
            $sf->status = 'unsubscribed';
            $sf->unsubscribed_at = now();
            $sf->save();
        }

        if ($sl && $slBefore !== $sl->status) {
            $this->sendAdminNotice($sl, $sl->status === 'subscribed' ? 'Subscription updated' : 'Unsubscribed');
        }
        if ($sf && $sfBefore !== $sf->status) {
            $this->sendAdminNotice($sf, $sf->status === 'subscribed' ? 'Subscription updated' : 'Unsubscribed');
        }

        return view('marketing.preferences', [
            'email' => $email,
            'token' => $token,
            'sl' => $sl,
            'sf' => $sf,
            'saved' => true,
        ]);
    }

    private function sendAdminNotice(EmailSubscriber $subscriber, string $event): void
    {
        $brand = $subscriber->brand === 'sf' ? 'SharpFleet' : 'SharpLync';
        $to = $subscriber->brand === 'sf'
            ? 'info@sharpfleet.com.au'
            : 'info@sharplync.com.au';

        $lines = [
            'Event: ' . $event,
            'Brand: ' . $brand,
            'Email: ' . $subscriber->email,
        ];

        if (!empty($subscriber->first_name)) {
            $lines[] = 'First name: ' . $subscriber->first_name;
        }

        $lines[] = 'Status: ' . $subscriber->status;
        $lines[] = 'Time: ' . now()->format('d/m/Y H:i:s');

        Mail::raw(implode("\n", $lines), function ($message) use ($to, $event, $brand) {
            $message->to($to)
                ->subject('Marketing ' . $brand . ': ' . $event);
        });
    }
}
