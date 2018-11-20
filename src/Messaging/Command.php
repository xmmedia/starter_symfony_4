<?php

declare(strict_types=1);

namespace App\Messaging;

/**
 * This is the base class for commands used to trigger actions in a domain model.
 *
 * @see https://github.com/prooph/common
 */
abstract class Command extends DomainMessage
{
    use PayloadTrait;
    use IssuedByTrait;

    public function messageType(): string
    {
        return self::TYPE_COMMAND;
    }
}
