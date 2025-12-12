<?php

namespace App\ScamCheck\Services;

use Illuminate\Support\Facades\Http;

class AzureVisionOCR
{
    protected string $endpoint;
    protected string $key;

    public function __construct()
    {
        $this->endpoint = rtrim(config('services.azure_vision.endpoint'), '/');
        $this->key      = config('services.azure_vision.key');
    }

    public function extractText(string $imagePath): string
    {
        $imageBinary = file_get_contents($imagePath);

        // 1️⃣ Submit image for OCR (RAW BYTES — NOT JSON)
        $response = Http::withHeaders([
            'Ocp-Apim-Subscription-Key' => $this->key,
        ])->withBody($imageBinary, 'application/octet-stream')
          ->post("{$this->endpoint}/vision/v3.2/read/analyze");

        if (!$response->successful()) {
            return '';
        }

        // 2️⃣ Azure returns async operation URL
        $operationUrl = $response->header('Operation-Location');

        if (!$operationUrl) {
            return '';
        }

        // 3️⃣ Poll for OCR result
        for ($i = 0; $i < 6; $i++) {
            sleep(1);

            $result = Http::withHeaders([
                'Ocp-Apim-Subscription-Key' => $this->key,
            ])->get($operationUrl);

            if (!$result->successful()) {
                continue;
            }

            $data = $result->json();

            if (($data['status'] ?? '') === 'succeeded') {
                return $this->parseText($data);
            }

            if (($data['status'] ?? '') === 'failed') {
                return '';
            }
        }

        return '';
    }

    /**
     * Extract readable text safely
     */
    protected function parseText(array $data): string
    {
        $text = [];

        foreach ($data['analyzeResult']['readResults'] ?? [] as $page) {
            foreach ($page['lines'] ?? [] as $line) {
                $text[] = $line['text'];
            }
        }

        // ✅ Force UTF-8 safety (extra protection)
        return mb_convert_encoding(
            implode("\n", $text),
            'UTF-8',
            'UTF-8'
        );
    }
}
