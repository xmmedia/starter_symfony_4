<?php

declare(strict_types=1);

namespace App\Model\User\Handler;

use App\Model\User\Command\ActivateUserByAdmin;
use App\Model\User\Exception\UserNotFound;
use App\Model\User\UserList;

class ActivateUserByAdminHandler
{
    private UserList $userRepo;

    public function __construct(UserList $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    public function __invoke(ActivateUserByAdmin $command): void
    {
        $user = $this->userRepo->get($command->userId());

        if (!$user) {
            throw UserNotFound::withUserId($command->userId());
        }

        $user->activateByAdmin();

        $this->userRepo->save($user);
    }
}
