<?php

namespace App\ScamCheck\Services;

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
        // Extract URLs + clean EML/HTML
        $cleaned = $this->cleanInputAndExtractUrls($text);

        // Send to Azure
        $response = $this->sendToAzure($cleaned);

        if (isset($response['error'])) {
            return "ERROR FROM AZURE:\n" . json_encode($response['error'], JSON_PRETTY_PRINT);
        }

        if (!isset($response['choices'][0]['message']['content'])) {
            return "NO CONTENT RETURNED:\n" . json_encode($response, JSON_PRETTY_PRINT);
        }

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

                            // SYSTEM ROLE — tuned with URL + Microsoft calibration
                            [
                                'role' => 'system',
                                'content' =>
                                "You are a cybersecurity scam-detection assistant.\n\n" .
                                "You MUST ALWAYS respond ONLY in valid JSON. No text before or after. No explanations.\n\n" .
                                "REQUIRED JSON FORMAT:\n" .
                                "{\n" .
                                "  \"verdict\": \"likely scam | suspicious | unclear | likely legitimate\",\n" .
                                "  \"risk_score\": <0-100>,\n" .
                                "  \"summary\": \"short explanation\",\n" .
                                "  \"red_flags\": [\"item 1\", \"item 2\"],\n" .
                                "  \"recommended_action\": \"what the user should do\"\n" .
                                "}\n\n" .
                                "STRICT RULES:\n" .
                                "- Always return VALID JSON.\n" .
                                "- Never include headings or markdown.\n" .
                                "- Never add fields that are not in the schema.\n" .
                                "- Never change field names.\n" .
                                "- Never output narrative text outside JSON.\n" .
                                "- Arrays must always be JSON arrays.\n" .
                                "- Strings must be valid JSON strings.\n\n" .
                                "SCORING RULES:\n" .
                                "- 0–20 → clearly legitimate.\n" .
                                "- 21–40 → minor concerns (still likely legitimate).\n" .
                                "- 41–69 → suspicious or unclear.\n" .
                                "- 70–100 → likely scam.\n\n" .
                                "URL ANALYSIS RULES:\n" .
                                "- If ANY URL domain doesn't match the claimed sender, major red flag.\n" .
                                "- Random hosting (pages.dev, weebly, godaddysites, etc.) increases risk.\n" .
                                "- Official Microsoft domains are low risk (microsoft.com, office.com, aka.ms, safelinks.*).\n\n" .
                                "When ready, return ONLY the JSON object.\n"
                            ],

                            [
                                'role' => 'user',
                                'content' => "Analyze this email or message and follow the format exactly:\n\n" . $text
                            ]
                        ],

                        'temperature' => 0.2,
                        'max_tokens'  => 900,
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
     * Clean email AND extract URLs BEFORE stripping HTML
     */
    protected function cleanInputAndExtractUrls(string $raw): string
    {
        $urls = [];

        // 1) Extract from href=""
        preg_match_all('/href=["\']([^"\']+)["\']/i', $raw, $matches1);
        if (!empty($matches1[1])) {
            $urls = array_merge($urls, $matches1[1]);
        }

        // 2) Extract from plain text
        preg_match_all('/https?:\/\/[^\s<>"\'()]+/i', $raw, $matches2);
        if (!empty($matches2[0])) {
            $urls = array_merge($urls, $matches2[0]);
        }

        $urls = array_values(array_unique($urls));

        $urlList = "";
        if (!empty($urls)) {
            $urlList .= "Detected URLs:\n";
            foreach ($urls as $u) {
                $urlList .= $u . "\n";
            }
            $urlList .= "\n";
        }

        // 3) Strip MIME headers
        $parts = preg_split("/\R\R/", $raw, 2);
        $body  = $parts[1] ?? $raw;

        // 4) Strip HTML after URL extraction
        $body = strip_tags($body);

        // 5) Clean quoted-printable artefacts
        $body = preg_replace('/=\R/', '', $body);
        $body = preg_replace('/=([0-9A-F]{2})/', '', $body);

        $body = trim($body);

        return $body . "\n\n" . $urlList;
    }
}
