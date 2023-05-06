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

final readonly class InitiatePasswordRecoveryHandler
{
    public function __construct(
        private readonly UserList $userRepo,
        private readonly EmailGatewayInterface $emailGateway,
        private readonly string $template,
        private readonly RouterInterface $router,
        private readonly TokenGeneratorInterface $tokenGenerator,
    ) {
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
        // @todo consider nonce: bin2hex(random_bytes(16)) + check how validated here: https://symfony.com/doc/current/security/custom_authentication_provider.html#the-authentication-provider
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
