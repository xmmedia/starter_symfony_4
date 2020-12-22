<?php

declare(strict_types=1);

namespace App\Model\Enquiry\Command;

use App\Model\Enquiry\EnquiryId;
use App\Model\Enquiry\Name;
use App\Util\Assert;
use Xm\SymfonyBundle\Messaging\Command;
use Xm\SymfonyBundle\Model\Email;

final class SubmitEnquiry extends Command
{
    public static function with(
        EnquiryId $enquiryId,
        Name $name,
        Email $email,
        string $message
    ): self {
        return new self([
            'enquiryId' => $enquiryId->toString(),
            'name'      => $name->toString(),
            'email'     => $email->toString(),
            'message'   => $message,
        ]);
    }

    public function enquiryId(): EnquiryId
    {
        return EnquiryId::fromString($this->payload['enquiryId']);
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
        Assert::keyExists($payload, 'enquiryId');
        Assert::uuid($payload['enquiryId']);

        Assert::keyExists($payload, 'name');
        Assert::string($payload['name']);

        Assert::keyExists($payload, 'email');

        Assert::keyExists($payload, 'message');
        Assert::string($payload['message']);

        parent::setPayload($payload);
    }
}
