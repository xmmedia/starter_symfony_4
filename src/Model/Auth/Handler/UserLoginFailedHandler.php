<?php

declare(strict_types=1);

namespace App\Model\Auth\Handler;

use App\Model\Auth\Auth;
use App\Model\Auth\AuthList;
use App\Model\Auth\Command\UserLoginFailed;

class UserLoginFailedHandler
{
    private AuthList $authRepo;

    public function __construct(AuthList $authRepo)
    {
        $this->authRepo = $authRepo;
    }

    public function __invoke(UserLoginFailed $command): void
    {
        $auth = Auth::failure(
            $command->authId(),
            $command->email(),
            $command->userAgent(),
            $command->ipAddress(),
            $command->exceptionMessage(),
        );

        $this->authRepo->save($auth);
    }
}
