<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use App\Security\Exception\AccountInactiveException;
use App\Security\Exception\AccountNotVerifiedException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }
    }

    /**
     * Exceptions/messages generated here can be displayed to the user
     * because they've entered the correct password.
     */
    public function checkPostAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if (!$user->verified()) {
            $ex = new AccountNotVerifiedException('User account has not been verified.');
            $ex->setUser($user);
            throw $ex;
        }

        if (!$user->active()) {
            $ex = new AccountInactiveException('User account is not active.');
            $ex->setUser($user);
            throw $ex;
        }
    }
}
