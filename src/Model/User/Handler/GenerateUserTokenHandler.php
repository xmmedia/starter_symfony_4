<?php

declare(strict_types=1);

namespace App\Model\User\Handler;

use App\Model\User\Command\GenerateUserToken;
use App\Model\User\Exception\UserNotFound;
use App\Model\User\UserList;
use App\Security\TokenGeneratorInterface;

class GenerateUserTokenHandler
{
    public function __construct(
        private UserList $userRepo,
        private TokenGeneratorInterface $tokenGenerator,
    ) {
    }

    public function __invoke(GenerateUserToken $command): void
    {
        $user = $this->userRepo->get($command->userId());
        if (!$user) {
            throw UserNotFound::withUserId($command->userId());
        }

        $user->tokenGenerated(($this->tokenGenerator)());

        $this->userRepo->save($user);
    }
}
