<?php

declare(strict_types=1);

namespace App\Model\Auth\Handler;

use App\Model\Auth\Auth;
use App\Model\Auth\AuthList;
use App\Model\Auth\Command\UserLoggedInSuccessfully;

class UserLoggedInSuccessfullyHandler
{
    /** @var AuthList */
    private $authRepo;

    public function __construct(AuthList $authRepo)
    {
        $this->authRepo = $authRepo;
    }

    public function __invoke(UserLoggedInSuccessfully $command): void
    {
        $auth = Auth::success(
            $command->authId(),
            $command->userId(),
            $command->email(),
            $command->userAgent(),
            $command->ipAddress()
        );

        $this->authRepo->save($auth);
    }
}
