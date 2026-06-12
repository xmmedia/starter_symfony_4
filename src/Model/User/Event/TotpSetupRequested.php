<?php

declare(strict_types=1);

namespace App\Model\User\Event;

use App\Model\User\UserId;
use Xm\SymfonyBundle\EventSourcing\AggregateChanged;

final class TotpSetupRequested extends AggregateChanged
{
    private string $totpSecret;

    public static function now(UserId $userId, string $totpSecret): self
    {
        $event = self::occur($userId->toString(), [
            'totpSecret' => $totpSecret,
        ]);

        $event->totpSecret = $totpSecret;

        return $event;
    }

    public function userId(): UserId
    {
        return UserId::fromString($this->aggregateId());
    }

    public function totpSecret(): string
    {
        if (!isset($this->totpSecret)) {
            $this->totpSecret = $this->payload['totpSecret'];
        }

        return $this->totpSecret;
    }
}
