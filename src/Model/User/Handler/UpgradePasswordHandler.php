<?php

declare(strict_types=1);

namespace App\Model\User\Handler;

use App\Model\User\Command\UpgradePassword;
use App\Model\User\Exception\UserNotFound;
use App\Model\User\UserList;

class UpgradePasswordHandler
{
    private UserList $userRepo;

    public function __construct(UserList $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    public function __invoke(UpgradePassword $command): void
    {
        $user = $this->userRepo->get($command->userId());

        if (!$user) {
            throw UserNotFound::withUserId($command->userId());
        }

        $user->upgradePassword($command->encodedPassword());

        $this->userRepo->save($user);
    }
}
