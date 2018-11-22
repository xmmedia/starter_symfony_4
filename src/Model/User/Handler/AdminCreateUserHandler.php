<?php

declare(strict_types=1);

namespace App\Model\User\Handler;

use App\Model\User\Command\AdminCreateUser;
use App\Model\User\User;
use App\Model\User\UserList;

class AdminCreateUserHandler
{
    /** @var UserList */
    private $userRepo;

    public function __construct(UserList $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    public function __invoke(AdminCreateUser $command): void
    {
        $user = User::createByAdmin(
            $command->userId(),
            $command->email(),
            $command->encodedPassword(),
            $command->role(),
            $command->enabled(),
            $command->firstName(),
            $command->lastName()
        );

        $this->userRepo->save($user);
    }
}
