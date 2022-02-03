<?php

declare(strict_types=1);

namespace App\Model\User\Handler;

use App\Model\User\Command\VerifyUserByAdmin;
use App\Model\User\Exception\UserNotFound;
use App\Model\User\UserList;

class VerifyUserByAdminHandler
{
    public function __construct(private UserList $userRepo)
    {
    }

    public function __invoke(VerifyUserByAdmin $command): void
    {
        $user = $this->userRepo->get($command->userId());

        if (!$user) {
            throw UserNotFound::withUserId($command->userId());
        }

        $user->verifyByAdmin();

        $this->userRepo->save($user);
    }
}
