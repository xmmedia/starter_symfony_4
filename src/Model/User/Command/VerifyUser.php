<?php

declare(strict_types=1);

namespace App\Model\User\Command;

use Xm\SymfonyBundle\Messaging\Command;

final class VerifyUser extends Command
{
    use UserCommandTrait;
}
