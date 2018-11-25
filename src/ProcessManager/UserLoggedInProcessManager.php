<?php

declare(strict_types=1);

namespace App\ProcessManager;

use App\Model\Auth\Event\UserLoggedIn;
use App\Model\User\Command\UserLoggedIn as UserLoggedInCommand;
use Symfony\Component\Messenger\MessageBusInterface;

class UserLoggedInProcessManager
{
    /** @var MessageBusInterface */
    private $commandBus;

    public function __construct(MessageBusInterface $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    public function __invoke(UserLoggedIn $event): void
    {
        $this->commandBus->dispatch(UserLoggedInCommand::now($event->userId()));
    }
}
