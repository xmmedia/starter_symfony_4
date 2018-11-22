<?php

declare(strict_types=1);

namespace App\Model\User\Handler;

use App\Model\User\Command\UpdateUserProfile;
use App\Model\User\Exception\UserNotFound;
use App\Model\User\UserList;

class UpdateUserProfileHandler
{
    /** @var UserList */
    private $userRepo;

    public function __construct(UserList $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    public function __invoke(UpdateUserProfile $command): void
    {
        $user = $this->userRepo->get($command->userId());

        if (!$user) {
            throw UserNotFound::withJobId($command->userId());
        }

        $user->updateFromProfile(
            $command->email(),
            $command->firstName(),
            $command->lastName()
        );

        $this->userRepo->save($user);
    }
}
