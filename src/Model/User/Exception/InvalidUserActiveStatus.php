<?php

declare(strict_types=1);

namespace App\Model\User\Exception;

use App\Model\User\UserId;

final class InvalidUserActiveStatus extends \RuntimeException
{
    public static function triedToActivateWhenAlreadyActive(UserId $userId): self
    {
        return new self(sprintf(
            'Tried to activate the user "%s" that\'s already active.',
            $userId->toString()
        ));
    }

    public static function triedToDeactivateWhenAlreadyInactive(UserId $userId): self
    {
        return new self(sprintf(
            'Tried to deactivate the user "%s" that\'s already inactive.',
            $userId->toString()
        ));
    }
}
