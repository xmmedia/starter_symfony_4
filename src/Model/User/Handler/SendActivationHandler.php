<?php

declare(strict_types=1);

namespace App\Model\User\Handler;

use App\Model\User\Command\SendActivation;
use App\Model\User\Exception\UserAlreadyVerified;
use App\Model\User\Exception\UserNotFound;
use App\Model\User\UserList;
use App\Security\TokenGeneratorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Xm\SymfonyBundle\Infrastructure\Email\EmailGatewayInterface;
use Xm\SymfonyBundle\Model\Email;
use Xm\SymfonyBundle\Util\StringUtil;

class SendActivationHandler
{
    public function __construct(
        private UserList $userRepo,
        private EmailGatewayInterface $emailGateway,
        private string $template,
        private RouterInterface $router,
        private TokenGeneratorInterface $tokenGenerator,
    ) {
    }

    public function __invoke(SendActivation $command): void
    {
        $user = $this->userRepo->get($command->userId());
        if (!$user) {
            throw UserNotFound::withUserId($command->userId());
        }

        if ($user->verified()) {
            throw UserAlreadyVerified::triedToSendVerification($command->userId());
        }

        $name = StringUtil::trim(sprintf(
            '%s %s',
            $command->firstName(),
            $command->lastName(),
        ));
        $token = ($this->tokenGenerator)();

        $verifyUrl = $this->router->generate(
            'user_verify',
            ['token' => $token],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );

        $messageId = $this->emailGateway->send(
            $this->template,
            Email::fromString($command->email()->toString(), $name),
            [
                'verifyUrl' => $verifyUrl,
                'name'      => $name,
                'email'     => $command->email()->toString(),
            ],
        );

        $user->inviteSent($token, $messageId);

        $this->userRepo->save($user);
    }
}
