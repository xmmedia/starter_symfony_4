<?php

declare(strict_types=1);

namespace App\Model\User\Command;

use Xm\SymfonyBundle\Messaging\Command;

final class DeactivateUserByAdmin extends Command
{
    use UserCommandTrait;
}
