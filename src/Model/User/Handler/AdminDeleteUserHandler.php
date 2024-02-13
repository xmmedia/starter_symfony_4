<?php

declare(strict_types=1);

namespace App\Model\User\Handler;

use App\Model\User\Command\AdminDeleteUser;
use App\Model\User\Exception\UserNotFound;
use App\Model\User\UserList;

final readonly class AdminDeleteUserHandler
{
    public function __construct(private UserList $userRepo)
    {
    }

    public function __invoke(AdminDeleteUser $command): void
    {
        $user = $this->userRepo->get($command->userId());

        if (!$user) {
            throw UserNotFound::withUserId($command->userId());
        }

        $user->delete();

        $this->userRepo->save($user);
    }
}
