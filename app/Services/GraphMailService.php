<?php

namespace App\Services;

use Microsoft\Graph\Graph;
use Illuminate\Mail\Transport\Transport;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mime\Email;

class GraphMailService extends Transport
{
    protected $graph;

    public function __construct()
    {
        $this->graph = new Graph();
        $this->graph->setAccessToken(env('GRAPH_ACCESS_TOKEN'));
    }

    /**
     * Send the given Symfony Email message via Microsoft Graph.
     */
    public function send(SentMessage $message, ?\Symfony\Component\Mailer\Transport\Smtp\TransportInterface $transport = null): void
    {
        $email = $message->getOriginalMessage();
        $subject = $email->getSubject();
        $to = implode(',', array_map(fn($addr) => $addr->getAddress(), $email->getTo()));
        $body = $email->getHtmlBody() ?: $email->getTextBody();

        $graphMessage = [
            'message' => [
                'subject' => $subject,
                'body' => [
                    'contentType' => 'HTML',
                    'content' => $body,
                ],
                'toRecipients' => array_map(fn($addr) => [
                    'emailAddress' => ['address' => $addr->getAddress()]
                ], $email->getTo()),
            ],
            'saveToSentItems' => true,
        ];

        try {
            $this->graph->createRequest('POST', '/users/' . env('GRAPH_SENDER_EMAIL') . '/sendMail')
                ->attachBody($graphMessage)
                ->execute();
        } catch (\Throwable $e) {
            \Log::error("Graph Mail Error: " . $e->getMessage());
        }
    }
}