<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Marketing\EmailSubscriber;
use Illuminate\Support\Str;

class SubscriberController extends Controller
{
    public function index()
    {
        $subscribers = EmailSubscriber::orderByDesc('id')->limit(200)->get();

        return view('marketing.admin.subscribers.index', [
            'subscribers' => $subscribers,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:190'],
            'brand' => ['required', 'in:sl,sf'],
            'status' => ['required', 'in:pending,subscribed,unsubscribed'],
        ]);

        $email = strtolower(trim($validated['email']));

        $subscriber = EmailSubscriber::where('email', $email)
            ->where('brand', $validated['brand'])
            ->first();

        if ($subscriber) {
            return redirect()
                ->route('marketing.admin.subscribers')
                ->with('error', 'Subscriber already exists for this brand.');
        }

        EmailSubscriber::create([
            'first_name' => $validated['first_name'] ?? null,
            'email' => $email,
            'brand' => $validated['brand'],
            'status' => $validated['status'],
            'confirmation_token' => Str::random(60),
            'unsubscribe_token' => Str::random(60),
            'confirmed_at' => $validated['status'] === 'subscribed' ? now() : null,
            'unsubscribed_at' => $validated['status'] === 'unsubscribed' ? now() : null,
        ]);

        return redirect()
            ->route('marketing.admin.subscribers')
            ->with('success', 'Subscriber added.');
    }
}
