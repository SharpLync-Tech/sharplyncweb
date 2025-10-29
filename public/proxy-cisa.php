<?php
/*
  File: proxy-cisa.php
  Version: v1.0
  Last updated: 30 Oct 2025 by Max (ChatGPT)
  Description: SharpLync multi-source live intelligence feed aggregator
               (The Hacker News, BleepingComputer, and DownDetector).
*/

header('Access-Control-Allow-Origin: https://sharplync.com.au');
header('Content-Type: application/json; charset=UTF-8');

// ---------- CONFIGURATION ----------
$feeds = [
    'HackerNews'      => 'https://feeds.feedburner.com/TheHackersNews',
    'BleepingComputer'=> 'https://www.bleepingcomputer.com/feed/',
    // DownDetector unofficial RSS (through feedparser.io mirror)
    'DownDetector'    => 'https://rss.app/feeds/L8d2XTTT2YV8FyD9.xml' // replace with working mirror later
];

$cacheFile = __DIR__ . '/cache_intel_feed.json';
$cacheTime = 900; // 15 minutes

// ---------- USE CACHE IF AVAILABLE ----------
if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheTime)) {
    echo file_get_contents($cacheFile);
    exit;
}

$allItems = [];

foreach ($feeds as $source => $url) {
    $context = stream_context_create([
        'http' => ['timeout' => 10, 'user_agent' => 'SharpLyncThreatPulse/1.0']
    ]);

    $xmlData = @file_get_contents($url, false, $context);
    if (!$xmlData) continue;

    $xml = @simplexml_load_string($xmlData, "SimpleXMLElement", LIBXML_NOCDATA);
    if (!$xml || !isset($xml->channel->item)) continue;

    foreach ($xml->channel->item as $item) {
        $allItems[] = [
            'title'   => (string) $item->title,
            'link'    => (string) $item->link,
            'date'    => date('D, d M Y H:i:s O', strtotime((string)$item->pubDate ?? 'now')),
            'source'  => $source
        ];
    }
}

// ---------- FALLBACK ----------
if (empty($allItems)) {
    $allItems = [
        ['title'=>'No live feeds available — showing fallback data.',
         'link'=>'https://sharplync.com.au',
         'date'=>date('D, d M Y H:i:s O'),
         'source'=>'DEMO'],
        ['title'=>'Check your network or feed URLs.',
         'link'=>'https://sharplync.com.au',
         'date'=>date('D, d M Y H:i:s O'),
         'source'=>'DEMO']
    ];
}

// ---------- SORT BY DATE ----------
usort($allItems, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});

$output = json_encode(['items'=>$allItems], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

// ---------- SAVE CACHE ----------
file_put_contents($cacheFile, $output);

// ---------- OUTPUT ----------
echo $output;
?>