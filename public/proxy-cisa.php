<?php
/*
  File: proxy-cisa.php
  Version: v2.0
  Last updated: 30 Oct 2025 by Max (ChatGPT)
  Description: Hybrid threat intelligence proxy that fetches live RSS feeds from CERT.at and ENISA,
               with automatic fallback to demo alerts if both sources fail.
*/

// ---------------- CONFIGURATION ---------------- //
header('Access-Control-Allow-Origin: https://sharplync.com.au');
header('Content-Type: application/json; charset=UTF-8');

$feeds = [
    'certat' => 'https://www.cert.at/tagesmeldungen/rss.xml',     // 🇦🇹 Austrian CERT feed
    'enisa'  => 'https://www.enisa.europa.eu/news/enisa-news/RSS' // 🇪🇺 EU Cybersecurity Agency feed
];

$cacheFile = __DIR__ . '/cache_threats.json';
$cacheTime = 600; // 10 minutes

// ---------------- HELPER FUNCTIONS ---------------- //
function fetch_feed($url) {
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'user_agent' => 'SharpLyncThreatPulse/2.0'
        ]
    ]);
    return @file_get_contents($url, false, $context);
}

function parse_rss($rssContent, $source) {
    $xml = @simplexml_load_string($rssContent, 'SimpleXMLElement', LIBXML_NOCDATA);
    if (!$xml) return [];

    $items = [];
    foreach ($xml->channel->item as $entry) {
        $items[] = [
            'title' => (string)$entry->title,
            'link'  => (string)$entry->link,
            'date'  => (string)$entry->pubDate,
            'source' => strtoupper($source)
        ];
    }
    return $items;
}

// ---------------- MAIN LOGIC ---------------- //

// Use cached version if available and fresh
if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheTime)) {
    echo file_get_contents($cacheFile);
    exit;
}

$results = [];
foreach ($feeds as $key => $url) {
    $rss = fetch_feed($url);
    if ($rss) {
        $parsed = parse_rss($rss, $key);
        if (!empty($parsed)) {
            $results = array_slice($parsed, 0, 10);
            break;
        }
    }
}

// ---------------- FALLBACK IF ALL FAIL ---------------- //
if (empty($results)) {
    $results = [
        [
            'title' => 'CISA feed unavailable — showing sample alert.',
            'link'  => 'https://sharplync.com.au',
            'date'  => date('r'),
            'source' => 'DEMO'
        ],
        [
            'title' => 'SharpLync detects phishing domains impersonating Microsoft 365.',
            'link'  => 'https://sharplync.com.au',
            'date'  => date('r'),
            'source' => 'DEMO'
        ],
        [
            'title' => 'Trend Micro identifies surge in ransomware traffic across EU.',
            'link'  => 'https://sharplync.com.au',
            'date'  => date('r'),
            'source' => 'DEMO'
        ]
    ];
}

// ---------------- OUTPUT ---------------- //
$output = json_encode(['items' => $results], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
file_put_contents($cacheFile, $output);
echo $output;
?>