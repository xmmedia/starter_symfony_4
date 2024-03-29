<?php

declare(strict_types=1);

namespace App\Model\User\Handler;

use App\Model\User\Command\AdminUpdateUser;
use App\Model\User\Exception\UserNotFound;
use App\Model\User\Service\ChecksUniqueUsersEmail;
use App\Model\User\UserList;

final readonly class AdminUpdateUserHandler
{
    public function __construct(
        private UserList $userRepo,
        private ChecksUniqueUsersEmail $checksUniqueUsersEmail,
    ) {
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
            $command->userData(),
            $this->checksUniqueUsersEmail,
        );

        $this->userRepo->save($user);
    }
}
