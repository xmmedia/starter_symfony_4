<?php

declare(strict_types=1);

namespace App\Model\User\Handler;

use App\Model\User\Command\AdminUpdateUser;
use App\Model\User\Exception\UserNotFound;
use App\Model\User\Service\ChecksUniqueUsersEmail;
use App\Model\User\UserList;

class AdminUpdateUserHandler
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

    public function __invoke(AdminUpdateUser $command): void
    {
        $user = $this->userRepo->get($command->userId());

        if (!$user) {
            throw UserNotFound::withUserId($command->userId());
        }

        $user->updateByAdmin(
            $command->email(),
            $command->role(),
            $command->firstName(),
            $command->lastName(),
            $this->checksUniqueUsersEmail,
        );

        $this->userRepo->save($user);
    }
}
