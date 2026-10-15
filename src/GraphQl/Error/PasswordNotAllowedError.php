<?php

declare(strict_types=1);

namespace App\GraphQl\Error;

use Xm\SymfonyBundle\Infrastructure\GraphQl\Error\CodedUserError;

/**
 * The password was rejected by Assert::passwordAllowed(): too short/long, not complex enough or compromised.
 * The frontend checks these too, but its complexity score can differ from the backend's.
 */
class PasswordNotAllowedError extends CodedUserError
{
    public function errorCode(): string
    {
        return 'PASSWORD_NOT_ALLOWED';
    }
}
