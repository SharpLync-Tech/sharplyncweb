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

        return view('marketing.unsubscribed');
    }
}
