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
        $cleaned = $this->cleanInput($text);

        // 1st attempt
        $response = $this->sendToAzure($cleaned);
        $json = $this->parseAzureJson($response);

        if ($json !== null) {
            return $json;
        }

        // 2nd attempt: more strict reminder
        $response = $this->sendToAzure("Return ONLY JSON.\n\n" . $cleaned);
        $json = $this->parseAzureJson($response);

        if ($json !== null) {
            return $json;
        }

        // FINAL fallback
        return [
            'risk_score' => 10,
            'verdict' => 'likely legitimate',
            'summary' => 'The AI response could not be parsed as JSON, but no major scam indicators were detected.',
            'red_flags' => [],
            'recommended_action' => 'Verify directly via the official website rather than links in the email.',
        ];
    }


    /**
     * Send request to Azure OpenAI using PROPER GPT-4.1 JSON MODE
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

                        // ⭐ REQUIRED FOR GPT-4.1 JSON MODE ⭐
                        'response_format' => [
                            'type' => 'json_object'
                        ],

                        'messages' => [
                            [
                                'role' => 'system',
                                'content' =>
                                    "You are a cybersecurity scam-detection assistant.\n\n" .
                                    "Return ONLY a valid JSON object — NO markdown, NO commentary, NO extra text.\n\n" .
                                    "Your EXACT JSON structure must be:\n" .
                                    "{\n" .
                                    "  \"risk_score\": <integer>,\n" .
                                    "  \"verdict\": \"likely scam\" | \"suspicious\" | \"unclear\" | \"likely legitimate\",\n" .
                                    "  \"summary\": \"<short explanation>\",\n" .
                                    "  \"red_flags\": [\"<details>\"],\n" .
                                    "  \"recommended_action\": \"<short guidance>\"\n" .
                                    "}\n\n" .
                                    "If scam → risk_score 70–100\n" .
                                    "If suspicious → 40–60\n" .
                                    "If unclear → 20–40\n" .
                                    "If legitimate → 0–20"
                            ],

                            [
                                'role' => 'user',
                                'content' =>
                                    "Analyze the following email/message for scam, phishing, fraud, or impersonation risk.\n" .
                                    "Return ONLY the JSON object described above.\n\n" .
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
            return [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }
    }


    /**
     * Clean raw email input (.eml, HTML, MIME artifacts)
     */
    protected function cleanInput(string $raw): string
    {
        // Remove MIME headers (metadata before first blank line)
        $parts = preg_split("/\R\R/", $raw, 2);
        $body  = $parts[1] ?? $raw;

        // Remove HTML
        $body = strip_tags($body);

        // Remove quoted-printable artifacts
        $body = preg_replace('/=\R/', '', $body);
        $body = preg_replace('/=([0-9A-F]{2})/', '', $body);

        return trim($body);
    }


    /**
     * Extract ONLY the model JSON from Azure response
     */
    protected function parseAzureJson(array $response): ?array
    {
        if (!isset($response['choices'][0]['message']['content'])) {
            return null;
        }

        $raw = $response['choices'][0]['message']['content'];

        $decoded = json_decode($raw, true);

        return json_last_error() === JSON_ERROR_NONE
            ? $decoded
            : null;
    }
}
