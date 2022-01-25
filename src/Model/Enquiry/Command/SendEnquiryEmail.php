<?php

declare(strict_types=1);

namespace App\Model\Enquiry\Command;

use App\Util\Assert;
use Xm\SymfonyBundle\Messaging\Command;
use Xm\SymfonyBundle\Model\Email;

final class SendEnquiryEmail extends Command
{
    public static function with(
        string $name,
        Email $email,
        string $message,
    ): self {
        return new self([
            'name'    => $name,
            'email'   => $email->toString(),
            'message' => $message,
        ]);
    }

    public function name(): string
    {
        return $this->payload['name'];
    }

    public function email(): Email
    {
        return Email::fromString($this->payload['email']);
    }

    public function message(): string
    {
        return $this->payload['message'];
    }

    protected function setPayload(array $payload): void
    {
        Assert::keyExists($payload, 'name');
        Assert::notEmpty($payload['name']);
        Assert::string($payload['name']);

        Assert::keyExists($payload, 'email');

        Assert::keyExists($payload, 'message');
        Assert::notEmpty($payload['message']);
        Assert::string($payload['message']);

        parent::setPayload($payload);
    }
}
