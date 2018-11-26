<?php

declare(strict_types=1);

namespace App\Model\User\Handler;

use App\Infrastructure\Email\EmailGateway;
use App\Model\Email;
use App\Model\User\Command\SendActivation;
use App\Model\User\Exception\UserNotFound;
use App\Model\User\UserList;
use App\Security\TokenGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class SendActivationHandler
{
    /** @var UserList */
    private $userRepo;

    /** @var EmailGateway */
    private $emailGateway;

    /** @var RouterInterface|\Symfony\Bundle\FrameworkBundle\Routing\Router */
    private $router;

    /** @var TokenGenerator */
    private $tokenGenerator;

    public function __construct(
        UserList $userRepo,
        EmailGateway $emailGateway,
        RouterInterface $router,
        TokenGenerator $tokenGenerator
    ) {
        $this->userRepo = $userRepo;
        $this->emailGateway = $emailGateway;
        $this->router = $router;
        $this->tokenGenerator = $tokenGenerator;
    }

    public function __invoke(SendActivation $command): void
    {
        $user = $this->userRepo->get($command->userId());
        if (!$user) {
            throw UserNotFound::withUserId($command->userId());
        }

        $name = trim(sprintf(
            '%s %s',
            $command->firstName(),
            $command->lastName()
        ));
        $token = ($this->tokenGenerator)();

        $verifyUrl = $this->router->generate(
            'user_verify',
            ['token' => $token],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $messageId = $this->emailGateway->send(
            // @todo-symfony
            9106459,
            Email::fromString($command->email()->toString(), $name),
            [
                'verifyUrl' => $verifyUrl,
                'name'      => $name,
                'email'     => $command->email()->toString(),
            ]
        );

        $user->inviteSent($token, $messageId);

        $this->userRepo->save($user);
    }
}
