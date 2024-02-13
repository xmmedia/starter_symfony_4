<?php

declare(strict_types=1);

namespace App\Model\User\Handler;

use App\Model\User\Command\AdminAddUser;
use App\Model\User\Service\ChecksUniqueUsersEmail;
use App\Model\User\User;
use App\Model\User\UserList;

final readonly class AdminAddUserHandler
{
    public function __construct(
        private readonly UserList $userRepo,
        private readonly ChecksUniqueUsersEmail $checksUniqueUsersEmail,
    ) {
    }

    public function __invoke(AdminAddUser $command): void
    {
        $user = User::addByAdmin(
            $command->userId(),
            $command->email(),
            $command->hashedPassword(),
            $command->role(),
            $command->active(),
            $command->firstName(),
            $command->lastName(),
            $command->sendInvite(),
            $command->userData(),
            $this->checksUniqueUsersEmail,
        );

        $this->userRepo->save($user);
    }
}
