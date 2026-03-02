<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Console\Scheduling\Schedule;
use App\Models\Marketing\Campaign;
use App\Models\Marketing\EmailSubscriber;
use App\Jobs\Marketing\SendCampaignEmailJob;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('marketing:process-scheduled', function () {
    $now = now();

    $campaigns = Campaign::where('status', 'scheduled')
        ->whereNotNull('scheduled_at')
        ->where('scheduled_at', '<=', $now)
        ->get();

    $processed = 0;

    foreach ($campaigns as $campaign) {
        $subscriberQuery = EmailSubscriber::where('status', 'subscribed')
            ->where('brand', $campaign->brand);

        $subscriberQuery->chunkById(200, function ($subscribers) use ($campaign) {
            foreach ($subscribers as $subscriber) {
                SendCampaignEmailJob::dispatch($campaign->id, $subscriber->id);
            }
        });

        $campaign->status = 'sent';
        $campaign->sent_at = now();
        $campaign->save();

        $processed++;
    }

    $this->info('Processed ' . $processed . ' scheduled campaign(s).');
})->purpose('Process scheduled marketing campaigns');

app()->booted(function () {
    app(Schedule::class)
        ->command('sharpfleet:ping')
        ->hourly();

    app(Schedule::class)
        ->command('sharpfleet:send-reminders')
        ->daily();

    // Booking reminders are time-sensitive (1 hour before start), so run more frequently.
    app(Schedule::class)
    ->command('sharpfleet:send-booking-reminders --dry-run=0')
    ->appendOutputTo(storage_path('logs/sharpfleet-send-booking-reminders.log'))
    ->everyFiveMinutes();

    app(Schedule::class)
        ->command('marketing:process-scheduled')
        ->everyFiveMinutes();

});
