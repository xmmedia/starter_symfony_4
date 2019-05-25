<?php

declare(strict_types=1);

namespace App\Model\User\Handler;

use App\Infrastructure\Email\EmailGatewayInterface;
use App\Infrastructure\Email\EmailTemplate;
use App\Model\Email;
use App\Model\User\Command\InitiatePasswordRecovery;
use App\Model\User\Exception\UserNotFound;
use App\Model\User\UserList;
use App\Security\TokenGeneratorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class InitiatePasswordRecoveryHandler
{
    /** @var UserList */
    private $userRepo;

    /** @var EmailGatewayInterface|\App\Infrastructure\Email\EmailGateway */
    private $emailGateway;

    /** @var RouterInterface|\Symfony\Bundle\FrameworkBundle\Routing\Router */
    private $router;

    /** @var TokenGeneratorInterface|\App\Security\TokenGenerator */
    private $tokenGenerator;

    public function __construct(
        UserList $userRepo,
        EmailGatewayInterface $emailGateway,
        RouterInterface $router,
        TokenGeneratorInterface $tokenGenerator
    ) {
        $this->userRepo = $userRepo;
        $this->emailGateway = $emailGateway;
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
            ['action' => 'reset', 'token' => $token],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $messageId = $this->emailGateway->send(
            EmailTemplate::PASSWORD_RESET,
            Email::fromString($command->email()->toString()),
            [
                'resetUrl' => $resetUrl,
                'email'    => $command->email()->toString(),
            ]
        );

        $user->passwordRecoverySent($token, $messageId);

        $this->userRepo->save($user);
    }
}
