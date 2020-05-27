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
                            'path'     => $event->path()->toString(),
                            'template' => $event->template()->toString(),
                            'page_id'  => $event->aggregateId(),
                        ] + self::parseEvent($event),
                        $types
                    );
                },

                Event\PageWasPublished::class => function (
                    array $state,
                    Event\PageWasPublished $event
                ): void {
                    /** @var PageReadModel $readModel */
                    /** @var ReadModelProjector $this */
                    $readModel = $this->readModel();
                    $readModel->stack(
                        'update',
                        $event->aggregateId(),
                        ['published' => true]
                    );
                },
                Event\PageWasUnpublished::class => function (
                    array $state,
                    Event\PageWasUnpublished $event
                ): void {
                    /** @var PageReadModel $readModel */
                    /** @var ReadModelProjector $this */
                    $readModel = $this->readModel();
                    $readModel->stack(
                        'update',
                        $event->aggregateId(),
                        ['published' => false]
                    );
                },

                Event\PageWasUpdated::class => function (
                    array $state,
                    Event\PageWasUpdated $event
                ) use ($types): void {
                    /** @var PageReadModel $readModel */
                    /** @var ReadModelProjector $this */
                    $readModel = $this->readModel();
                    $readModel->stack(
                        'update',
                        $event->aggregateId(),
                        self::parseEvent($event),
                        $types
                    );
                },
                Event\PagePathWasChanged::class => function (
                    array $state,
                    Event\PagePathWasChanged $event
                ) use ($types): void {
                    /** @var PageReadModel $readModel */
                    /** @var ReadModelProjector $this */
                    $readModel = $this->readModel();
                    $readModel->stack(
                        'update',
                        $event->aggregateId(),
                        [
                            'path' => $event->newPath()->toString(),
                        ] + self::generateLastModified($event),
                        $types
                    );
                },
                Event\PageTemplateWasChanged::class => function (
                    array $state,
                    Event\PageTemplateWasChanged $event
                ) use ($types): void {
                    /** @var PageReadModel $readModel */
                    /** @var ReadModelProjector $this */
                    $readModel = $this->readModel();
                    $readModel->stack(
                        'update',
                        $event->aggregateId(),
                        [
                            'template' => $event->newTemplate()->toString(),
                        ] + self::generateLastModified($event),
                        $types
                    );
                },

                Event\PageWasDeleted::class => function (
                    array $state,
                    Event\PageWasDeleted $event
                ): void {
                    /** @var PageReadModel $readModel */
                    /** @var ReadModelProjector $this */
                    $readModel = $this->readModel();
                    $readModel->stack(
                        'remove',
                        $event->aggregateId()
                    );
                },
            ]);

        return $projector;
    }

    /**
     * @param Event\PageWasAdded|Event\PageWasUpdated|AggregateChanged $event
     */
    public static function parseEvent(AggregateChanged $event): array
    {
        return [
            'title'   => $event->title()->toString(),
            'content' => $event->content()->toArray(),
        ] + self::generateLastModified($event);
    }

    /**
     * @param Event\PageWasAdded|Event\PageWasUpdated|Event\PagePathWasChanged|AggregateChanged $event
     */
    public static function generateLastModified(AggregateChanged $event): array
    {
        $issuedBy = null;
        if ('cli' !== $event->metadata()['issuedBy']) {
            $issuedBy = $event->metadata()['issuedBy'];
        }

        return [
            'last_modified'    => $event->createdAt(),
            'last_modified_by' => $issuedBy,
        ];
    }
}
