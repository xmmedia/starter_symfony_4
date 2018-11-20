<?php

declare(strict_types=1);

namespace App\Messaging;

/**
 * A message implementing this interface is aware of its name.
 */
interface HasMessageName
{
    public function messageName(): string;
}
