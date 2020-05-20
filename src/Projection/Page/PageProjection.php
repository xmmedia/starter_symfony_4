<?php

declare(strict_types=1);

namespace App\Projection\Page;

use App\Model\Page\Event;
use Prooph\Bundle\EventStore\Projection\ReadModelProjection;
use Prooph\EventStore\Projection\ReadModelProjector;

/**
 * @method \Prooph\EventStore\Projection\ReadModel readModel()
 */
class PageProjection implements ReadModelProjection
{
    public function project(ReadModelProjector $projector): ReadModelProjector
    {
        $projector->fromStream('page')
            ->when([
                Event\PageWasAdded::class => function (
                    array $state,
                    Event\PageWasAdded $event
                ): void {
                    /** @var PageReadModel $readModel */
                    /** @var ReadModelProjector $this */
                    $readModel = $this->readModel();
                    $readModel->stack('insert', [
                        'id'        => $event->aggregateId(),
                        'name'      => $event->name(),
                        'email'     => $event->email()->toString(),
                        'message'   => $event->message(),
                        'submitted' => $event->createdAt(),
                    ], [
                        'submitted' => 'datetime',
                    ]);
                },
            ]);

        return $projector;
    }
}
