<?php

declare(strict_types=1);

namespace App\ProcessManager;

use App\Model\User\Command\SendPasswordChangedNotification;
use App\Model\User\Event\ChangedPassword;
use App\Model\User\Event\UserActivated;
use App\Model\User\Event\UserUpdatedProfile;
use App\Model\User\UserList;
use Carbon\CarbonImmutable;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class ChangedPasswordProcessManager
{
    public function __construct(private UserList $userRepo, private MessageBusInterface $commandBus)
    {
    }

    public function __invoke(ChangedPassword $event): void
    {
        $events = $this->userRepo->getEvents($event->userId());

        $lastEvent = null;
        foreach ($events as $previousEvent) {
            // found the current event & last event is UserActivated then don't send notification
            if ($previousEvent->uuid()->toString() === $event->uuid()->toString()) {
                if ($lastEvent instanceof UserActivated) {
                    return;
                }
            }

            $lastEvent = $previousEvent;
        }

        $previousChange = null;
        foreach (array_reverse(iterator_to_array($events)) as $previousEvent) {
            if (!$previousEvent instanceof UserUpdatedProfile) {
                continue;
            }

            if ($previousEvent->uuid()->toString() === $event->uuid()->toString()) {
                continue;
            }

            /** @var ChangedPassword $previousChange */
            $previousChange = $previousEvent;
            break;
        }

        // don't send the email if the previous change was less than 10 minutes ago
        if ($previousChange && CarbonImmutable::instance($previousChange->createdAt())->diffInMinutes($event->createdAt()) < 10) {
            return;
        }

        $this->commandBus->dispatch(
            SendPasswordChangedNotification::now($event->userId()),
        );
    }
}
