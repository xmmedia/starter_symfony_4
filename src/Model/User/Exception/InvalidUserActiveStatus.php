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

    public static function triedToVerifyAnInactiveUser(UserId $userId): self
    {
        return new self(sprintf(
            'Tried to verify the user "%s" that\'s currently inactive.',
            $userId->toString()
        ));
    }

    public static function triedToRequestPasswordReset(UserId $userId): self
    {
        return new self(sprintf(
            'Tried to request a password reset for user "%s" that\'s currently inactive.',
            $userId->toString()
        ));
    }

    public static function triedToUpdateProfile(UserId $userId): self
    {
        return new self(sprintf(
            'User "%s" tried to update their profile but their currently inactive.',
            $userId->toString()
        ));
    }

    public static function triedToChangePassword(UserId $userId): self
    {
        return new self(sprintf(
            'User "%s" tried to change their password but their currently inactive.',
            $userId->toString()
        ));
    }

    public static function triedToLogin(UserId $userId): self
    {
        return new self(sprintf(
            'User "%s" tried to login but their currently inactive.',
            $userId->toString()
        ));
    }
}
