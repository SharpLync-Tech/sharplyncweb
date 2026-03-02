<?php

namespace App\Jobs\Marketing;

use App\Models\Marketing\Campaign;
use App\Models\Marketing\EmailSubscriber;
use App\Models\Marketing\EmailSend;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendCampaignEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $campaignId;
    protected int $subscriberId;

    public function __construct(int $campaignId, int $subscriberId)
    {
        $this->campaignId = $campaignId;
        $this->subscriberId = $subscriberId;
    }

    public function handle()
    {
        $campaign = Campaign::find($this->campaignId);
        $subscriber = EmailSubscriber::find($this->subscriberId);

        if (!$campaign || !$subscriber) {
            return;
        }

        $existingSend = EmailSend::where('campaign_id', $campaign->id)
            ->where('subscriber_id', $subscriber->id)
            ->first();

        if ($existingSend && $existingSend->status == 'sent') {
            return;
        }

        $brand = $campaign->brand;
        $template = $campaign->template_view;

        if (!$template) {
            $template = $brand === 'sf'
                ? 'emails.marketing.templates.sf-basic'
                : 'emails.marketing.templates.sl-basic';
        }

        $unsubscribeUrl = $subscriber->unsubscribe_token
            ? url('/marketing/unsubscribe/' . $subscriber->unsubscribe_token)
            : null;
        $preferencesUrl = $subscriber->unsubscribe_token
            ? url('/marketing/preferences/' . $subscriber->unsubscribe_token)
            : null;

        $payload = array_merge($campaign->body_json ?? [], [
            'campaign' => $campaign,
            'subscriber' => $subscriber,
            'brand' => $brand,
            'heroImage' => $campaign->hero_image,
            'unsubscribeUrl' => $unsubscribeUrl,
            'preferencesUrl' => $preferencesUrl,
            'subject' => $campaign->subject,
            'preheader' => $campaign->preheader,
            'bodyHtml' => $campaign->body_html,
        ]);

        try {
            Mail::send(
                $template,
                $payload,
                function ($message) use ($campaign, $subscriber, $brand) {
                    $fromAddress = config('mail.from.address');
                    $fromName = $brand === 'sf' ? 'SharpFleet' : 'SharpLync';

                    if ($fromAddress) {
                        $message->from($fromAddress, $fromName);
                    }

                    $message->to($subscriber->email)
                        ->subject($campaign->subject);
                }
            );

            EmailSend::updateOrCreate(
                [
                    'campaign_id' => $campaign->id,
                    'subscriber_id' => $subscriber->id,
                ],
                [
                    'status' => 'sent',
                    'sent_at' => now(),
                    'message_id' => null,
                ]
            );
        } catch (\Exception $e) {
            EmailSend::updateOrCreate(
                [
                    'campaign_id' => $campaign->id,
                    'subscriber_id' => $subscriber->id,
                ],
                [
                    'status' => 'failed',
                    'sent_at' => now(),
                ]
            );

            throw $e;
        }
    }
}
