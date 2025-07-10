<?php

declare(strict_types=1);

namespace App\Model\User\Event;

use App\Model\User\UserId;
use Xm\SymfonyBundle\EventSourcing\AggregateChanged;

final class PasswordUpgraded extends AggregateChanged
{
    private string $hashedPassword;

    public static function now(UserId $userId, string $hashedPassword): self
    {
        $event = self::occur($userId->toString(), [
            'hashedPassword' => $hashedPassword,
        ]);

        $event->hashedPassword = $hashedPassword;

        return $event;
    }

    public function userId(): UserId
    {
        return UserId::fromString($this->aggregateId());
    }

    public function hashedPassword(): string
    {
        if (!isset($this->hashedPassword)) {
            if (\array_key_exists('hashedPassword', $this->payload)) {
                $this->hashedPassword = $this->payload['hashedPassword'];
            } else {
                // @todo-symfony remove encodedPassword conditional (& test) if this is a new project (encodedPassword is the old name)
                $this->hashedPassword = $this->payload['encodedPassword'];
            }
        }

        return $this->hashedPassword;
    }
}
