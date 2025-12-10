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
                                'content' => <<<SYS
                                    You are a cybersecurity and fraud-analysis assistant.

                                    Your ONLY job is to analyse emails/messages and decide how likely they are to be a scam or phishing.

                                    You MUST ALWAYS respond with a single valid JSON object, with no extra text, no explanations, no markdown.

                                    The JSON MUST have exactly these fields:
                                    {
                                    "risk_score": <integer 0-100>,
                                    "verdict": "<one of: 'likely scam', 'suspicious', 'unclear', 'likely legitimate'>",
                                    "summary": "<short one-paragraph explanation>",
                                    "red_flags": ["<list of specific red flags, may be empty>"],
                                    "recommended_action": "<what the user should do next>"
                                    }

                                    Rules:
                                    - If the email is clearly legitimate (for example, a real Microsoft invoice with valid domains and clean headers), use a low risk_score like 0–20 and verdict "likely legitimate".
                                    - If it looks clearly malicious or phishing, use risk_score 70–100 and verdict "likely scam".
                                    - If it is somewhat suspicious but not clearly malicious, use verdict "suspicious".
                                    - If there is not enough information to decide, use verdict "unclear" but still return a numeric risk_score.
                                    - Never include any text outside the JSON. No backticks. No comments. No field names other than the ones specified above.
                                    SYS
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
