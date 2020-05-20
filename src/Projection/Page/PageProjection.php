<?php

declare(strict_types=1);

namespace App\Projection\Page;

use App\Model\Page\Event;
use Prooph\Bundle\EventStore\Projection\ReadModelProjection;
use Prooph\EventStore\Projection\ReadModelProjector;
use Xm\SymfonyBundle\EventSourcing\AggregateChanged;

/**
 * @method \Prooph\EventStore\Projection\ReadModel readModel()
 */
class PageProjection implements ReadModelProjection
{
    public function project(ReadModelProjector $projector): ReadModelProjector
    {
        $types = [
            'content'       => 'json',
            'last_modified' => 'datetime',
        ];

        $projector->fromStream('page')
            ->when([
                Event\PageWasAdded::class => function (
                    array $state,
                    Event\PageWasAdded $event
                ) use ($types): void {
                    /** @var PageReadModel $readModel */
                    /** @var ReadModelProjector $this */
                    $readModel = $this->readModel();
                    $readModel->stack(
                        'insert',
                        [
                            'page_id' => $event->aggregateId(),
                        ] + self::parseEvent($event),
                        $types
                    );
                },
            ]);

        return $projector;
    }

    /**
     * @param Event\PageWasAdded|AggregateChanged $event
     */
    public static function parseEvent(AggregateChanged $event): array
    {
        return [
            'path'             => $event->path()->toString(),
            'title'            => $event->title()->toString(),
            'content'          => $event->content()->toArray(),
            'last_modified'    => $event->createdAt(),
            'last_modified_by' => 'cli' !== $event->metadata()['issuedBy'] ?: null,
        ];
    }
}
