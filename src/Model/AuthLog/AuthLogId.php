<?php

declare(strict_types=1);

namespace App\Model\AuthLog;

use Xm\SymfonyBundle\Model\UuidId;
use Xm\SymfonyBundle\Model\UuidInterface;
use Xm\SymfonyBundle\Model\ValueObject;

final class AuthLogId implements ValueObject, UuidInterface, \Stringable
{
    use UuidId;
}
