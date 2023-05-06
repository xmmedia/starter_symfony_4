<?php

declare(strict_types=1);

namespace App\Model\User\Handler;

use App\Model\User\Command\ActivateUserByAdmin;
use App\Model\User\Exception\UserNotFound;
use App\Model\User\UserList;

final readonly class ActivateUserByAdminHandler
{
    public function __construct(private readonly UserList $userRepo)
    {
    }

    public function __invoke(ActivateUserByAdmin $command): void
    {
        $user = $this->userRepo->get($command->userId());

        if (!$user) {
            throw UserNotFound::withUserId($command->userId());
        }

        $user->activateByAdmin();

        $this->userRepo->save($user);
    }
}
