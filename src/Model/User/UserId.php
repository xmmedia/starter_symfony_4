<?php

declare(strict_types=1);

namespace App\Model\User;

use App\Model\UuidId;
use App\Model\UuidIdGeneratable;
use App\Model\ValueObject;

class UserId implements ValueObject
{
    use UuidId;
    use UuidIdGeneratable;
}
