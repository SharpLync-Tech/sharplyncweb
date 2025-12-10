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
                                'content' => "This model is used for cybersecurity and fraud detection. The assistant is allowed to process harmful, malicious, scam, phishing, or criminal content ONLY for the purpose of analysis, classification, and safety reporting. The assistant must not provide instructions to perform harmful activities. The assistant's purpose is defensive and evaluative."
                            ],
                            [
                                'role' => 'user',
                                'content' => $text,
                            ]
                        ],
                        'temperature' => 0.2,
                        'max_tokens' => 300
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
