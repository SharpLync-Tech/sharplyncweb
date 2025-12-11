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
    public function analyze(string $text): ?string
    {
        // Clean EML / HTML / MIME noise
        $cleaned = $this->cleanInput($text);

        // Send once — simple, clean, and reliable
        $response = $this->sendToAzure($cleaned);

        // If Azure error → return it directly
        if (isset($response['error'])) {
            return "ERROR FROM AZURE:\n" . json_encode($response['error'], JSON_PRETTY_PRINT);
        }

        // If no normal response
        if (!isset($response['choices'][0]['message']['content'])) {
            return "NO CONTENT RETURNED:\n" . json_encode($response, JSON_PRETTY_PRINT);
        }

        // Return model text directly (simple and reliable)
        return $response['choices'][0]['message']['content'];
    }


    /**
     * Send request to Azure OpenAI
     */
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

                            // SYSTEM ROLE — Scam analysis instructions
                            [
                                'role' => 'system',
                                'content' =>
                                    "You are a cybersecurity scam-detection assistant.\n\n".
                                    "Analyze the message and produce a SIMPLE structured response.\n\n".
                                    "FORMAT YOUR ANSWER EXACTLY LIKE THIS:\n".
                                    "Verdict: <likely scam | suspicious | unclear | likely legitimate>\n".
                                    "Risk Score: <0-100>\n".
                                    "Summary: <short explanation>\n".
                                    "Red Flags:\n".
                                    " - <item 1>\n".
                                    " - <item 2>\n".
                                    "Recommended Action: <what the user should do>\n\n".
                                    "NOTES:\n".
                                    "- No JSON.\n".
                                    "- No markdown.\n".
                                    "- No bullet points except under Red Flags.\n".
                                    "- Keep it clean and simple.\n"
                            ],

                            // USER ROLE — actual content
                            [
                                'role' => 'user',
                                'content' => "Analyze this email/message:\n\n" . $text
                            ]
                        ],

                        'temperature' => 0.2,
                        'max_tokens' => 800,
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


    /**
     * Clean .eml, HTML, MIME artifacts
     */
    protected function cleanInput(string $raw): string
    {
        // Remove MIME headers (body begins after first blank line)
        $parts = preg_split("/\R\R/", $raw, 2);
        $body  = $parts[1] ?? $raw;

        // Remove HTML tags
        $body = strip_tags($body);

        // Remove quoted-printable soft line breaks (=)
        $body = preg_replace('/=\R/', '', $body);
        $body = preg_replace('/=([0-9A-F]{2})/', '', $body);

        // Normalize whitespace
        return trim($body);
    }
}
