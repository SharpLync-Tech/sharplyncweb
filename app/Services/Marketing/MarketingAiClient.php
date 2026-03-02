<?php

namespace App\Services\Marketing;

use GuzzleHttp\Client;

class MarketingAiClient
{
    protected Client $client;
    protected string $endpoint;
    protected string $deployment;
    protected string $apiKey;

    public function __construct()
    {
        $this->endpoint = rtrim((string) env('AZURE_OPENAI_ENDPOINT'), '/');
        $this->deployment = (string) env('AZURE_OPENAI_DEPLOYMENT');
        $this->apiKey = (string) env('AZURE_OPENAI_KEY');

        $this->client = new Client([
            'base_uri' => $this->endpoint,
            'timeout' => 25,
        ]);
    }

    public function generateEmail(array $input): array
    {
        $brand = $input['brand'];
        $goal = $input['goal'];
        $audience = $input['audience'];
        $keyPoints = $input['key_points'] ?? '';
        $tone = $input['tone'];
        $ctaText = $input['cta_text'] ?? '';
        $ctaUrl = $input['cta_url'] ?? '';

        $system = implode("\n", [
            "You are a senior marketing copywriter and email designer.",
            "Return only valid JSON with keys: subject, preheader, html.",
            "HTML must be clean, email-safe, and use inline-friendly structure.",
            "Do not include <html>, <head>, or <body> tags.",
            "Use short paragraphs, bullets where helpful, and a clear CTA button if provided.",
            "Ensure the tone is followed precisely.",
            "Keep subject under 60 chars and preheader under 90 chars.",
        ]);

        $user = implode("\n", [
            "Brand: " . ($brand === 'sf' ? 'SharpFleet' : 'SharpLync'),
            "Goal: " . $goal,
            "Audience: " . $audience,
            "Tone: " . $tone,
            "Key points: " . ($keyPoints !== '' ? $keyPoints : 'None'),
            "CTA text: " . ($ctaText !== '' ? $ctaText : 'None'),
            "CTA URL: " . ($ctaUrl !== '' ? $ctaUrl : 'None'),
            "Return JSON only."
        ]);

        try {
            $response = $this->client->post(
                "/openai/deployments/{$this->deployment}/chat/completions",
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'api-key' => $this->apiKey,
                    ],
                    'query' => [
                        'api-version' => '2024-10-01-preview',
                    ],
                    'json' => [
                        'messages' => [
                            ['role' => 'system', 'content' => $system],
                            ['role' => 'user', 'content' => $user],
                        ],
                        'temperature' => 0.6,
                        'max_tokens' => 1200,
                        'response_format' => [
                            'type' => 'json_object',
                        ],
                    ],
                ]
            );

            $data = json_decode($response->getBody()->getContents(), true);

            $content = $data['choices'][0]['message']['content'] ?? '';
            $parsed = json_decode($content, true);

            if (!is_array($parsed)) {
                return [
                    'error' => 'AI returned invalid JSON.',
                    'raw' => $content,
                ];
            }

            return $parsed;
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }
    }
}
