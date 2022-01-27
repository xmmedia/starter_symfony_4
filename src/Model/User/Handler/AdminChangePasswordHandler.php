<?php

declare(strict_types=1);

namespace App\Model\User\Handler;

use App\Model\User\Command\AdminChangePassword;
use App\Model\User\Exception\UserNotFound;
use App\Model\User\UserList;

class AdminChangePasswordHandler
{
    private UserList $userRepo;

    public function __construct(UserList $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    public function __invoke(AdminChangePassword $command): void
    {
        $user = $this->userRepo->get($command->userId());

        if (!$user) {
            throw UserNotFound::withUserId($command->userId());
        }

        $user->changePasswordByAdmin($command->hashedPassword());

        $this->userRepo->save($user);
    }
}
