<?php

declare(strict_types=1);

namespace App\Model;

use Ramsey\Uuid\Uuid;

/**
 * [!!] Avoid using unless necessary.
 */
trait UuidIdGeneratable
{
    public static function generate(): self
    {
        return new self(Uuid::uuid4());
    }
}
