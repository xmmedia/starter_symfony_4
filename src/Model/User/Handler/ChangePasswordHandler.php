<?php

declare(strict_types=1);

namespace App\Model\User\Handler;

use App\Model\User\Command\ChangePassword;
use App\Model\User\Exception\UserNotFound;
use App\Model\User\UserList;

class ChangePasswordHandler
{
    /** @var UserList */
    private $userRepo;

    public function __construct(UserList $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    public function __invoke(ChangePassword $command): void
    {
        $user = $this->userRepo->get($command->userId());

        if (!$user) {
            throw UserNotFound::withUserId($command->userId());
        }

        $user->changePassword($command->encodedPassword());

        $this->userRepo->save($user);
    }
}
