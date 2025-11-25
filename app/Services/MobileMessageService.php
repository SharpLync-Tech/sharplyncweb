<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MobileMessageService
{
    protected string $baseUrl;
    protected ?string $username;
    protected ?string $password;
    protected string $defaultSender;

    public function __construct()
    {
        // These will come from Azure App Settings (not a .env file)
        $this->baseUrl       = rtrim(env('MOBILEMSG_BASE_URL', 'https://api.mobilemessage.com.au'), '/');
        $this->username      = env('MOBILEMSG_USERNAME');
        $this->password      = env('MOBILEMSG_PASSWORD');
        $this->defaultSender = env('MOBILEMSG_SENDER', 'SharpLync'); // you can change this in Azure
    }

    /**
     * Send a single SMS message via MobileMessage.
     *
     * @param  string      $to         Recipient mobile (e.g. 04XXXXXXXX or +614XXXXXXXX)
     * @param  string      $message    SMS body
     * @param  string|null $customRef  Optional tracking reference
     * @param  bool        $unicode    Whether to enable unicode (emojis, etc.)
     * @param  string|null $sender     Optional override sender ID
     * @return array                   Decoded JSON response
     *
     * @throws \RuntimeException       If credentials missing or API call fails
     */
    public function sendMessage(
        string $to,
        string $message,
        ?string $customRef = null,
        bool $unicode = false,
        ?string $sender = null
    ): array {
        if (empty($this->username) || empty($this->password)) {
            throw new \RuntimeException('MobileMessage credentials are not configured (MOBILEMSG_USERNAME / MOBILEMSG_PASSWORD).');
        }

        $senderToUse = $sender ?: $this->defaultSender;

        $payload = [
            'enable_unicode' => $unicode,
            'messages' => [
                [
                    'to'      => $to,
                    'message' => $message,
                    'sender'  => $senderToUse,
                ],
            ],
        ];

        if ($customRef) {
            $payload['messages'][0]['custom_ref'] = $customRef;
        }

        if ($unicode) {
            $payload['messages'][0]['unicode'] = true;
        }

        $url = $this->baseUrl . '/v1/messages';

        try {
            $response = Http::withBasicAuth($this->username, $this->password)
                ->acceptJson()
                ->asJson()
                ->post($url, $payload);

            if (! $response->successful()) {
                Log::error('MobileMessage sendMessage failed', [
                    'url'      => $url,
                    'status'   => $response->status(),
                    'body'     => $response->body(),
                    'payload'  => $payload,
                ]);

                throw new \RuntimeException(
                    'MobileMessage API error: HTTP ' . $response->status()
                    . ' - ' . $response->body()
                );
            }

            $json = $response->json() ?? [];

            Log::info('MobileMessage sendMessage success', [
                'to'      => $to,
                'sender'  => $senderToUse,
                'payload' => $payload,
                'response'=> $json,
            ]);

            return $json;
        } catch (\Throwable $e) {
            Log::error('MobileMessage sendMessage exception', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            throw new \RuntimeException('Failed to send SMS via MobileMessage: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Get account credit balance (for later: Admin portal widget).
     */
    public function getCreditBalance(): ?int
    {
        if (empty($this->username) || empty($this->password)) {
            throw new \RuntimeException('MobileMessage credentials are not configured (MOBILEMSG_USERNAME / MOBILEMSG_PASSWORD).');
        }

        $url = $this->baseUrl . '/v1/account';

        try {
            $response = Http::withBasicAuth($this->username, $this->password)
                ->acceptJson()
                ->get($url);

            if (! $response->successful()) {
                Log::error('MobileMessage getCreditBalance failed', [
                    'url'    => $url,
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);

                return null;
            }

            $data = $response->json() ?? [];

            return $data['credit_balance'] ?? null;
        } catch (\Throwable $e) {
            Log::error('MobileMessage getCreditBalance exception', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    /**
     * Get details of a single message by message_id (optional, for debugging/admin).
     */
    public function getMessageById(string $messageId): ?array
    {
        if (empty($this->username) || empty($this->password)) {
            throw new \RuntimeException('MobileMessage credentials are not configured (MOBILEMSG_USERNAME / MOBILEMSG_PASSWORD).');
        }

        $url = $this->baseUrl . '/v1/messages';

        try {
            $response = Http::withBasicAuth($this->username, $this->password)
                ->acceptJson()
                ->get($url, [
                    'message_id' => $messageId,
                ]);

            if (! $response->successful()) {
                Log::error('MobileMessage getMessageById failed', [
                    'url'      => $url,
                    'status'   => $response->status(),
                    'body'     => $response->body(),
                    'messageId'=> $messageId,
                ]);

                return null;
            }

            $data = $response->json() ?? [];

            return $data;
        } catch (\Throwable $e) {
            Log::error('MobileMessage getMessageById exception', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return null;
        }
    }
}
