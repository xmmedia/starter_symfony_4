<?php

declare(strict_types=1);

namespace App\Model\Enquiry\Handler;

use App\Model\Enquiry\Command\SendEnquiryEmail;
use Xm\SymfonyBundle\Infrastructure\Email\EmailGatewayInterface;
use Xm\SymfonyBundle\Model\Email;

class SendEnquiryEmailHandler
{
    /** @var EmailGatewayInterface|\Xm\SymfonyBundle\Infrastructure\Email\EmailGateway */
    private $emailGateway;

    /** @var string|int */
    private $templateIdOrAlias;

    /** @var string */
    private $adminEmail;

    public function __construct(
        EmailGatewayInterface $emailGateway,
        $templateIdOrAlias,
        string $adminEmail
    ) {
        $this->emailGateway = $emailGateway;
        $this->templateIdOrAlias = $templateIdOrAlias;
        $this->adminEmail = $adminEmail;
    }

    public function __invoke(SendEnquiryEmail $command): void
    {
        $this->emailGateway->send(
            $this->templateIdOrAlias,
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
