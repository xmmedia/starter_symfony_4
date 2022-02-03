<?php

declare(strict_types=1);

namespace App\ProcessManager;

use App\Model\User\Command\SendActivation;
use App\Model\User\Event\UserWasAddedByAdmin;
use Symfony\Component\Messenger\MessageBusInterface;

class UserInviteProcessManager
{
    public function __construct(private MessageBusInterface $commandBus)
    {
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
                $event->lastName(),
            ),
        );
    }
}
