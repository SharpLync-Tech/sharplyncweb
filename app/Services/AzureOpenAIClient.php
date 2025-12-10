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
            'timeout'  => 15,
        ]);
    }

    public function analyze(string $text): ?array
    {
        try {
            $response = $this->client->post(
                "/openai/deployments/{$this->deployment}/chat/completions",
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'api-key' => $this->apiKey,
                    ],
                    'query' => [
                        'api-version' => '2024-10-01-preview'
                    ],
                    'json' => [
                        'model' => $this->deployment,
                        'messages' => [
                            [
                                'role' => 'system',
                                'content' => "You are a cybersecurity scam-detection assistant. 
                                            Your job is to analyse messages, emails, or screenshots for signs of scams, phishing, impersonation, fraud, or malicious intent.

                                            You MUST reply in the following JSON format ONLY:

                                            {
                                            \"risk_score\": number (0–100),
                                            \"verdict\": \"Likely scam\" | \"Possibly scam\" | \"Unlikely scam\" | \"Safe\",
                                            \"summary\": \"Short summary of what the message is about\",
                                            \"red_flags\": [\"list of suspicious elements\"],
                                            \"recommendation\": \"What the user should do next\"
                                            }

                                            Do NOT include extra text. Output valid JSON only.

                                            You are allowed to process harmful or malicious content ONLY for defensive analysis. Never provide harmful instructions."
                            ],
                            [
                                'role' => 'user',
                                'content' => $text,
                            ]
                        ],
                        'temperature' => 0.2,
                        'max_tokens' => 800
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
}
