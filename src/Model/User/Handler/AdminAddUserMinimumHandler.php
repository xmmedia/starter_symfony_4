<?php

declare(strict_types=1);

namespace App\Model\User\Handler;

use App\Model\User\Command\AdminAddUserMinimum;
use App\Model\User\Service\ChecksUniqueUsersEmail;
use App\Model\User\User;
use App\Model\User\UserList;

class AdminAddUserMinimumHandler
{
    private UserList $userRepo;
    private ChecksUniqueUsersEmail $checksUniqueUsersEmail;

    public function __construct(
        UserList $userRepo,
        ChecksUniqueUsersEmail $checksUniqueUsersEmail,
    ) {
        $this->userRepo = $userRepo;
        $this->checksUniqueUsersEmail = $checksUniqueUsersEmail;
    }

    public function __invoke(AdminAddUserMinimum $command): void
    {
        $user = User::addByAdminMinimum(
            $command->userId(),
            $command->email(),
            $command->hashedPassword(),
            $command->role(),
            $command->firstName(),
            $command->lastName(),
            $command->sendInvite(),
            $this->checksUniqueUsersEmail,
        );

        $this->userRepo->save($user);
    }
}
