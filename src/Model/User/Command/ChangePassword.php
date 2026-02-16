<?php

declare(strict_types=1);

namespace App\Model\User\Command;

use App\Model\User\UserId;
use App\Util\Assert;
use Xm\SymfonyBundle\Messaging\Command;

final class ChangePassword extends Command
{
    public static function forUser(
        UserId $userId,
        string $hashedPassword,
    ): self {
        return new self([
            'userId'         => $userId->toString(),
            'hashedPassword' => $hashedPassword,
        ]);
    }

    public function userId(): UserId
    {
        return UserId::fromString($this->payload['userId']);
    }

    public function hashedPassword(): string
    {
        return $this->payload['hashedPassword'];
    }

    #[\Override]
    protected function setPayload(array $payload): void
    {
        Assert::keyExists($payload, 'userId');
        Assert::uuid($payload['userId']);

        Assert::keyExists($payload, 'hashedPassword');
        Assert::notEmpty($payload['hashedPassword']);
        Assert::string($payload['hashedPassword']);

        parent::setPayload($payload);
    }
}
