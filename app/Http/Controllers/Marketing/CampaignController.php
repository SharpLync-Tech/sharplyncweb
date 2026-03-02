<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Marketing\Campaign;
use App\Models\Marketing\EmailSubscriber;
use App\Jobs\Marketing\SendCampaignEmailJob;
use Illuminate\Support\Facades\Mail;

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
        $request->validate([
            'subject' => 'required|string|max:255',
            'body_html' => 'required|string',
        ]);

        Campaign::create([
            'subject' => $request->subject,
            'body_html' => $request->body_html,
            'status' => 'draft',
        ]);

        return redirect()
            ->route('marketing.admin.campaigns')
            ->with('success', 'Campaign created.');
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