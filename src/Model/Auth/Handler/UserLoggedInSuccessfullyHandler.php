<?php

declare(strict_types=1);

namespace App\Model\Auth\Handler;

use App\Model\Auth\Auth;
use App\Model\Auth\AuthList;
use App\Model\Auth\Command\UserLoggedInSuccessfully;

final readonly class UserLoggedInSuccessfullyHandler
{
    public function __construct(private AuthList $authRepo)
    {
    }

    public function __invoke(UserLoggedInSuccessfully $command): void
    {
        $auth = Auth::success(
            $command->authId(),
            $command->userId(),
            $command->email(),
            $command->userAgent(),
            $command->ipAddress(),
            $command->route(),
        );

        $this->authRepo->save($auth);
    }
}
