<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Marketing\Campaign;
use App\Models\Marketing\EmailSubscriber;
use App\Jobs\Marketing\SendCampaignEmailJob;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class CampaignController extends Controller
{
    public function index()
    {
        $campaigns = Campaign::orderByDesc('id')->get();
        $subscriberCount = EmailSubscriber::where('status', 'subscribed')->count();

        return view('marketing.admin.campaigns.index', compact('campaigns', 'subscriberCount'));
    }

    public function create()
    {
        return view('marketing.admin.campaigns.create');
    }

    public function store(Request $request)
    {
        // --- DEBUG: log what we received ---
        Log::info('[MARKETING] Campaign store hit', [
            'ip' => $request->ip(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'payload' => $request->all(),
        ]);

        // --- Validate ---
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'body_html' => ['required', 'string'],
        ]);

        Log::info('[MARKETING] Campaign validation passed', [
            'validated' => $validated,
        ]);

        try {
            $campaign = Campaign::create([
                'name' => $validated['name'],
                'subject' => $validated['subject'],
                'body_html' => $validated['body_html'],
                'status' => 'draft',
            ]);

            Log::info('[MARKETING] Campaign created', [
                'campaign_id' => $campaign->id ?? null,
                'campaign' => $campaign->toArray(),
            ]);

            if (!$campaign || !$campaign->id) {
                Log::error('[MARKETING] Campaign create returned no ID', [
                    'campaign_object' => $campaign ? $campaign->toArray() : null,
                ]);

                return redirect()
                    ->route('marketing.admin.campaigns.create')
                    ->withInput()
                    ->with('error', 'Campaign did not return an ID. Check logs: storage/logs/laravel.log');
            }

            return redirect()
                ->route('marketing.admin.campaigns')
                ->with('success', 'Campaign created (ID: ' . $campaign->id . ').');
        } catch (\Throwable $e) {
            Log::error('[MARKETING] Campaign create failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return redirect()
                ->route('marketing.admin.campaigns.create')
                ->withInput()
                ->with('error', 'Create failed: ' . $e->getMessage());
        }
    }

    public function preview($id)
    {
        $campaign = Campaign::findOrFail($id);

        return view('marketing.admin.campaigns.preview', compact('campaign'));
    }

    public function sendTest($id)
    {
        $campaign = Campaign::findOrFail($id);

        $subscriber = EmailSubscriber::where('status', 'subscribed')->first();

        if (!$subscriber) {
            return redirect()
                ->route('marketing.admin.campaigns')
                ->with('error', 'No subscribed test user found.');
        }

        Mail::send([], [], function ($message) use ($campaign, $subscriber) {
            $message->to($subscriber->email)
                ->subject('[TEST] ' . $campaign->subject)
                ->html($campaign->body_html);
        });

        return redirect()
            ->route('marketing.admin.campaigns')
            ->with('success', 'Test email sent to ' . $subscriber->email);
    }

    public function sendNowWeb($id)
    {
        $campaign = Campaign::findOrFail($id);

        if ($campaign->status === 'sent') {
            return redirect()
                ->route('marketing.admin.campaigns')
                ->with('success', 'Campaign already sent.');
        }

        $subscribers = EmailSubscriber::where('status', 'subscribed')->get();

        foreach ($subscribers as $subscriber) {
            SendCampaignEmailJob::dispatch($campaign->id, $subscriber->id);
        }

        $campaign->status = 'sent';
        $campaign->save();

        return redirect()
            ->route('marketing.admin.campaigns')
            ->with('success', 'Campaign dispatched to ' . $subscribers->count() . ' subscribers.');
    }
}