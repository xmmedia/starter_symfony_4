<?php

declare(strict_types=1);

namespace App\Model\User\Command;

use App\Model\User\UserId;
use App\Util\Assert;
use Xm\SymfonyBundle\Messaging\Command;

final class RequestTotpSetup extends Command
{
    public static function with(UserId $userId): self
    {
        return new self([
            'userId' => $userId->toString(),
        ]);
    }

    public function userId(): UserId
    {
        return UserId::fromString($this->payload['userId']);
    }

    #[\Override]
    protected function setPayload(array $payload): void
    {
        Assert::keyExists($payload, 'userId');
        Assert::uuid($payload['userId']);

        parent::setPayload($payload);
    }
}
