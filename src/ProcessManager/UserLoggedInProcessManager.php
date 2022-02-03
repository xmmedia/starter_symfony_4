<?php

declare(strict_types=1);

namespace App\ProcessManager;

use App\Model\Auth\Event\UserLoggedIn;
use App\Model\User\Command\UserLoggedIn as UserLoggedInCommand;
use Symfony\Component\Messenger\MessageBusInterface;

class UserLoggedInProcessManager
{
    public function __construct(private MessageBusInterface $commandBus)
    {
    }

    public function __invoke(UserLoggedIn $event): void
    {
        $this->commandBus->dispatch(UserLoggedInCommand::now($event->userId()));
    }
}
