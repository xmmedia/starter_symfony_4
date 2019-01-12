<?php

declare(strict_types=1);

namespace App\Model\Enquiry\Handler;

use App\Infrastructure\Email\EmailGatewayInterface;
use App\Infrastructure\Email\EmailTemplate;
use App\Model\Email;
use App\Model\Enquiry\Command\SendEnquiryEmail;

class SendEnquiryEmailHandler
{
    /** @var EmailGatewayInterface|\App\Infrastructure\Email\EmailGateway */
    private $emailGateway;

    /** @var string */
    private $adminEmail;

    public function __construct(EmailGatewayInterface $emailGateway, string $adminEmail)
    {
        $this->emailGateway = $emailGateway;
        $this->adminEmail = $adminEmail;
    }

    public function __invoke(SendEnquiryEmail $command): void
    {
        $this->emailGateway->send(
            EmailTemplate::ENQUIRY_RECEIVED,
            Email::fromString($this->adminEmail),
            [
                'name'     => $command->name(),
                'email'    => $command->email()->toString(),
                'message'  => $command->message(),
                'received' => $command->createdAt()->format('Y-m-d H:m:s'),
            ]
        );
    }
}
