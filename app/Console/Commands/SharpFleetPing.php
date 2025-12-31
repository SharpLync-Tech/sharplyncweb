<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SharpFleetPing extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'sharpfleet:ping';

    /**
     * The console command description.
     */
    protected $description = 'Test command to verify Azure scheduler and WebJobs execution';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        Log::info('[SharpFleet Ping] Scheduler ran at ' . now()->toDateTimeString());

        $this->info('SharpFleet ping executed successfully.');

        return self::SUCCESS;
    }
}