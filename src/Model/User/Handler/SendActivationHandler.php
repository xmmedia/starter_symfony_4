<?php

declare(strict_types=1);

namespace App\Model\User\Handler;

use App\Infrastructure\Email\EmailGateway;
use App\Model\Email;
use App\Model\User\Command\SendActivation;
use App\Model\User\Exception\UserNotFound;
use App\Model\User\UserList;

class SendActivationHandler
{
    /** @var UserList */
    private $userRepo;

    /** @var EmailGateway */
    private $emailGateway;

    public function __construct(UserList $userRepo, EmailGateway $emailGateway)
    {
        $this->userRepo = $userRepo;
        $this->emailGateway = $emailGateway;
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

        // @todo set confirmation token or other token? + event/method on User

        $messageId = $this->emailGateway->send(
            // @todo-symfony
            9106459,
            Email::fromString($command->email()->toString(), $name),
            [
                'activateUrl' => '',
                'name'        => $name,
            ]
        );

        $user->inviteSent($messageId);

        $this->userRepo->save($user);
    }
}
