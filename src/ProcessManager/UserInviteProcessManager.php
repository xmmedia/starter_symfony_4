<?php

declare(strict_types=1);

namespace App\ProcessManager;

use App\Model\User\Command\SendActivation;
use App\Model\User\Event\UserWasCreatedByAdmin;
use Symfony\Component\Messenger\MessageBusInterface;

class UserInviteProcessManager
{
    /** @var MessageBusInterface */
    private $commandBus;

    public function __construct(MessageBusInterface $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    public function __invoke(UserWasCreatedByAdmin $event): void
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
