<?php

declare(strict_types=1);

namespace App\ProcessManager;

use Symfony\Component\Messenger\MessageBusInterface;
use Xm\SymfonyBundle\Model\User\Command\SendActivation;
use Xm\SymfonyBundle\Model\User\Event\UserWasAddedByAdmin;

class UserInviteProcessManager
{
    /** @var MessageBusInterface */
    private $commandBus;

    public function __construct(MessageBusInterface $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    public function __invoke(UserWasAddedByAdmin $event): void
    {
        if (!$event->sendInvite()) {
            return;
        }

        $this->commandBus->dispatch(
            SendActivation::now(
                $event->userId(),
                $event->email(),
                $event->firstName(),
                $event->lastName()
            )
        );
    }
}
