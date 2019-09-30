<?php

declare(strict_types=1);

namespace App\Model\Enquiry\Command;

use App\Model\Enquiry\Enquiry;
use App\Model\Enquiry\EnquiryId;
use Webmozart\Assert\Assert;
use Xm\SymfonyBundle\Messaging\Command;
use Xm\SymfonyBundle\Model\Email;

final class SubmitEnquiry extends Command
{
    public static function with(
        EnquiryId $enquiryId,
        string $name,
        Email $email,
        string $message
    ): self {
        return new self([
            'enquiryId' => $enquiryId->toString(),
            'name'      => $name,
            'email'     => $email->toString(),
            'message'   => $message,
        ]);
    }

    public function enquiryId(): EnquiryId
    {
        return EnquiryId::fromString($this->payload()['enquiryId']);
    }

    public function name(): string
    {
        return $this->payload()['name'];
    }

    public function email(): Email
    {
        return Email::fromString($this->payload()['email']);
    }

    public function message(): string
    {
        return $this->payload()['message'];
    }

    protected function setPayload(array $payload): void
    {
        Assert::keyExists($payload, 'enquiryId');
        Assert::uuid($payload['enquiryId']);

        Assert::keyExists($payload, 'name');
        Assert::notEmpty($payload['name']);
        Assert::string($payload['name']);
        Assert::minLength($payload['name'], Enquiry::NAME_MIN_LENGTH);
        Assert::maxLength($payload['name'], Enquiry::NAME_MAX_LENGTH);

        Assert::keyExists($payload, 'email');

        Assert::keyExists($payload, 'message');
        Assert::notEmpty($payload['message']);
        Assert::string($payload['message']);
        Assert::minLength($payload['message'], Enquiry::MESSAGE_MIN_LENGTH);
        Assert::maxLength($payload['message'], Enquiry::MESSAGE_MAX_LENGTH);

        parent::setPayload($payload);
    }
}
