<?php

declare(strict_types=1);

namespace App\Tests;

use App\Model\ValueObject;

class FakeVo implements ValueObject
{
    public static function create(): self
    {
        return new self();
    }

    public function sameValueAs(ValueObject $other): bool
    {
        return false;
    }
}
