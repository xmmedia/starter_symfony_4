<?php

declare(strict_types=1);

namespace App\Model;

interface ValueObject
{
    public function sameValueAs(self $other): bool;
}
