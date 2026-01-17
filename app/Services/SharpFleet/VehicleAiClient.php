<?php

namespace App\Services\SharpFleet;

use GuzzleHttp\Client;

class VehicleAiClient
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
            'timeout' => 15,
        ]);
    }

    public function suggestMakes(string $query, string $location): array
    {
        $prompt = "Return up to 12 makes/brands popular in {$location} that start with: \"{$query}\". " .
            "Include cars, trucks, heavy equipment (dozers, excavators, loaders), tractors, boats, jet skis, and ride-on mowers.";
        return $this->askList($prompt);
    }

    public function suggestModels(string $make, string $query, string $location): array
    {
        $prompt = "Return up to 12 models for the make \"{$make}\" popular in {$location} that start with: \"{$query}\". " .
            "If the make is a heavy equipment or machinery brand, include relevant machine model lines.";
        return $this->askList($prompt);
    }

    public function suggestTrims(string $make, string $model, string $query, string $location): array
    {
        $prompt = "Return up to 12 trim levels or variants for the {$make} {$model} popular in {$location} that start with: \"{$query}\". " .
            "For heavy equipment or machinery, return common series/variant names instead of passenger car trims.";
        return $this->askList($prompt);
    }

    protected function askList(string $prompt): array
    {
        if ($this->endpoint === '' || $this->deployment === '' || $this->apiKey === '') {
            return [];
        }

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
                            [
                                'role' => 'system',
                                'content' =>
                                    "You are a vehicle lookup assistant.\n" .
                                    "Always respond with valid JSON only. No prose.\n" .
                                    "JSON format:\n" .
                                    "{\n" .
                                    "  \"items\": [\"item1\", \"item2\"]\n" .
                                    "}\n" .
                                    "Rules:\n" .
                                    "- Items must be strings only.\n" .
                                    "- Return an empty array if no matches.\n" .
                                    "- Do not include duplicate items.\n",
                            ],
                            [
                                'role' => 'user',
                                'content' => $prompt,
                            ],
                        ],
                        'temperature' => 0.2,
                        'max_tokens' => 250,
                    ],
                ]
            );

            $data = json_decode($response->getBody()->getContents(), true);
            $content = $data['choices'][0]['message']['content'] ?? '';
            $json = json_decode($content, true);

            if (!is_array($json) || !isset($json['items']) || !is_array($json['items'])) {
                return [];
            }

            $items = array_values(array_unique(array_filter(array_map('strval', $json['items']))));
            return array_slice($items, 0, 12);
        } catch (\Throwable $e) {
            return [];
        }
    }
}
