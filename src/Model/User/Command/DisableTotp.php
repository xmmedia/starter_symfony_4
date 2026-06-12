<?php

declare(strict_types=1);

namespace App\Model\User\Command;

use App\Model\User\UserId;
use App\Util\Assert;
use Xm\SymfonyBundle\Messaging\Command;

final class DisableTotp extends Command
{
    public static function with(UserId $userId, string $code): self
    {
        return new self([
            'userId' => $userId->toString(),
            'code'   => $code,
        ]);
    }

    public function userId(): UserId
    {
        return UserId::fromString($this->payload['userId']);
    }

    public function code(): string
    {
        return $this->payload['code'];
    }

    #[\Override]
    protected function setPayload(array $payload): void
    {
        Assert::keyExists($payload, 'userId');
        Assert::uuid($payload['userId']);

        Assert::keyExists($payload, 'code');
        Assert::notEmpty($payload['code']);
        Assert::string($payload['code']);

        parent::setPayload($payload);
    }
}
