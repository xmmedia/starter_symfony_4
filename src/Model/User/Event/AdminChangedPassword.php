<?php

declare(strict_types=1);

namespace App\Model\User\Event;

use App\EventSourcing\AggregateChanged;
use App\Model\User\UserId;

class AdminChangedPassword extends AggregateChanged
{
    /** @var string */
    private $encodedPassword;

    public static function now(UserId $userId, string $encodedPassword): self
    {
        $event = self::occur($userId->toString(), [
            'encodedPassword' => $encodedPassword,
        ]);

        $event->encodedPassword = $encodedPassword;

        return $event;
    }

    public function userId(): UserId
    {
        return UserId::fromString($this->aggregateId());
    }

    public function encodedPassword(): string
    {
        if (null === $this->encodedPassword) {
            $this->encodedPassword = $this->payload()['encodedPassword'];
        }

        return $this->encodedPassword;
    }
}
