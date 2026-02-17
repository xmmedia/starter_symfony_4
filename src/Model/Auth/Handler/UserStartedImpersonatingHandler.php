<?php

declare(strict_types=1);

namespace App\Model\Auth\Handler;

use App\Model\Auth\Auth;
use App\Model\Auth\AuthList;
use App\Model\Auth\Command\UserStartedImpersonating;

final readonly class UserStartedImpersonatingHandler
{
    public function __construct(private AuthList $authRepo)
    {
    }

    public function __invoke(UserStartedImpersonating $command): void
    {
        $auth = Auth::startedImpersonating(
            $command->authId(),
            $command->adminUserId(),
            $command->impersonatedUserId(),
            $command->impersonatedEmail(),
            $command->userAgent(),
            $command->ipAddress(),
            $command->route(),
        );

        $this->authRepo->save($auth);
    }
}
