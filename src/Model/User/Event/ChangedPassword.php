<?php

declare(strict_types=1);

namespace App\Model\User\Event;

use App\Model\User\UserId;
use Xm\SymfonyBundle\EventSourcing\AggregateChanged;

class ChangedPassword extends AggregateChanged
{
    private string $encodedPassword;

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
        if (!isset($this->encodedPassword)) {
            $this->encodedPassword = $this->payload['encodedPassword'];
        }

        return $this->encodedPassword;
    }
}
