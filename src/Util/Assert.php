<?php

declare(strict_types=1);

namespace App\Util;

use App\Model\User\User;
use Symfony\Component\Security\Core\Encoder\BasePasswordEncoder;
use Xm\SymfonyBundle\Util\StringUtil;

class Assert extends \Xm\SymfonyBundle\Util\Assert
{
    public static function passwordLength(?string $password): void
    {
        self::notEmpty(
            StringUtil::trim($password),
            'The password cannot be empty or all whitespace.'
        );

        self::lengthBetween(
            $password,
            User::PASSWORD_MIN_LENGTH,
            BasePasswordEncoder::MAX_PASSWORD_LENGTH,
            'The password must length must be between %2$d and %3$d. Got '.\strlen($password)
        );
    }
}
