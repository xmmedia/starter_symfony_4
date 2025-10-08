<?php

declare(strict_types=1);

namespace App\Model\Auth\Handler;

use App\Model\Auth\Auth;
use App\Model\Auth\AuthList;
use App\Model\Auth\Command\UserLoginFailed;

final readonly class UserLoginFailedHandler
{
    public function __construct(private AuthList $authRepo)
    {
    }

    public function __invoke(UserLoginFailed $command): void
    {
        $auth = Auth::failure(
            $command->authId(),
            $command->email(),
            $command->userId(),
            $command->userAgent(),
            $command->ipAddress(),
            $command->exceptionMessage(),
            $command->route(),
        );

        $this->authRepo->save($auth);
    }
}
