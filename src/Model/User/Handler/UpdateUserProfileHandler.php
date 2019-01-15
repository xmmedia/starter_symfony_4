<?php

declare(strict_types=1);

namespace App\Model\User\Handler;

use App\Model\User\Command\UpdateUserProfile;
use App\Model\User\Exception\UserNotFound;
use App\Model\User\Service\ChecksUniqueUsersEmail;
use App\Model\User\UserList;

class UpdateUserProfileHandler
{
    /** @var UserList */
    private $userRepo;

    /** @var ChecksUniqueUsersEmail */
    private $checksUniqueUsersEmailAddress;

    public function __construct(
        UserList $userRepo,
        ChecksUniqueUsersEmail $checksUniqueUsersEmailAddress
    ) {
        $this->userRepo = $userRepo;
        $this->checksUniqueUsersEmailAddress = $checksUniqueUsersEmailAddress;
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
            $this->checksUniqueUsersEmailAddress
        );

        $this->userRepo->save($user);
    }
}
