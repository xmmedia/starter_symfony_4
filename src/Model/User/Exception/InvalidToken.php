<?php

declare(strict_types=1);

namespace App\Model\User\Exception;

use App\Model\User\Token;

final class InvalidToken extends \InvalidArgumentException
{
    public static function tokenDoesntExist(Token $token): self
    {
        return new self(
            sprintf('The token %s doesn\'t exist.', $token),
        );
    }

    public static function userDoesntExist(Token $token): self
    {
        return new self(
            sprintf('The user on token %s doesn\'t exist.', $token),
        );
    }

    public static function userInactive(Token $token): self
    {
        return new self(
            sprintf('The user on token %s is inactive.', $token),
        );
    }

    public static function userVerified(Token $token): self
    {
        return new self(
            sprintf('The user on token %s is already verified.', $token),
        );
    }
}
