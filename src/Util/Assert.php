<?php

declare(strict_types=1);

namespace App\Util;

use App\Model\User\User;
use Symfony\Component\Security\Core\Encoder\BasePasswordEncoder;

class Assert extends \Webmozart\Assert\Assert
{
    public static function passwordLength($password): void
    {
        self::lengthBetween(
            $password,
            User::PASSWORD_MIN_LENGTH,
            BasePasswordEncoder::MAX_PASSWORD_LENGTH,
            'The password must length must be between %2$d and %3$d. Got '.\strlen($password)
        );
    }
}
