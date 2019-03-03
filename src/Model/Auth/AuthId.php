<?php

declare(strict_types=1);

namespace App\Model\Auth;

use App\Model\UuidId;
use App\Model\UuidInterface;
use App\Model\ValueObject;

class AuthId implements ValueObject, UuidInterface
{
    use UuidId;
}
