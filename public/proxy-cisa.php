<?php
/*
  File: proxy-cisa.php
  Version: v1.0
  Last updated: 30 Oct 2025 by Max (ChatGPT)
  Description: Secure server-side proxy for fetching the live CISA Alerts RSS feed.
*/

// Allow cross-origin requests from your domain
header('Access-Control-Allow-Origin: https://sharplync.com.au');
header('Content-Type: application/json; charset=UTF-8');

// CISA RSS feed source
$feedUrl = 'https://catalog.data.gov/api/3/action/package_search?q=cisa+alerts';

// Simple 10-minute cache to reduce load on CISA and your server
$cacheFile = __DIR__ . '/cache_cisa_feed.json';
$cacheTime = 600; // seconds

// Serve cached version if it's still fresh
if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheTime)) {
    echo file_get_contents($cacheFile);
    exit;
}

// Try fetching live data from CISA
$context = stream_context_create([
    'http' => ['timeout' => 10, 'user_agent' => 'SharpLyncThreatPulse/1.0']
]);

$xmlData = @file_get_contents($feedUrl, false, $context);

// Handle fetch failure
if ($xmlData === false) {
    if (file_exists($cacheFile)) {
        // Serve cached data if available
        echo file_get_contents($cacheFile);
    } else {
        echo json_encode(['error' => 'Unable to fetch live CISA feed.']);
    }
    exit;
}

// Convert XML to JSON
$xml = simplexml_load_string($xmlData, "SimpleXMLElement", LIBXML_NOCDATA);
if ($xml === false) {
    echo json_encode(['error' => 'Invalid XML received from CISA.']);
    exit;
}

$json = json_encode($xml, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

// Save to cache
file_put_contents($cacheFile, $json);

// Output the JSON
echo $json;
?>