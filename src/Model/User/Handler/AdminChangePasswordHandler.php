<?php

declare(strict_types=1);

namespace App\Model\User\Handler;

use App\Model\User\Command\AdminChangePassword;
use App\Model\User\Exception\UserNotFound;
use App\Model\User\UserList;

final readonly class AdminChangePasswordHandler
{
    public function __construct(private readonly UserList $userRepo)
    {
    }

    public function __invoke(AdminChangePassword $command): void
    {
        $user = $this->userRepo->get($command->userId());

        if (!$user) {
            throw UserNotFound::withUserId($command->userId());
        }

        $user->changePasswordByAdmin($command->hashedPassword());

        $this->userRepo->save($user);
    }
}
