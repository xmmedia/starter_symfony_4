<?php

declare(strict_types=1);

namespace App\Model\User\Handler;

use App\Model\User\Command\UpdateUserProfile;
use App\Model\User\Exception\UserNotFound;
use App\Model\User\Service\ChecksUniqueUsersEmail;
use App\Model\User\UserList;

final readonly class UpdateUserProfileHandler
{
    public function __construct(
        private readonly UserList $userRepo,
        private readonly ChecksUniqueUsersEmail $checksUniqueUsersEmail,
    ) {
    }

    public function __invoke(UpdateUserProfile $command): void
    {
        $user = $this->userRepo->get($command->userId());

        if (!$user) {
            throw UserNotFound::withUserId($command->userId());
        }

        $user->update(
            $command->email(),
            $command->firstName(),
            $command->lastName(),
            $command->userData(),
            $this->checksUniqueUsersEmail,
        );

        $this->userRepo->save($user);
    }
}
