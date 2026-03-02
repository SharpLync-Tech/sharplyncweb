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
        $fluff = $input['fluff'] ?? 'none';
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
            "Detail level: " . $this->mapFluff($fluff),
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

            $raw = $response->getBody()->getContents();
            $data = json_decode($raw, true);

            if (!is_array($data)) {
                return [
                    'error' => 'AI returned non-JSON response.',
                    'raw' => $raw,
                ];
            }

            $content = $data['choices'][0]['message']['content'] ?? '';

            if ($content === '') {
                return [
                    'error' => 'AI returned empty content.',
                    'raw' => $data,
                ];
            }
            $parsed = json_decode($content, true);

            if (!is_array($parsed)) {
                return [
                    'error' => 'AI returned invalid JSON.',
                    'raw' => $content,
                ];
            }

            if (
                (!isset($parsed['subject']) || trim((string) $parsed['subject']) === '') &&
                (!isset($parsed['preheader']) || trim((string) $parsed['preheader']) === '') &&
                (!isset($parsed['html']) || trim((string) $parsed['html']) === '')
            ) {
                return [
                    'error' => 'AI returned empty fields.',
                    'raw' => $parsed,
                ];
            }

            return $parsed;
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }
    }

    private function mapFluff(string $fluff): string
    {
        if ($fluff === 'rich') {
            return 'Rich detail, more descriptive language, and fuller context.';
        }
        if ($fluff === 'light') {
            return 'Moderate detail, slightly expanded explanations.';
        }
        return 'Concise, minimal extra detail.';
    }
}
