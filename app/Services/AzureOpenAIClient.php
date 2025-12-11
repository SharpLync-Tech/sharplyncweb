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
     * Public method called by controller
     */
    public function analyze(string $text): ?array
    {
        // Clean input (remove HTML, MIME, etc.)
        $cleaned = $this->cleanInput($text);

        // Attempt 1
        $response1 = $this->sendToAzure($cleaned);
        $decoded1  = $this->parseAzureJson($response1);

        if ($decoded1 !== null) {
            return $decoded1;
        }

        // Attempt 2 (reminder to output JSON only)
        $retryText = "Return ONLY JSON. No markup.\n\n" . $cleaned;
        $response2 = $this->sendToAzure($retryText);
        $decoded2  = $this->parseAzureJson($response2);

        if ($decoded2 !== null) {
            return $decoded2;
        }

        // FINAL HARD FALLBACK
        return [
            'risk_score' => 10,
            'verdict' => 'likely legitimate',
            'summary' => 'No valid JSON returned and email appears low risk.',
            'red_flags' => [],
            'recommended_action' => 'Verify directly via the official service website.',
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
                        // Force JSON output
                        'response_format' => [
                            'type' => 'json_object'
                        ],

                        'messages' => [
                            [
                                'role' => 'system',
                                'content' =>
                                    "You are a cybersecurity scam-detection assistant.\n\n" .
                                    "Return ONLY a valid JSON object. NO markdown. NO commentary.\n\n" .
                                    "JSON MUST be exactly:\n" .
                                    "{\n" .
                                    "  \"risk_score\": <integer>,\n" .
                                    "  \"verdict\": \"likely scam\" | \"suspicious\" | \"unclear\" | \"likely legitimate\",\n" .
                                    "  \"summary\": \"<short explanation>\",\n" .
                                    "  \"red_flags\": [\"<item1>\", \"<item2>\"] ,\n" .
                                    "  \"recommended_action\": \"<short guidance>\"\n" .
                                    "}\n"
                            ],
                            [
                                'role' => 'user',
                                'content' =>
                                    "Analyze this email/message and return ONLY the JSON structure.\n\n" .
                                    $text
                            ]
                        ],

                        'temperature' => 0.0,
                        'max_tokens' => 1200
                    ]
                ]
            );

            return json_decode($response->getBody()->getContents(), true);

        } catch (\Exception $e) {
            // Return exception in a structured way
            return [
                'error' => [
                    'message' => $e->getMessage(),
                    'type' => 'exception'
                ]
            ];
        }
    }


    /**
     * Clean raw email input (.eml, HTML, MIME artifacts)
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
     * Diagnostic JSON parser — exposes ALL Azure issues
     */
    protected function parseAzureJson(array $response): ?array
    {
        // Azure returned an error
        if (isset($response['error'])) {
            return [
                'risk_score' => 0,
                'verdict' => 'azure_error',
                'summary' => 'Azure error: ' . json_encode($response['error']),
                'red_flags' => [],
                'recommended_action' => 'Check Azure quota, filters, or logs.',
            ];
        }

        // No content returned → show raw response
        if (!isset($response['choices'][0]['message']['content'])) {
            return [
                'risk_score' => 0,
                'verdict' => 'no_content',
                'summary' => 'Azure returned no content: ' . json_encode($response),
                'red_flags' => [],
                'recommended_action' => 'Inspect Azure output.',
            ];
        }

        $raw = $response['choices'][0]['message']['content'];

        // Try decode JSON
        $decoded = json_decode($raw, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        // Invalid JSON — return raw text for debugging
        return [
            'risk_score' => 0,
            'verdict' => 'invalid_json',
            'summary' => 'Model returned invalid JSON: ' . $raw,
            'red_flags' => [],
            'recommended_action' => 'Adjust prompt or parsing.',
        ];
    }
}
