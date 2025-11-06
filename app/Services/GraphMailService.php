<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Mail\Transport\Transport;
use Swift_Mime_SimpleMessage;
use Microsoft\Graph\Graph;

class GraphMailService extends Transport
{
    protected $accessToken;
    protected $graph;

    public function __construct()
    {
        $this->accessToken = $this->getAccessToken();

        $this->graph = new Graph();
        $this->graph->setAccessToken($this->accessToken);
    }

    /**
     * Get an access token using client credentials flow.
     */
    private function getAccessToken()
    {
        $tenantId = env('GRAPH_TENANT_ID');
        $clientId = env('GRAPH_CLIENT_ID');
        $clientSecret = env('GRAPH_CLIENT_SECRET');

        $client = new Client();

        $response = $client->post("https://login.microsoftonline.com/{$tenantId}/oauth2/v2.0/token", [
            'form_params' => [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'scope' => 'https://graph.microsoft.com/.default',
                'grant_type' => 'client_credentials',
            ],
        ]);

        $data = json_decode($response->getBody()->getContents(), true);
        return $data['access_token'];
    }

    /**
     *   Send the message through Microsoft Graph API.
     */
    public function send(Swift_Mime_SimpleMessage $message, &$failedRecipients = null)
    {
        $to = array_map(function ($addr) {
            return ['emailAddress' => ['address' => $addr]];
        }, array_keys($message->getTo() ?? []));

        $subject = $message->getSubject();
        $body = $message->getBody();

        $payload = [
            'message' => [
                'subject' => $subject,
                'body' => [
                    'contentType' => 'HTML',
                    'content' => $body,
                ],
                'toRecipients' => $to,
            ],
            'saveToSentItems' => false,
        ];

        try {
            $this->graph->createRequest(
                'POST',
                '/users/' . env('GRAPH_SENDER_EMAIL') . '/sendMail'
            )->attachBody($payload)->execute();

            return $this->numberOfRecipients($message);
        } catch (\Throwable $e) {
            \Log::error('Graph Mail Error: ' . $e->getMessage());
            throw $e;
        }
    }
}