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
                                    "You MUST always respond in this exact simple text structure:\n" .
                                    "Scam Analysis Result\n\n" .
                                    "Verdict: <likely scam | suspicious | unclear | likely legitimate>\n" .
                                    "Risk Score: <0-100>\n" .
                                    "Summary: <short explanation>\n" .
                                    "Red Flags:\n" .
                                    " - <item 1>\n" .
                                    " - <item 2>\n" .
                                    "Recommended Action: <what the user should do>\n\n" .

                                    "SCORING RANGES:\n" .
                                    "- 0–20 → clearly legitimate.\n" .
                                    "- 21–40 → minor concerns but still \"likely legitimate\".\n" .
                                    "- 41–69 → \"suspicious\" or \"unclear\".\n" .
                                    "- 70–100 → \"likely scam\" (strong phishing indicators).\n\n" .

                                    "URL ANALYSIS RULES:\n" .
                                    "- ALWAYS review any URLs listed under 'Detected URLs'.\n" .
                                    "- If a URL domain does NOT match the claimed sender (e.g. email claims to be from Meta but URLs are hosted on *.pages.dev), treat this as a MAJOR red flag and increase the risk score.\n" .
                                    "- High-risk URL patterns:\n" .
                                    "  * Non-official domains for big brands (Meta, Microsoft, banks).\n" .
                                    "  * Random hosting like pages.dev, weebly, godaddysites, blogspot, etc. used for login or appeals.\n" .
                                    "  * URL parameters that suggest credential capture, payment collection, or account verification on unknown domains.\n\n" .

                                    "MICROSOFT / LARGE PROVIDER CALIBRATION:\n" .
                                    "- Treat emails that look like standard invoices or notifications from large providers (e.g. Microsoft) as LOW RISK **when**:\n" .
                                    "  * The majority of URLs are to official domains such as microsoft.com, office.com, outlook.com, windows.com, live.com,\n" .
                                    "    login.microsoftonline.com, aka.ms, protection.outlook.com, safelinks.protection.outlook.com, safelink.emails.azure.net.\n" .
                                    "  * The structure, branding and language look like normal system-generated email.\n" .
                                    "  * There are no clearly unrelated external domains in the URLs.\n" .
                                    "- In such cases, you should generally use:\n" .
                                    "  * Verdict: \"likely legitimate\"\n" .
                                    "  * Risk Score: in the 0–30 range (unless there is a very strong contradiction).\n" .
                                    "- DO NOT heavily penalise:\n" .
                                    "  * The presence of a PDF invoice attachment.\n" .
                                    "  * Future-dated billing periods (common for upcoming or current subscriptions).\n" .
                                    "  * Microsoft SafeLinks or long tracking URLs when the underlying destination is still a Microsoft domain.\n\n" .

                                    "WHEN TO ESCALATE TO HIGH RISK (70+):\n" .
                                    "- Strong domain mismatch (e.g., Meta email with pages.dev or random domain for login/appeal).\n" .
                                    "- Requests to log in, pay, or submit credentials on an unrelated domain.\n" .
                                    "- Strong urgency, threats, or extortion-style language.\n" .
                                    "- Obvious spoofing or fake login pages.\n\n" .

                                    "GENERAL RULE:\n" .
                                    "- Be willing to classify clearly normal-looking transaction emails from Microsoft or other big providers as 'likely legitimate' with a low score.\n" .
                                    "- Reserve high scores for emails with genuinely dangerous indicators, especially around URLs and sender/domain mismatch.\n"
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
