<?php

declare(strict_types=1);

namespace App\Projection\User;

use App\Model\User\Event;
use Prooph\Bundle\EventStore\Projection\ReadModelProjection;
use Prooph\EventStore\Projection\ReadModelProjector;

class UserProjection implements ReadModelProjection
{
    public function project(ReadModelProjector $projector): ReadModelProjector
    {
        $projector->fromStream('user')
            ->when([
                Event\UserWasCreatedByAdmin::class => function (
                    array $state,
                    Event\UserWasCreatedByAdmin $event
                ): void {
                    /** @var UserReadModel $readModel */
                    /** @var ReadModelProjector $this */
                    $readModel = $this->readModel();
                    $readModel->stack('insert', [
                        'id'         => $event->aggregateId(),
                        'email'      => $event->email()->toString(),
                        'password'   => $event->encodedPassword(),
                        'enabled'    => $event->enabled(),
                        'roles'      => [$event->role()->getRole()],
                        'first_name' => $event->firstName()->toString(),
                        'last_name'  => $event->lastName()->toString(),
                    ], [
                        'roles' => 'array',
                    ]);
                },

                Event\UserUpdatedProfile::class => function (
                    array $state,
                    Event\UserUpdatedProfile $event
                ): void {
                    /** @var UserReadModel $readModel */
                    /** @var ReadModelProjector $this */
                    $readModel = $this->readModel();
                    $readModel->stack(
                        'update',
                        $event->userId()->toString(),
                        [
                            'email'      => $event->email()->toString(),
                            'first_name' => $event->firstName()->toString(),
                            'last_name'  => $event->lastName()->toString(),
                        ]
                    );
                },
            ]);

        return $projector;
    }
}
