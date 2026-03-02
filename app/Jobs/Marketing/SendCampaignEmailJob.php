<?php

namespace App\Jobs\Marketing;

use App\Models\Marketing\EmailCampaign;
use App\Models\Marketing\EmailSend;
use App\Models\Marketing\EmailSubscriber;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendCampaignEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $campaignId;
    public int $subscriberId;

    public function __construct(int $campaignId, int $subscriberId)
    {
        $this->campaignId = $campaignId;
        $this->subscriberId = $subscriberId;
    }

    public function handle(): void
    {
        $campaign = EmailCampaign::find($this->campaignId);
        $subscriber = EmailSubscriber::find($this->subscriberId);

        if (!$campaign || !$subscriber) {
            return;
        }

        // Only send to subscribed users and only for matching brand
        if ($subscriber->status !== 'subscribed' || $subscriber->brand !== $campaign->brand) {
            return;
        }

        // Prevent duplicates (unique index campaign_id + subscriber_id will also protect)
        $existing = EmailSend::where('campaign_id', $campaign->id)
            ->where('subscriber_id', $subscriber->id)
            ->first();

        if ($existing) {
            return;
        }

        $unsubscribeUrl = route('marketing.unsubscribe', ['token' => $subscriber->unsubscribe_token]);

        // Data for templates/layout
        $body = is_array($campaign->body_json) ? $campaign->body_json : [];

        $viewData = array_merge($body, [
            'brand'          => $campaign->brand,
            'subject'        => $campaign->subject,
            'heroImage'      => $campaign->hero_image,
            'unsubscribeUrl' => $unsubscribeUrl,
        ]);

        // Optional: allow different FROM per brand via Azure Env Vars.
        // If not set, it falls back to your existing mail.from config.
        $fromAddress = null;
        $fromName = null;

        if ($campaign->brand === 'sf') {
            $fromAddress = getenv('MARKETING_FROM_ADDRESS_SF') ?: null;
            $fromName = getenv('MARKETING_FROM_NAME_SF') ?: null;
        } else {
            $fromAddress = getenv('MARKETING_FROM_ADDRESS_SL') ?: null;
            $fromName = getenv('MARKETING_FROM_NAME_SL') ?: null;
        }

        try {
            $messageId = null;

            Mail::send($campaign->template_view, $viewData, function ($message) use ($subscriber, $campaign, $fromAddress, $fromName, &$messageId) {
                $message->to($subscriber->email)
                    ->subject($campaign->subject);

                if (!empty($fromAddress)) {
                    $message->from($fromAddress, $fromName ?: null);
                }

                // Try capture a message id if mailer exposes it (not guaranteed across drivers)
                try {
                    $symfony = $message->getSymfonyMessage();
                    if ($symfony && method_exists($symfony, 'getHeaders')) {
                        $h = $symfony->getHeaders();
                        if ($h && method_exists($h, 'get')) {
                            $idHeader = $h->get('Message-ID');
                            if ($idHeader && method_exists($idHeader, 'getBodyAsString')) {
                                $messageId = $idHeader->getBodyAsString();
                            }
                        }
                    }
                } catch (Exception $e) {
                    // ignore
                }
            });

            EmailSend::create([
                'campaign_id'   => $campaign->id,
                'subscriber_id' => $subscriber->id,
                'status'        => 'sent',
                'message_id'    => $messageId,
                'sent_at'       => now(),
            ]);
        } catch (Exception $e) {
            // Log failure
            try {
                EmailSend::create([
                    'campaign_id'   => $campaign->id,
                    'subscriber_id' => $subscriber->id,
                    'status'        => 'failed',
                    'message_id'    => null,
                    'sent_at'       => now(),
                ]);
            } catch (Exception $e2) {
                // ignore
            }

            // Re-throw so queue marks it failed/retries (default behavior)
            throw $e;
        }
    }
}