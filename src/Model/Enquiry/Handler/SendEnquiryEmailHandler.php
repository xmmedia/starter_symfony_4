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

    /** @var string */
    private $template;

    /** @var string */
    private $adminEmail;

    public function __construct(
        EmailGatewayInterface $emailGateway,
        string $template,
        string $adminEmail,
    ) {
        $this->emailGateway = $emailGateway;
        $this->template = $template;
        $this->adminEmail = $adminEmail;
    }

    public function __invoke(SendEnquiryEmail $command): void
    {
        $this->emailGateway->send(
            $this->template,
            Email::fromString($this->adminEmail),
            [
                'name'     => $command->name(),
                'email'    => $command->email()->toString(),
                'message'  => $command->message(),
                'received' => $command->createdAt()->format('Y-m-d H:m:s'),
            ],
        );
    }
}
