<?php

declare(strict_types=1);

namespace App\Model\User;

use Xm\SymfonyBundle\Model\ValueObject;

interface UserIdInterface extends ValueObject, \Stringable
{
}
