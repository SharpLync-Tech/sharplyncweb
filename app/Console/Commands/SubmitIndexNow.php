<?php

namespace App\Console\Commands;

use App\Services\IndexNowService;
use Illuminate\Console\Command;

class SubmitIndexNow extends Command
{
    protected $signature = 'seo:indexnow
        {urls?* : Public URLs or paths that were added, updated, or deleted}
        {--all : Submit every canonical URL configured for the sitemap}';

    protected $description = 'Notify IndexNow about changed public URLs';

    public function handle(IndexNowService $indexNow): int
    {
        $urls = $this->option('all')
            ? config('seo.sitemap', [])
            : $this->argument('urls');

        if ($urls === []) {
            $this->error('Provide one or more URLs, or use --all.');

            return self::INVALID;
        }

        $response = $indexNow->submit($urls);
        $this->info('Submitted ' . count(array_unique($urls)) . ' URL(s) to IndexNow (HTTP ' . $response->status() . ').');

        return self::SUCCESS;
    }
}
