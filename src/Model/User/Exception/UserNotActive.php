<?php

declare(strict_types=1);

namespace App\Model\User\Exception;

use App\Model\User\UserId;

final class UserNotActive extends \RuntimeException
{
    public static function triedToSendVerification(UserId $userId): self
    {
        return new self(\sprintf(
            'Tried to send verification to user "%s" but they\'re not active.',
            $userId,
        ));
    }

    public static function triedToSendPasswordReset(UserId $userId): self
    {
        return new self(\sprintf(
            'Tried to send a password reset to user "%s" but they\'re not active.',
            $userId,
        ));
    }
}
