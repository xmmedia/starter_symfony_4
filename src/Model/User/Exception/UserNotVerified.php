<?php

declare(strict_types=1);

namespace App\Model\User\Exception;

use App\Model\User\UserId;

final class UserNotVerified extends \RuntimeException
{
    public static function triedToLogin(UserId $userId): self
    {
        return new self(sprintf(
            'User "%s" tried to login but they\'re account is not verified.',
            $userId->toString()
        ));
    }
}
