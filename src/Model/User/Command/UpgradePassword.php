<?php

declare(strict_types=1);

namespace App\Model\User\Command;

use App\Model\User\UserId;
use App\Util\Assert;
use Xm\SymfonyBundle\Messaging\Command;

final class UpgradePassword extends Command
{
    public static function forUser(
        UserId $userId,
        string $encodedPassword
    ): self {
        return new self([
            'userId'          => $userId->toString(),
            'encodedPassword' => $encodedPassword,
        ]);
    }

    public function userId(): UserId
    {
        return UserId::fromString($this->payload['userId']);
    }

    public function encodedPassword(): string
    {
        return $this->payload['encodedPassword'];
    }

    protected function setPayload(array $payload): void
    {
        Assert::keyExists($payload, 'userId');
        Assert::uuid($payload['userId']);

        Assert::keyExists($payload, 'encodedPassword');
        Assert::notEmpty($payload['encodedPassword']);
        Assert::string($payload['encodedPassword']);

        parent::setPayload($payload);
    }
}
