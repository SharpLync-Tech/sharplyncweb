<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Jobs\Marketing\SendCampaignEmailJob;
use App\Models\Marketing\EmailCampaign;
use App\Models\Marketing\EmailSubscriber;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    /**
     * Dispatch jobs to send a campaign now (one job per subscriber).
     */
    public function sendNow(Request $request, int $id)
    {
        $campaign = EmailCampaign::find($id);

        if (!$campaign) {
            return response()->json(['success' => false, 'message' => 'Campaign not found.'], 404);
        }

        // Mark as sending (simple v1 state)
        $campaign->status = 'sending';
        $campaign->sent_at = now();
        $campaign->save();

        $subscribers = EmailSubscriber::where('brand', $campaign->brand)
            ->where('status', 'subscribed')
            ->get(['id']);

        foreach ($subscribers as $s) {
            SendCampaignEmailJob::dispatch($campaign->id, $s->id);
        }

        // Mark as sent (v1: dispatch complete; actual delivery may still be in queue)
        $campaign->status = 'sent';
        $campaign->save();

        return response()->json([
            'success' => true,
            'message' => 'Campaign dispatch started.',
            'campaign_id' => $campaign->id,
            'subscribers_dispatched' => $subscribers->count(),
        ]);
    }

    /**
     * Process scheduled campaigns (dispatch jobs for any due campaigns).
     */
    public function processScheduled(Request $request)
    {
        $due = EmailCampaign::where('status', 'scheduled')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->get();

        $processed = 0;
        $totalDispatched = 0;

        foreach ($due as $campaign) {
            $campaign->status = 'sending';
            $campaign->sent_at = now();
            $campaign->save();

            $subscribers = EmailSubscriber::where('brand', $campaign->brand)
                ->where('status', 'subscribed')
                ->get(['id']);

            foreach ($subscribers as $s) {
                SendCampaignEmailJob::dispatch($campaign->id, $s->id);
            }

            $campaign->status = 'sent';
            $campaign->save();

            $processed++;
            $totalDispatched += $subscribers->count();
        }

        return response()->json([
            'success' => true,
            'processed_campaigns' => $processed,
            'total_subscribers_dispatched' => $totalDispatched,
        ]);
    }
}