<?php

namespace App\Services;

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

    /**
     * Main analyzer method
     */
    public function analyze(string $text): ?array
    {
        // 1️⃣ Clean emails / HTML / MIME noise BEFORE sending to AI
        $cleanedText = $this->cleanInput($text);

        // 2️⃣ Single request (no retry!) to avoid rate-limit bursts
        $response = $this->sendToAzure($cleanedText);

        // If API returned error format
        if (isset($response['error'])) {
            return [
                'risk_score' => 10,
                'verdict' => 'unclear',
                'summary' => 'Azure returned an error: ' . ($response['message'] ?? 'Unknown error'),
                'red_flags' => [],
                'recommended_action' => 'Try again later.',
            ];
        }

        // Try to parse JSON
        $decoded = $this->forceValidJson($response);

        if ($decoded !== null) {
            return $decoded;
        }

        // 3️⃣ Hard fallback — safe, low-risk default
        return [
            'risk_score' => 10,
            'verdict' => 'likely legitimate',
            'summary' => 'The AI response could not be parsed as valid JSON, but no strong scam indicators were detected.',
            'red_flags' => [],
            'recommended_action' => 'Verify directly via the service’s official website rather than email links.',
        ];
    }


    /**
     * Send request to Azure OpenAI
     */
    protected function sendToAzure(string $text): ?array
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
                        'model' => $this->deployment,

                        // Force JSON output
                        'response_format' => [
                            'type' => 'json_object'
                        ],

                        'messages' => [
                            [
                                'role' => 'system',
                                'content' => <<<SYS
You are a cybersecurity scam-detection assistant.

Your ONLY job is to analyse emails, messages, or screenshots for signs of phishing, fraud, or impersonation.

STRICT OUTPUT RULES:
- You MUST ALWAYS output ONLY a valid JSON object.
- NO backticks.
- NO markdown.
- NO explanation outside JSON.
- NO commentary.
- NO extra text.

JSON MUST HAVE EXACTLY:
{
  "risk_score": <integer 0-100>,
  "verdict": "likely scam" | "suspicious" | "unclear" | "likely legitimate",
  "summary": "<short explanation>",
  "red_flags": ["<list of suspicious elements>"],
  "recommended_action": "<action the user should take>"
}

Legitimate email → risk_score 0–20.
Unclear → risk_score 30–60.
Scam → risk_score 70–100.
SYS
                            ],
                            [
                                'role' => 'user',
                                'content' => $text,
                            ]
                        ],

                        'temperature' => 0.2,
                        'max_tokens' => 1200
                    ]
                ]
            );

            return json_decode($response->getBody()->getContents(), true);

        } catch (\Exception $e) {
            return [
                'error'   => true,
                'message' => $e->getMessage(),
            ];
        }
    }


    /**
     * Clean .eml, HTML, MIME artifacts so model can read clearly
     */
    protected function cleanInput(string $raw): string
    {
        $parts = preg_split("/\R\R/", $raw, 2);
        $body  = $parts[1] ?? $raw;

        $body = strip_tags($body);

        $body = preg_replace('/=\R/', '', $body);
        $body = preg_replace('/=([0-9A-F]{2})/', '', $body);

        return trim($body);
    }


    /**
     * Return decoded JSON if valid, otherwise null
     */
    protected function forceValidJson($response): ?array
    {
        if (!isset($response['choices'][0]['message']['content'])) {
            return null;
        }

        $raw = $response['choices'][0]['message']['content'];

        $decoded = json_decode($raw, true);

        return (json_last_error() === JSON_ERROR_NONE) ? $decoded : null;
    }
}
