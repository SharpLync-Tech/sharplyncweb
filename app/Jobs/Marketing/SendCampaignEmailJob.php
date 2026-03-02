<?php

namespace App\Jobs\Marketing;

use App\Models\Marketing\Campaign;
use App\Models\Marketing\Subscriber;
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

    protected $campaign;
    protected $subscriber;

    public function __construct(Campaign $campaign, Subscriber $subscriber)
    {
        $this->campaign = $campaign;
        $this->subscriber = $subscriber;
    }

    public function handle()
    {
        $existingSend = EmailSend::where('campaign_id', $this->campaign->id)
            ->where('subscriber_id', $this->subscriber->id)
            ->first();

        if ($existingSend && $existingSend->status === 'sent') {
            return;
        }

        try {

            Mail::send(
                'marketing.emails.master', // THIS IS YOUR TEMPLATE
                [
                    'campaign'   => $this->campaign,
                    'subscriber' => $this->subscriber,
                ],
                function ($message) {
                    $message->to($this->subscriber->email)
                            ->subject($this->campaign->subject);
                }
            );

            EmailSend::updateOrCreate(
                [
                    'campaign_id'   => $this->campaign->id,
                    'subscriber_id' => $this->subscriber->id,
                ],
                [
                    'status'   => 'sent',
                    'sent_at'  => now(),
                    'message_id' => null,
                ]
            );

        } catch (\Exception $e) {

            EmailSend::updateOrCreate(
                [
                    'campaign_id'   => $this->campaign->id,
                    'subscriber_id' => $this->subscriber->id,
                ],
                [
                    'status'  => 'failed',
                    'sent_at' => now(),
                ]
            );

            throw $e;
        }
    }
}