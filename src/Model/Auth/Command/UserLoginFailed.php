<?php

declare(strict_types=1);

namespace App\Model\Auth\Command;

use App\Model\Auth\AuthId;
use App\Model\User\UserId;
use App\Util\Assert;
use Xm\SymfonyBundle\Messaging\Command;

final class UserLoginFailed extends Command
{
    public static function now(
        AuthId $authId,
        ?string $email,
        ?UserId $userId,
        ?string $userAgent,
        string $ipAddress,
        ?string $exceptionMessage,
        string $route,
    ): self {
        return new self([
            'authId'           => $authId->toString(),
            'email'            => $email,
            'userId'           => $userId?->toString(),
            'userAgent'        => $userAgent,
            'ipAddress'        => $ipAddress,
            'exceptionMessage' => $exceptionMessage,
            'route'            => $route,
        ]);
    }

    public function authId(): AuthId
    {
        return AuthId::fromString($this->payload['authId']);
    }

    public function email(): ?string
    {
        return $this->payload['email'];
    }

    public function userId(): ?UserId
    {
        if (null === $this->payload['userId']) {
            return null;
        }

        return UserId::fromString($this->payload['userId']);
    }

    public function userAgent(): ?string
    {
        return $this->payload['userAgent'];
    }

    public function ipAddress(): string
    {
        return $this->payload['ipAddress'];
    }

    public function exceptionMessage(): ?string
    {
        return $this->payload['exceptionMessage'];
    }

    public function route(): string
    {
        return $this->payload['route'];
    }

    #[\Override]
    protected function setPayload(array $payload): void
    {
        Assert::keyExists($payload, 'authId');
        Assert::uuid($payload['authId']);

        Assert::keyExists($payload, 'email');
        Assert::nullOrString($payload['email']);

        Assert::keyExists($payload, 'userId');
        Assert::nullOrUuid($payload['userId']);

        Assert::keyExists($payload, 'userAgent');
        Assert::nullOrString($payload['userAgent']);

        Assert::keyExists($payload, 'ipAddress');
        Assert::notEmpty($payload['ipAddress']);
        Assert::string($payload['ipAddress']);

        Assert::keyExists($payload, 'exceptionMessage');
        Assert::nullOrString($payload['exceptionMessage']);

        Assert::keyExists($payload, 'route');
        Assert::notEmpty($payload['route']);

        parent::setPayload($payload);
    }
}
