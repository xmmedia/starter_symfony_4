<?php

declare(strict_types=1);

namespace App\Model\Enquiry\Command;

use App\Messaging\Command;
use App\Model\Email;
use App\Model\Enquiry\EnquiryId;
use Webmozart\Assert\Assert;

final class SubmitEnquiryForm extends Command
{
    public const NAME_MIN_LENGTH = 5;
    public const NAME_MAX_LENGTH = 50;
    public const MESSAGE_MIN_LENGTH = 10;
    public const MESSAGE_MAX_LENGTH = 5000;

    public static function withData(
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
        Assert::minLength($payload['name'], self::NAME_MIN_LENGTH);
        Assert::maxLength($payload['name'], self::NAME_MAX_LENGTH);

        Assert::keyExists($payload, 'email');

        Assert::keyExists($payload, 'message');
        Assert::notEmpty($payload['message']);
        Assert::string($payload['message']);
        Assert::minLength($payload['message'], self::MESSAGE_MIN_LENGTH);
        Assert::maxLength($payload['message'], self::MESSAGE_MAX_LENGTH);

        parent::setPayload($payload);
    }
}
