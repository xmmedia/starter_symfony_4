<?php

declare(strict_types=1);

namespace App\Model\User\Handler;

use App\Model\User\Command\AdminAddUserMinimum;
use App\Model\User\Service\ChecksUniqueUsersEmail;
use App\Model\User\User;
use App\Model\User\UserList;

final readonly class AdminAddUserMinimumHandler
{
    public function __construct(
        private UserList $userRepo,
        private ChecksUniqueUsersEmail $checksUniqueUsersEmail,
    ) {
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
