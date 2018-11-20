<?php

declare(strict_types=1);

namespace App\Messaging;

/**
 * This is the base class for domain events.
 */
abstract class DomainEvent extends DomainMessage
{
    public function messageType(): string
    {
        return self::TYPE_EVENT;
    }
}
