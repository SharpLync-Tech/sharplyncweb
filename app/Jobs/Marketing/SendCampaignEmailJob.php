<?php

namespace App\Jobs\Marketing;

use App\Models\Marketing\Campaign;
use App\Models\Marketing\EmailSubscriber;
use App\Models\Marketing\EmailSend;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendCampaignEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $campaignId;
    public $subscriberId;

    public function __construct($campaignId, $subscriberId)
    {
        $this->campaignId = $campaignId;
        $this->subscriberId = $subscriberId;
    }

    public function handle()
    {
        $campaign = Campaign::find($this->campaignId);
        $subscriber = EmailSubscriber::find($this->subscriberId);

        if (!$campaign || !$subscriber) {
            Log::error('[MARKETING] Missing campaign or subscriber', [
                'campaign_id' => $this->campaignId,
                'subscriber_id' => $this->subscriberId,
            ]);
            return;
        }

        try {

            Mail::send([], [], function ($message) use ($campaign, $subscriber) {
                $message->to($subscriber->email)
                    ->subject($campaign->subject)
                    ->html($campaign->body_html);
            });

            EmailSend::create([
                'campaign_id' => $campaign->id,
                'subscriber_id' => $subscriber->id,
                'status' => 'sent',
                'message_id' => null,
                'sent_at' => now(),
            ]);

        } catch (\Throwable $e) {

            Log::error('[MARKETING] Send failed', [
                'campaign_id' => $campaign->id,
                'subscriber_id' => $subscriber->id,
                'error' => $e->getMessage(),
            ]);

            EmailSend::create([
                'campaign_id' => $campaign->id,
                'subscriber_id' => $subscriber->id,
                'status' => 'failed',
                'message_id' => null,
                'sent_at' => now(),
            ]);
        }
    }
}