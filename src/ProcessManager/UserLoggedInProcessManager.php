<?php

declare(strict_types=1);

namespace App\ProcessManager;

use Symfony\Component\Messenger\MessageBusInterface;
use Xm\SymfonyBundle\Model\Auth\Event\UserLoggedIn;
use Xm\SymfonyBundle\Model\User\Command\UserLoggedIn as UserLoggedInCommand;

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
