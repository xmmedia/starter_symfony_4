<?php

declare(strict_types=1);

namespace App\Model\User;

use Xm\SymfonyBundle\Model\UuidId;
use Xm\SymfonyBundle\Model\UuidInterface;

final readonly class UserId implements UuidInterface, UserIdInterface
{
    use UuidId;
}
