<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Marketing\Campaign;
use App\Models\Marketing\EmailSubscriber;
use App\Jobs\Marketing\SendCampaignEmailJob;

class CampaignController extends Controller
{
    public function index()
    {
        $campaigns = Campaign::orderByDesc('id')->get();

        return view('marketing.admin.campaigns.index', compact('campaigns'));
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

        return redirect()->route('marketing.admin.campaigns')
            ->with('success', 'Campaign created.');
    }

    public function sendNowWeb($id)
    {
        $campaign = Campaign::findOrFail($id);

        $subscribers = EmailSubscriber::where('status', 'confirmed')->get();

        foreach ($subscribers as $subscriber) {
            SendCampaignEmailJob::dispatch($campaign->id, $subscriber->id);
        }

        return redirect()->route('marketing.admin.campaigns')
            ->with('success', 'Campaign dispatched to ' . $subscribers->count() . ' subscribers.');
    }
}