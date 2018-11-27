<?php

declare(strict_types=1);

namespace App\Model\Auth\Command;

use App\Messaging\Command;
use App\Model\Auth\AuthId;
use Webmozart\Assert\Assert;

final class UserLoginFailed extends Command
{
    public static function now(
        AuthId $authId,
        // @todo support null?
        string $email,
        // @todo support null? (and all following)
        string $userAgent,
        string $ipAddress,
        ?string $exceptionMessage
    ): self {
        return new self([
            'authId'           => $authId->toString(),
            'email'            => $email,
            'userAgent'        => $userAgent,
            'ipAddress'        => $ipAddress,
            'exceptionMessage' => $exceptionMessage,
        ]);
    }

    public function authId(): AuthId
    {
        return AuthId::fromString($this->payload()['authId']);
    }

    public function email(): string
    {
        return $this->payload()['email'];
    }

    public function userAgent(): string
    {
        return $this->payload()['userAgent'];
    }

    public function ipAddress(): string
    {
        return $this->payload()['ipAddress'];
    }

    public function exceptionMessage(): ?string
    {
        return $this->payload()['exceptionMessage'];
    }

    protected function setPayload(array $payload): void
    {
        Assert::keyExists($payload, 'authId');
        Assert::uuid($payload['authId']);

        Assert::keyExists($payload, 'email');

        Assert::keyExists($payload, 'userAgent');
        Assert::notEmpty($payload['userAgent']);
        Assert::string($payload['userAgent']);

        Assert::keyExists($payload, 'ipAddress');
        Assert::notEmpty($payload['ipAddress']);
        Assert::string($payload['ipAddress']);

        Assert::keyExists($payload, 'exceptionMessage');

        parent::setPayload($payload);
    }
}
