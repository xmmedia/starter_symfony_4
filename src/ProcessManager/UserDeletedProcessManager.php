<?php

declare(strict_types=1);

namespace App\ProcessManager;

use App\Infrastructure\Service\UserPasswordStore;
use App\Model\User\Event\UserWasDeletedByAdmin;

/**
 * Removes the password hash when the user is deleted.
 *
 * The user projection can't do this: `user_credential` isn't projection owned.
 */
final readonly class UserDeletedProcessManager
{
    public function __construct(private UserPasswordStore $passwordStore)
    {
    }

    public function __invoke(UserWasDeletedByAdmin $event): void
    {
        $this->passwordStore->remove($event->userId());
    }
}
