<?php

declare(strict_types=1);

namespace App\Model\User\Handler;

use App\Model\User\Command\VerifyUser;
use App\Model\User\Exception\UserNotFound;
use App\Model\User\UserList;

final readonly class VerifyUserHandler
{
    public function __construct(private readonly UserList $userRepo)
    {
    }

    public function __invoke(VerifyUser $command): void
    {
        $user = $this->userRepo->get($command->userId());

        if (!$user) {
            throw UserNotFound::withUserId($command->userId());
        }

        $user->verify();

        $this->userRepo->save($user);
    }
}
