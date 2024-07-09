<?php

declare(strict_types=1);

namespace App\Projection\Auth;

use App\Model\Auth\Event;
use Prooph\Bundle\EventStore\Projection\ReadModelProjection;
use Prooph\EventStore\Projection\ReadModelProjector;

class AuthProjection implements ReadModelProjection
{
    public function project(ReadModelProjector $projector): ReadModelProjector
    {
        $projector->fromStream('auth')
            ->when([
                Event\UserLoggedIn::class => function (
                    array $state,
                    Event\UserLoggedIn $event,
                ): void {
                    /** @var AuthReadModel $readModel */
                    /** @var ReadModelProjector $this */
                    $readModel = $this->readModel();
                    $readModel->stack(
                        'LoggedIn',
                        $event->userId()->toString(),
                        $event->createdAt(),
                    );
                },
            ]);

        return $projector;
    }
}
