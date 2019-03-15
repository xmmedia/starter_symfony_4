<?php

declare(strict_types=1);

namespace App\Tests;

use App\Model\Entity;

// @todo IMPLEMENT
class FakeAr implements Entity
{
    public static function create(): self
    {
        return new self();
    }

    public function sameIdentityAs(Entity $other): bool
    {
        return false;
    }
}
