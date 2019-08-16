<?php

declare(strict_types=1);

namespace App\Model;

interface Entity
{
    public function sameIdentityAs(self $other): bool;
}
