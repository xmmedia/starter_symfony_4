<?php

declare(strict_types=1);

namespace App\Model\User\Handler;

use App\Model\User\Command\GenerateUserToken;
use App\Model\User\Exception\UserNotFound;
use App\Model\User\UserList;
use App\Security\TokenGeneratorInterface;
use Xm\SymfonyBundle\Infrastructure\Email\EmailGatewayInterface;

class GenerateUserTokenHandler
{
    private UserList $userRepo;
    private EmailGatewayInterface $emailGateway;
    private TokenGeneratorInterface $tokenGenerator;

    public function __construct(
        UserList $userRepo,
        EmailGatewayInterface $emailGateway,
        TokenGeneratorInterface $tokenGenerator,
    ) {
        $this->userRepo = $userRepo;
        $this->emailGateway = $emailGateway;
        $this->tokenGenerator = $tokenGenerator;
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
