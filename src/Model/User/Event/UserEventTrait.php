<?php

declare(strict_types=1);

namespace App\Model\User\Event;

use App\Model\User\UserId;

trait UserEventTrait
{
    public static function now(UserId $userId): self
    {
        $event = self::occur($userId->toString());

        return $event;
    }

    public function userId(): UserId
    {
        return UserId::fromString($this->aggregateId());
    }
}
