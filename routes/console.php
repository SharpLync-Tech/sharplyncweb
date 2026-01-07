<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Console\Scheduling\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

app()->booted(function () {
    app(Schedule::class)
        ->command('sharpfleet:ping')
        ->hourly();

    app(Schedule::class)
        ->command('sharpfleet:send-reminders')
        ->daily();

    // Booking reminders are time-sensitive (1 hour before start), so run more frequently.
    app(Schedule::class)
        ->command('sharpfleet:send-booking-reminders')
        ->everyFiveMinutes();
});
