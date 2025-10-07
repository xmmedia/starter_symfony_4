<?php

declare(strict_types=1);

namespace App\Model\User\Command;

use App\Model\User\Name;
use App\Model\User\UserId;
use App\Util\Assert;
use Xm\SymfonyBundle\Messaging\Command;
use Xm\SymfonyBundle\Model\Email;

/**
 * Sends email to user to activate their account by entering their password.
 * Uses a reset token and sets their account to active when complete.
 */
final class SendActivation extends Command
{
    public static function now(
        UserId $userId,
        Email $email,
        Name $firstName,
        Name $lastName,
    ): self {
        return new self([
            'userId'    => $userId->toString(),
            'email'     => $email->toString(),
            'firstName' => $firstName->toString(),
            'lastName'  => $lastName->toString(),
        ]);
    }

    public function userId(): UserId
    {
        return UserId::fromString($this->payload['userId']);
    }

    public function email(): Email
    {
        return Email::fromString($this->payload['email']);
    }

    public function firstName(): Name
    {
        return Name::fromString($this->payload['firstName']);
    }

    public function lastName(): Name
    {
        return Name::fromString($this->payload['lastName']);
    }

    protected function setPayload(array $payload): void
    {
        Assert::keyExists($payload, 'userId');
        Assert::uuid($payload['userId']);

        Assert::keyExists($payload, 'email');
        Assert::string($payload['email']);

        Assert::keyExists($payload, 'firstName');
        Assert::string($payload['firstName']);

        Assert::keyExists($payload, 'lastName');
        Assert::string($payload['lastName']);

        parent::setPayload($payload);
    }
}
