<?php

declare(strict_types=1);

namespace App\Model\User\Event;

use App\Model\User\UserId;
use Xm\SymfonyBundle\EventSourcing\AggregateChanged;

final class TotpDisabled extends AggregateChanged
{
    public static function now(UserId $userId): self
    {
        return self::occur($userId->toString(), []);
    }

    public function userId(): UserId
    {
        return UserId::fromString($this->aggregateId());
    }
}
