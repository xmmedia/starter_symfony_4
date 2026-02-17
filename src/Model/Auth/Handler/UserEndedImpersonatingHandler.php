<?php

declare(strict_types=1);

namespace App\Model\Auth\Handler;

use App\Model\Auth\Auth;
use App\Model\Auth\AuthList;
use App\Model\Auth\Command\UserEndedImpersonating;

final readonly class UserEndedImpersonatingHandler
{
    public function __construct(private AuthList $authRepo)
    {
    }

    public function __invoke(UserEndedImpersonating $command): void
    {
        $auth = Auth::endedImpersonating(
            $command->authId(),
            $command->adminUserId(),
            $command->userAgent(),
            $command->ipAddress(),
            $command->route(),
        );

        $this->authRepo->save($auth);
    }
}
