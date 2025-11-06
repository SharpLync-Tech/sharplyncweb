<?php

namespace App\Services;

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\Message;
use Microsoft\Graph\Model\Recipient;
use Microsoft\Graph\Model\Body;
use Microsoft\Graph\Model\EmailAddress;

class GraphMailService
{
    protected $graph;

    public function __construct()
    {
        $this->graph = new Graph();
        $this->graph->setAccessToken(env('GRAPH_ACCESS_TOKEN'));
    }

    public function sendMail($to, $subject, $body)
    {
        $message = [
            "message" => [
                "subject" => $subject,
                "body" => [
                    "contentType" => "HTML",
                    "content" => $body,
                ],
                "toRecipients" => [
                    [
                        "emailAddress" => [
                            "address" => $to,
                        ],
                    ],
                ],
            ],
            "saveToSentItems" => "true",
        ];

        try {
            $this->graph->createRequest("POST", "/users/" . env('GRAPH_SENDER_EMAIL') . "/sendMail")
                ->attachBody($message)
                ->execute();
            return true;
        } catch (\Exception $e) {
            \Log::error("Graph Mail Error: " . $e->getMessage());
            return false;
        }
    }
}