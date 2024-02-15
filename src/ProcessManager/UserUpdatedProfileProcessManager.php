<?php

declare(strict_types=1);

namespace App\ProcessManager;

use App\Model\User\Command\SendProfileUpdatedNotification;
use App\Model\User\Event\UserUpdatedProfile;
use App\Model\User\UserList;
use Carbon\CarbonImmutable;
use Prooph\EventStore\EventStore;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class UserUpdatedProfileProcessManager
{
    public function __construct(private UserList $userRepo, private MessageBusInterface $commandBus)
    {
    }

    public function __invoke(UserUpdatedProfile $event): void
    {
        $events = $this->userRepo->getEvents($event->userId(), UserUpdatedProfile::class);

        $previousUpdate = null;
        foreach (array_reverse(iterator_to_array($events)) as $previousEvent) {
            if ($previousEvent->uuid()->toString() === $event->uuid()->toString()) {
                continue;
            }

            /** @var UserUpdatedProfile $previousUpdate */
            $previousUpdate = $previousEvent;
            break;
        }

        // don't send the email if the previous update was less than an hour ago
        if ($previousUpdate && CarbonImmutable::instance($previousUpdate->createdAt())->diffInHours($event->createdAt()) < 1) {
            return;
        }

        $this->commandBus->dispatch(
            SendProfileUpdatedNotification::now($event->userId()),
        );
    }
}
