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
        // Clean EML / HTML / MIME noise
        $cleaned = $this->cleanInput($text);

        // Single, simple call
        $response = $this->sendToAzure($cleaned);

        // Azure error? Show it directly.
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

                            // SYSTEM ROLE — tuned scam analysis instructions
                            [
                                'role' => 'system',
                                'content' =>
                                    "You are a cybersecurity scam-detection assistant.\n\n" .
                                    "Your job is to analyse emails/messages and rate how likely they are to be a scam or phishing.\n\n" .
                                    "You MUST always respond in this exact simple text format:\n" .
                                    "Scam Analysis Result\n\n" .
                                    "Verdict: <likely scam | suspicious | unclear | likely legitimate>\n" .
                                    "Risk Score: <0-100>\n" .
                                    "Summary: <short explanation>\n" .
                                    "Red Flags:\n" .
                                    " - <item 1>\n" .
                                    " - <item 2>\n" .
                                    "Recommended Action: <what the user should do>\n\n" .
                                    "SCORING GUIDELINES:\n" .
                                    "- 0–20: clearly legitimate (normal transactional email, no strong red flags).\n" .
                                    "- 21–40: mostly legitimate but with minor concerns (still \"likely legitimate\").\n" .
                                    "- 41–69: \"suspicious\" or \"unclear\" — some red flags but not obviously a scam.\n" .
                                    "- 70–100: clearly malicious or very high risk (\"likely scam\").\n\n" .
                                    "IMPORTANT CALIBRATION:\n" .
                                    "- DO NOT mark an email as high risk ONLY because it contains:\n" .
                                    "  * an attached PDF invoice, or\n" .
                                    "  * tracking links (e.g. safelinks.protection.outlook.com, safelink.emails.azure.net), or\n" .
                                    "  * standard branding/language from large providers.\n" .
                                    "- Those can be normal for legitimate invoices from Microsoft or other large vendors.\n" .
                                    "- Treat these as red flags ONLY when combined with other serious issues like domain mismatch, fake login pages, obvious spoofing, or threats.\n\n" .
                                    "WHEN TO USE \"LIKELY LEGITIMATE\":\n" .
                                    "- The message looks like a normal invoice or notification from a known provider.\n" .
                                    "- Language, formatting and structure look professional and consistent.\n" .
                                    "- No clear domain mismatch or obvious spoofed links.\n\n" .
                                    "WHEN TO USE \"LIKELY SCAM\":\n" .
                                    "- Sender domain or URLs clearly do NOT match the claimed organisation.\n" .
                                    "- Strong urgency, threats, or pressure to act immediately.\n" .
                                    "- Requests to enter credentials, payment details, or sensitive info via unfamiliar pages.\n\n" .
                                    "GENERAL RULE:\n" .
                                    "- Be willing to classify emails as \"likely legitimate\" with a low risk score when they look like normal system-generated mail from known services.\n" .
                                    "- Reserve high scores (70+) for emails that show strong phishing/scam patterns.\n" .
                                    "- Keep your explanation and red flags practical and easy to understand by non-technical users.\n"
                            ],

                            // USER ROLE — actual content
                            [
                                'role' => 'user',
                                'content' => "Analyze this email or message and follow the format exactly:\n\n" . $text
                            ]
                        ],

                        'temperature' => 0.2,
                        'max_tokens'  => 800,
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
     * Clean .eml, HTML, MIME artifacts
     */
    protected function cleanInput(string $raw): string
    {
        // Remove MIME headers (body begins after first blank line)
        $parts = preg_split("/\R\R/", $raw, 2);
        $body  = $parts[1] ?? $raw;

        // Remove HTML tags
        $body = strip_tags($body);

        // Remove quoted-printable soft line breaks (=)
        $body = preg_replace('/=\R/', '', $body);
        $body = preg_replace('/=([0-9A-F]{2})/', '', $body);

        // Normalize whitespace
        return trim($body);
    }
}
