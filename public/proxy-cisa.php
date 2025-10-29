<?php
/*
  File: proxy-trendmicro.php
  Version: v1.0
  Last updated: 30 Oct 2025 by Max (ChatGPT)
  Description: Server-side proxy to fetch and cache Trend Micro’s live Cyberthreat RSS feed
               for SharpLync Threat Pulse. Returns feed in clean JSON format.
*/

header('Access-Control-Allow-Origin: https://sharplync.com.au');
header('Content-Type: application/json; charset=UTF-8');

// === Configuration ===
$feedUrl   = 'https://newsroom.trendmicro.com/cyberthreat?pageTemplate=rss';
$cacheFile = __DIR__ . '/cache_trendmicro_feed.json';
$cacheTime = 600; // 10 minutes

// === Serve cached copy if fresh ===
if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheTime)) {
    echo file_get_contents($cacheFile);
    exit;
}

// === Fetch live RSS feed ===
$context = stream_context_create([
    'http' => [
        'timeout' => 10,
        'user_agent' => 'SharpLyncThreatPulse/1.0 (+https://sharplync.com.au)'
    ]
]);

$xmlData = @file_get_contents($feedUrl, false, $context);

if ($xmlData === false) {
    // fallback if feed unreachable
    echo json_encode([
        'items' => [
            [
                'title' => 'Trend Micro feed unavailable — using fallback data.',
                'link'  => 'https://newsroom.trendmicro.com/',
                'date'  => date(DATE_RSS),
                'source' => 'Trend Micro (fallback)'
            ]
        ]
    ], JSON_PRETTY_PRINT);
    exit;
}

// === Parse XML ===
$xml = @simplexml_load_string($xmlData, "SimpleXMLElement", LIBXML_NOCDATA);
if ($xml === false || empty($xml->channel->item)) {
    echo json_encode(['error' => 'Invalid or empty RSS feed received.']);
    exit;
}

// === Convert to JSON ===
$items = [];
foreach ($xml->channel->item as $entry) {
    $items[] = [
        'title'  => (string)$entry->title,
        'link'   => (string)$entry->link,
        'date'   => (string)$entry->pubDate,
        'source' => 'Trend Micro'
    ];
}

$output = json_encode(['items' => $items], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

// === Cache it ===
file_put_contents($cacheFile, $output);

// === Output ===
echo $output;
?>