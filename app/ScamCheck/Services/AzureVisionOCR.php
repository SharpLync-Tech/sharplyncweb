<?php

namespace App\ScamCheck\Services;

use Illuminate\Support\Facades\Http;

class AzureVisionOCR
{
    protected string $endpoint;
    protected string $key;

    public function __construct()
    {
        $this->endpoint = rtrim(env('AZURE_VISION_ENDPOINT'), '/');
        $this->key      = env('AZURE_VISION_KEY');
    }

    public function extractText(string $imagePath): string
    {
        $response = Http::withHeaders([
            'Ocp-Apim-Subscription-Key' => $this->key,
            'Content-Type' => 'application/octet-stream',
        ])->post("{$this->endpoint}/vision/v3.2/read/analyze", file_get_contents($imagePath));

        if (!$response->successful()) {
            return '';
        }

        // Azure returns operation-location header
        $operationUrl = $response->header('Operation-Location');

        // Poll result (simple + safe)
        for ($i = 0; $i < 5; $i++) {
            sleep(1);

            $result = Http::withHeaders([
                'Ocp-Apim-Subscription-Key' => $this->key,
            ])->get($operationUrl);

            if (!$result->successful()) continue;

            $json = $result->json();

            if (($json['status'] ?? '') === 'succeeded') {
                return $this->parseText($json);
            }
        }

        return '';
    }

    protected function parseText(array $json): string
    {
        $text = [];

        foreach ($json['analyzeResult']['readResults'] ?? [] as $page) {
            foreach ($page['lines'] ?? [] as $line) {
                $text[] = $line['text'];
            }
        }

        return implode("\n", $text);
    }
}
