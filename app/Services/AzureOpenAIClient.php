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

                            // SYSTEM ROLE — tuned with URL analysis
                            [
                                'role' => 'system',
                                'content' =>
                                    "You are a cybersecurity scam-detection assistant.\n\n" .
                                    "You MUST always respond in this exact simple text structure:\n" .
                                    "Scam Analysis Result\n\n" .
                                    "Verdict: <likely scam | suspicious | unclear | likely legitimate>\n" .
                                    "Risk Score: <0-100>\n" .
                                    "Summary: <short explanation>\n" .
                                    "Red Flags:\n" .
                                    " - <item 1>\n" .
                                    " - <item 2>\n" .
                                    "Recommended Action: <what the user should do>\n\n" .

                                    // URL analysis focus
                                    "URL Analysis Rules:\n" .
                                    "- ALWAYS analyze every URL under 'Detected URLs'.\n" .
                                    "- If a URL domain does NOT match the claimed sender (e.g., Meta but domain is *.pages.dev), treat as a MAJOR red flag.\n" .
                                    "- Non-official login pages, suspicious hosting (pages.dev, weebly, godaddysites, blogspot, tinyurl, bit.ly, etc.) should raise the risk score.\n" .
                                    "- Microsoft SafeLinks (safelinks.protection.outlook.com, safelink.emails.azure.net) are NORMAL and NOT a scam indicator by themselves.\n" .
                                    "- High-risk indicators include:\n" .
                                    "  * domain mismatch\n" .
                                    "  * obfuscated URLs\n" .
                                    "  * links requiring credential login\n" .
                                    "  * unusual TLDs\n" .
                                    "  * recently created domains (guess based on appearance)\n\n" .

                                    "SCORING:\n" .
                                    "- 0–20 → likely legitimate\n" .
                                    "- 21–40 → minor concerns but mostly legitimate\n" .
                                    "- 41–69 → suspicious / unclear\n" .
                                    "- 70–100 → likely scam\n\n" .

                                    "Be decisive. If the URLs strongly indicate phishing, score HIGH."
                            ],

                            [
                                'role' => 'user',
                                'content' => "Analyze this email or message:\n\n" . $text
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
        $original = $raw;

        // -----------------------------------
        // 1. Extract URLs from HTML & text
        // -----------------------------------
        $urls = [];

        // From href=""
        preg_match_all('/href=["\']([^"\']+)["\']/i', $raw, $matches1);
        if (!empty($matches1[1])) {
            $urls = array_merge($urls, $matches1[1]);
        }

        // From raw plain text URLs
        preg_match_all('/https?:\/\/[^\s<>"\'()]+/i', $raw, $matches2);
        if (!empty($matches2[0])) {
            $urls = array_merge($urls, $matches2[0]);
        }

        // Unique + clean URLs
        $urls = array_values(array_unique($urls));

        $urlList = "";
        if (!empty($urls)) {
            $urlList .= "Detected URLs:\n";
            foreach ($urls as $u) {
                $urlList .= $u . "\n";
            }
            $urlList .= "\n";
        }

        // -----------------------------------
        // 2. Strip MIME headers (get body)
        // -----------------------------------
        $parts = preg_split("/\R\R/", $raw, 2);
        $body  = $parts[1] ?? $raw;

        // -----------------------------------
        // 3. Remove HTML tags AFTER extracting URLs
        // -----------------------------------
        $body = strip_tags($body);

        // -----------------------------------
        // 4. Clean quoted-printable soft breaks
        // -----------------------------------
        $body = preg_replace('/=\R/', '', $body);
        $body = preg_replace('/=([0-9A-F]{2})/', '', $body);

        // Normalize whitespace
        $body = trim($body);

        // -----------------------------------
        // 5. Append extracted URLs at the end
        // -----------------------------------
        return $body . "\n\n" . $urlList;
    }
}
