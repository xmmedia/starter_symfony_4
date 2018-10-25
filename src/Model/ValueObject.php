<?php

declare(strict_types=1);

namespace App\Model;

interface ValueObject
{
    public function sameValueAs(ValueObject $other): bool;
}
