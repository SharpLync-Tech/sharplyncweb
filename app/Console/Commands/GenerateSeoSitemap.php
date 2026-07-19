<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateSeoSitemap extends Command
{
    protected $signature = 'seo:generate-sitemap';

    protected $description = 'Generate public/sitemap.xml from the canonical SEO page list';

    public function handle(): int
    {
        $siteUrl = rtrim((string) config('seo.site_url'), '/');
        $paths = array_values(array_unique(config('seo.sitemap', [])));

        $urls = array_map(function (string $path) use ($siteUrl): string {
            $location = $siteUrl . ($path === '/' ? '/' : '/' . ltrim($path, '/'));

            return '    <url><loc>'
                . htmlspecialchars($location, ENT_XML1 | ENT_QUOTES, 'UTF-8')
                . '</loc></url>';
        }, $paths);

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL
            . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL
            . implode(PHP_EOL, $urls) . PHP_EOL
            . '</urlset>' . PHP_EOL;

        File::put(public_path('sitemap.xml'), $xml);
        $this->info('Generated sitemap with ' . count($paths) . ' canonical URLs.');

        return self::SUCCESS;
    }
}
