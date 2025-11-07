<?php

namespace App\Mail\Transport;

use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mime\RawMessage;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\SentMessage;
use Microsoft\Kiota\Authentication\Oauth\ClientCredentialContext;
use Microsoft\Graph\Generated\Models\Message;
use Microsoft\Graph\Generated\Models\Recipient;
use Microsoft\Graph\Generated\Models\EmailAddress;
use Microsoft\Graph\Generated\Models\ItemBody;
use Microsoft\Graph\Generated\Models\FileAttachment;
use Microsoft\Graph\Generated\Users\Item\SendMail\SendMailPostRequestBody;
use Microsoft\Graph\GraphServiceClient;

class GraphTransport extends AbstractTransport implements TransportInterface
{
    protected $graphServiceClient;

    public function __construct()
    {
        parent::__construct(null);  // Null dispatcher - no events needed

        $tokenContext = new ClientCredentialContext(
            env('GRAPH_TENANT_ID'),
            env('GRAPH_CLIENT_ID'),
            env('GRAPH_CLIENT_SECRET')
        );

        $this->graphServiceClient = new GraphServiceClient(
            $tokenContext,
            ['https://graph.microsoft.com/.default']  // Scope for app-only permissions
        );
    }

    public function send(RawMessage $message, Envelope $envelope = null): ?SentMessage
    {
        // Ensure message is Email
        if (!$message instanceof Email) {
            throw new \InvalidArgumentException('GraphTransport only supports Email messages.');
        }

        // Build SentMessage for doSend
        $sentMessage = new SentMessage($message, $envelope ?? new Envelope(
            $message->getFrom()[0]->getAddress(),
            $message->getTo()[0]->getAddress()
        ), []);

        // Delegate to doSend (void)
        $this->doSend($sentMessage);

        // Return the SentMessage (Symfony expects this)
        return $sentMessage;
    }

    protected function doSend(SentMessage $sentMessage): void
    {
        $originalMessage = $sentMessage->getOriginalMessage();
        $envelope = $sentMessage->getEnvelope();

        // Ensure original is Email
        if (!$originalMessage instanceof Email) {
            throw new \InvalidArgumentException('GraphTransport only supports Email messages.');
        }

        $graphMessage = new Message();
        $graphMessage->setSubject($originalMessage->getSubject());

        // From (wrap EmailAddress in Recipient)
        $from = $envelope->getSender();
        $fromEmailAddress = new EmailAddress([
            'address' => $from->getAddress(),
            'name' => $from->getName() ?: env('MAIL_FROM_NAME', 'SharpLync')
        ]);
        $graphMessage->setFrom(new Recipient([
            'emailAddress' => $fromEmailAddress
        ]));

        // To (array of Recipient)
        $toRecipients = [];
        foreach ($originalMessage->getTo() as $to) {
            $toEmailAddress = new EmailAddress([
                'address' => $to->getAddress(),
                'name' => $to->getName() ?: ''
            ]);
            $toRecipients[] = new Recipient([
                'emailAddress' => $toEmailAddress
            ]);
        }
        $graphMessage->setToRecipients($toRecipients);

        // CC (array of Recipient)
        if ($cc = $originalMessage->getCc()) {
            $ccRecipients = [];
            foreach ($cc as $ccAddr) {
                $ccEmailAddress = new EmailAddress([
                    'address' => $ccAddr->getAddress(),
                    'name' => $ccAddr->getName() ?: ''
                ]);
                $ccRecipients[] = new Recipient([
                    'emailAddress' => $ccEmailAddress
                ]);
            }
            $graphMessage->setCcRecipients($ccRecipients);
        }

        // BCC (array of Recipient)
        if ($bcc = $originalMessage->getBcc()) {
            $bccRecipients = [];
            foreach ($bcc as $bccAddr) {
                $bccEmailAddress = new EmailAddress([
                    'address' => $bccAddr->getAddress(),
                    'name' => $bccAddr->getName() ?: ''
                ]);
                $bccRecipients[] = new Recipient([
                    'emailAddress' => $bccEmailAddress
                ]);
            }
            $graphMessage->setBccRecipients($bccRecipients);
        }

        // Body
        $bodyContent = $originalMessage->getHtmlBody() ?: $originalMessage->getTextBody();
        $contentType = $originalMessage->getHtmlBody() ? 'HTML' : 'Text';
        $graphMessage->setBody(new ItemBody([
            'contentType' => $contentType,
            'content' => $bodyContent
        ]));

        // Attachments
        if ($attachments = $originalMessage->getAttachments()) {
            $fileAttachments = [];
            foreach ($attachments as $attachment) {
                $fileAttachments[] = new FileAttachment([
                    'name' => $attachment->getFilename() ?: 'attachment',
                    'contentType' => $attachment->getContentType() ?: 'application/octet-stream',
                    'contentBytes' => base64_encode($attachment->getBody())
                ]);
            }
            $graphMessage->setAttachments($fileAttachments);
        }

        // Send via Graph v2 fluent API
        $fromAddress = $envelope->getSender()->getAddress();
        $requestBody = new SendMailPostRequestBody([
            'message' => $graphMessage,
            'saveToSentItems' => true
        ]);

        try {
            $this->graphServiceClient->users()->byUserId($fromAddress)->sendMail()->post($requestBody);
        } catch (\Exception $e) {
            \Log::error('Graph Mail Send Failed: ' . $e->getMessage());
            throw new \Symfony\Component\Mailer\Exception\TransportException($e->getMessage(), 0, $e);
        }
    }

    public function __toString(): string
    {
        return 'graph';
    }
}
