<?php

declare(strict_types=1);

namespace App\ProcessManager;

use App\Model\User\Command\SendActivation;
use App\Model\User\Event\UserWasAddedByAdmin;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class UserInviteProcessManager
{
    public function __construct(private MessageBusInterface $commandBus)
    {
    }

    public function __invoke(UserWasAddedByAdmin $event): void
    {
        // an inactive user can't activate their account, so the invite would fail
        if (!$event->sendInvite() || !$event->active()) {
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
