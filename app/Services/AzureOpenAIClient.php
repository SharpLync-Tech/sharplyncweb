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
                "/openai/v1/chat/completions",
                [
                    'headers' => [
                        'Content-Type'  => 'application/json',
                        'api-key'       => $this->apiKey,
                    ],
                    'json' => [
                        'model' => $this->deployment,
                        'messages' => [
                            [
                                'role' => 'system',
                                'content' => "You are a security analysis assistant. You are allowed to receive harmful, scam-like, phishing, or malicious content ONLY for the purpose of analyzing and identifying safety risks. 
                                              You must not generate or encourage harmful actions. Your role is strictly to classify, explain, and assess risk to help protect users."

                            ],
                            [
                                'role' => 'user',
                                'content' => $text,
                            ]
                        ],
                        'max_tokens' => 300,
                        'temperature' => 0.2,
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
