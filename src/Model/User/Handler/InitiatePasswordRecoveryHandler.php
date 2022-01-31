<?php

declare(strict_types=1);

namespace App\Model\User\Handler;

use App\Model\User\Command\InitiatePasswordRecovery;
use App\Model\User\Exception\UserNotFound;
use App\Model\User\UserList;
use App\Security\TokenGeneratorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Xm\SymfonyBundle\Infrastructure\Email\EmailGatewayInterface;
use Xm\SymfonyBundle\Model\Email;

class InitiatePasswordRecoveryHandler
{
    private UserList $userRepo;
    private EmailGatewayInterface $emailGateway;
    private string $template;
    private RouterInterface $router;
    private TokenGeneratorInterface $tokenGenerator;

    public function __construct(
        UserList $userRepo,
        EmailGatewayInterface $emailGateway,
        string $template,
        RouterInterface $router,
        TokenGeneratorInterface $tokenGenerator,
    ) {
        $this->userRepo = $userRepo;
        $this->emailGateway = $emailGateway;
        $this->template = $template;
        $this->router = $router;
        $this->tokenGenerator = $tokenGenerator;
    }

    public function __invoke(InitiatePasswordRecovery $command): void
    {
        $user = $this->userRepo->get($command->userId());
        if (!$user) {
            throw UserNotFound::withUserId($command->userId());
        }

        $token = ($this->tokenGenerator)();

        $resetUrl = $this->router->generate(
            'user_reset',
            ['token' => $token],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );

        $messageId = $this->emailGateway->send(
            $this->template,
            Email::fromString($command->email()->toString()),
            [
                'resetUrl' => $resetUrl,
                'email'    => $command->email()->toString(),
            ],
        );

        $user->passwordRecoverySent($token, $messageId);

        $this->userRepo->save($user);
    }
}
