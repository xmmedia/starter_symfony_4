<?php

declare(strict_types=1);

namespace App\Model\User\Command;

use App\Messaging\Command;
use App\Model\Email;
use App\Model\User\User;
use App\Model\User\UserId;
use Webmozart\Assert\Assert;

final class UpdateUserProfile extends Command
{
    public static function withData(
        UserId $userId,
        Email $email,
        string $firstName,
        string $lastName
    ): self {
        return new self([
            'userId'    => $userId->toString(),
            'email'     => $email->toString(),
            'firstName' => $firstName,
            'lastName'  => $lastName,
        ]);
    }

    public function userId(): UserId
    {
        return UserId::fromString($this->payload()['userId']);
    }

    public function email(): Email
    {
        return Email::fromString($this->payload()['email']);
    }

    public function firstName(): string
    {
        return $this->payload()['firstName'];
    }

    public function lastName(): string
    {
        return $this->payload()['lastName'];
    }

    protected function setPayload(array $payload): void
    {
        Assert::keyExists($payload, 'userId');
        Assert::uuid($payload['userId']);

        Assert::keyExists($payload, 'email');

        Assert::keyExists($payload, 'firstName');
        Assert::notEmpty($payload['firstName']);
        Assert::string($payload['firstName']);
        Assert::minLength($payload['firstName'], User::NAME_MIN_LENGTH);
        Assert::maxLength($payload['firstName'], User::NAME_MAX_LENGTH);

        Assert::keyExists($payload, 'lastName');
        Assert::notEmpty($payload['lastName']);
        Assert::string($payload['lastName']);
        Assert::minLength($payload['lastName'], User::NAME_MIN_LENGTH);
        Assert::maxLength($payload['lastName'], User::NAME_MAX_LENGTH);

        parent::setPayload($payload);
    }
}
