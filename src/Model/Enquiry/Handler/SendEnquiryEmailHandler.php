<?php

declare(strict_types=1);

namespace App\Model\Enquiry\Handler;

use App\Model\Enquiry\Command\SendEnquiryEmail;
use Xm\SymfonyBundle\Infrastructure\Email\EmailGatewayInterface;
use Xm\SymfonyBundle\Model\Email;

readonly class SendEnquiryEmailHandler
{
    public function __construct(
        private EmailGatewayInterface $emailGateway,
        private string $template,
        private string $adminEmail,
    ) {
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
