<?php

declare(strict_types=1);

namespace App\ProcessManager;

use App\Model\User\Command\SendActivation;
use App\Model\User\Event\MinimalUserWasAddedByAdmin;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class UserInviteForMinimumProcessManager
{
    public function __construct(private MessageBusInterface $commandBus)
    {
    }

    public function __invoke(MinimalUserWasAddedByAdmin $event): void
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
