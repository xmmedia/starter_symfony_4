<?php

declare(strict_types=1);

namespace App\Model\Auth\Command;

use App\Model\Auth\AuthId;
use App\Model\User\UserId;
use App\Util\Assert;
use Xm\SymfonyBundle\Messaging\Command;
use Xm\SymfonyBundle\Model\Email;

final class UserLoggedInSuccessfully extends Command
{
    public static function now(
        AuthId $authId,
        UserId $userId,
        Email $email,
        string $userAgent,
        string $ipAddress
    ): self {
        return new self([
            'authId'    => $authId->toString(),
            'userId'    => $userId->toString(),
            'email'     => $email->toString(),
            'userAgent' => $userAgent,
            'ipAddress' => $ipAddress,
        ]);
    }

    public function authId(): AuthId
    {
        return AuthId::fromString($this->payload['authId']);
    }

    public function userId(): UserId
    {
        return UserId::fromString($this->payload['userId']);
    }

    public function email(): Email
    {
        return Email::fromString($this->payload['email']);
    }

    public function userAgent(): string
    {
        return $this->payload['userAgent'];
    }

    public function ipAddress(): string
    {
        return $this->payload['ipAddress'];
    }

    protected function setPayload(array $payload): void
    {
        Assert::keyExists($payload, 'authId');
        Assert::uuid($payload['authId']);

        Assert::keyExists($payload, 'userId');
        Assert::uuid($payload['userId']);

        Assert::keyExists($payload, 'email');

        Assert::keyExists($payload, 'userAgent');
        Assert::notEmpty($payload['userAgent']);
        Assert::string($payload['userAgent']);

        Assert::keyExists($payload, 'ipAddress');
        Assert::notEmpty($payload['ipAddress']);
        Assert::string($payload['ipAddress']);

        parent::setPayload($payload);
    }
}
