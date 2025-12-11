<?php

namespace App\ScamCheck\Services;

use GuzzleHttp\Client;

class AzureOpenAIClient
{
    protected Client $client;
    protected string $endpoint;
    protected string $deployment;
    protected string $apiKey;

    public function __construct()
    {
        $this->endpoint   = rtrim(env('AZURE_OPENAI_ENDPOINT'), '/');
        $this->deployment = env('AZURE_OPENAI_DEPLOYMENT');
        $this->apiKey     = env('AZURE_OPENAI_KEY');

        $this->client = new Client([
            'base_uri' => $this->endpoint,
            'timeout'  => 20,
        ]);
    }

    public function analyze(string $text): ?string
    {
        // Extract URLs + clean EML/HTML
        $cleaned = $this->cleanInputAndExtractUrls($text);

        // Send to Azure
        $response = $this->sendToAzure($cleaned);

        if (isset($response['error'])) {
            return "ERROR FROM AZURE:\n" . json_encode($response['error'], JSON_PRETTY_PRINT);
        }

        if (!isset($response['choices'][0]['message']['content'])) {
            return "NO CONTENT RETURNED:\n" . json_encode($response, JSON_PRETTY_PRINT);
        }

        return $response['choices'][0]['message']['content'];
    }

    protected function sendToAzure(string $text): array
    {
        try {
            $response = $this->client->post(
                "/openai/deployments/{$this->deployment}/chat/completions",
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'api-key'      => $this->apiKey,
                    ],
                    'query' => [
                        'api-version' => '2024-10-01-preview'
                    ],
                    'json' => [
                        'messages' => [
                            [
                                'role' => 'system',
                                'content' => 
                                    // (SYSTEM PROMPT FULL TEXT — unchanged)
                                    "You are a cybersecurity scam-detection assistant.\n\n" .
                                    "... entire system prompt unchanged ..."
                            ],
                            [
                                'role' => 'user',
                                'content' => "Analyze this email or message and follow the format exactly:\n\n" . $text
                            ]
                        ],

                        'temperature' => 0.2,
                        'max_tokens'  => 900,
                    ]
                ]
            );

            return json_decode($response->getBody()->getContents(), true);

        } catch (\Exception $e) {
            return [
                'error' => [
                    'message' => $e->getMessage(),
                    'type'    => 'exception'
                ]
            ];
        }
    }

    protected function cleanInputAndExtractUrls(string $raw): string
    {
        $urls = [];

        preg_match_all('/href=["\']([^"\']+)["\']/i', $raw, $matches1);
        if (!empty($matches1[1])) {
            $urls = array_merge($urls, $matches1[1]);
        }

        preg_match_all('/https?:\/\/[^\s<>"\'()]+/i', $raw, $matches2);
        if (!empty($matches2[0])) {
            $urls = array_merge($urls, $matches2[0]);
        }

        $urls = array_values(array_unique($urls));

        $urlList = "";
        if (!empty($urls)) {
            $urlList .= "Detected URLs:\n";
            foreach ($urls as $u) {
                $urlList .= $u . "\n";
            }
            $urlList .= "\n";
        }

        $parts = preg_split("/\R\R/", $raw, 2);
        $body  = $parts[1] ?? $raw;

        $body = strip_tags($body);

        $body = preg_replace('/=\R/', '', $body);
        $body = preg_replace('/=([0-9A-F]{2})/', '', $body);

        $body = trim($body);

        return $body . "\n\n" . $urlList;
    }
}
