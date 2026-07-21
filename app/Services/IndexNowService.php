<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use RuntimeException;

class IndexNowService
{
    /**
     * Notify IndexNow about added, updated, or deleted public URLs.
     *
     * @param  array<int, string>  $urls  Absolute URLs or paths on the configured SEO host.
     */
    public function submit(array $urls): Response
    {
        $siteUrl = rtrim((string) config('seo.site_url'), '/');
        $host = parse_url($siteUrl, PHP_URL_HOST);
        $key = trim((string) config('seo.indexnow.key'));

        if (! is_string($host) || $host === '' || $key === '') {
            throw new RuntimeException('IndexNow requires a valid SEO site URL and key.');
        }

        $urlList = array_values(array_unique(array_map(
            fn (string $url): string => $this->canonicalUrl($url, $siteUrl, $host),
            $urls,
        )));

        if ($urlList === []) {
            throw new InvalidArgumentException('At least one URL is required for IndexNow submission.');
        }

        if (count($urlList) > 10000) {
            throw new InvalidArgumentException('IndexNow accepts at most 10,000 URLs per request.');
        }

        return Http::acceptJson()
            ->timeout(15)
            ->post((string) config('seo.indexnow.endpoint'), [
                'host' => $host,
                'key' => $key,
                'keyLocation' => $siteUrl . '/' . $key . '.txt',
                'urlList' => $urlList,
            ])
            ->throw();
    }

    private function canonicalUrl(string $url, string $siteUrl, string $host): string
    {
        $url = trim($url);
        $absoluteUrl = str_starts_with($url, 'http://') || str_starts_with($url, 'https://')
            ? $url
            : $siteUrl . ($url === '/' ? '/' : '/' . ltrim($url, '/'));

        if (parse_url($absoluteUrl, PHP_URL_HOST) !== $host) {
            throw new InvalidArgumentException('IndexNow URLs must belong to ' . $host . '.');
        }

        return $absoluteUrl;
    }
}
