<?php

declare(strict_types=1);

namespace App\Model\User\Exception;

use App\Model\User\UserId;

final class UserIsDeleted extends \InvalidArgumentException
{
    public static function triedTo(UserId $userId, string $action): self
    {
        return new self(
            \sprintf(
                'Tried to %s deleted User with ID "%s"',
                $action,
                $userId,
            ),
        );
    }

    public static function triedToDelete(UserId $userId): self
    {
        return new self(
            \sprintf(
                'Tried to delete User with ID "%s" that\'s already deleted',
                $userId,
            ),
        );
    }
}
