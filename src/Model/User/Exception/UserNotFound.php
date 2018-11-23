<?php

declare(strict_types=1);

namespace App\Model\User\Exception;

use App\Model\User\UserId;

final class UserNotFound extends \InvalidArgumentException
{
    public static function withUserId(UserId $userId): self
    {
        return new self(
            sprintf(
                'User with id "%s" cannot be found.',
                $userId->toString()
            )
        );
    }
}
