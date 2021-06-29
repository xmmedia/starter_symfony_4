<?php

declare(strict_types=1);

namespace App\Util;

use App\Model\User\Name;
use App\Model\User\User;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Xm\SymfonyBundle\Model\Email;
use Xm\SymfonyBundle\Util\PasswordStrengthInterface;
use Xm\SymfonyBundle\Util\StringUtil;

class Assert extends \Xm\SymfonyBundle\Util\Assert
{
    public static function passwordAllowed(
        ?string $password,
        Email $email,
        Name $firstName,
        Name $lastName,
        ?int $minimum = null,
        PasswordStrengthInterface $passwordStrength = null,
        HttpClientInterface $pwnedHttpClient = null
    ): void {
        self::passwordLength($password);
        self::passwordComplexity(
            $password,
            [
                $email->toString(),
                $firstName->toString(),
                $lastName->toString(),
            ],
            $minimum,
            $passwordStrength,
        );
        self::compromisedPassword($password, $pwnedHttpClient);
    }

    public static function passwordLength(?string $password): void
    {
        self::notEmpty(
            StringUtil::trim($password),
            'The password cannot be empty or all whitespace.'
        );

        self::lengthBetween(
            $password,
            User::PASSWORD_MIN_LENGTH,
            PasswordHasherInterface::MAX_PASSWORD_LENGTH,
            'The password must length must be between %2$d and %3$d. Got '.\strlen($password)
        );
    }
}
