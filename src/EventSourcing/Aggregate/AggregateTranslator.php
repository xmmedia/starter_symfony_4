<?php

declare(strict_types=1);

namespace App\EventSourcing\Aggregate;

use App\Messaging\Message;
use App\EventSourcing\Aggregate\AggregateTranslatorInterface as EventStoreAggregateTranslator;

final class AggregateTranslator implements EventStoreAggregateTranslator
{
    /** @var AggregateRootDecorator */
    private $aggregateRootDecorator;

    /**
     * @param object $eventSourcedAggregateRoot
     */
    public function extractAggregateVersion($eventSourcedAggregateRoot): int
    {
        return $this->getAggregateRootDecorator()->extractAggregateVersion($eventSourcedAggregateRoot);
    }

    /**
     * @param object $anEventSourcedAggregateRoot
     */
    public function extractAggregateId($anEventSourcedAggregateRoot): string
    {
        return $this->getAggregateRootDecorator()->extractAggregateId($anEventSourcedAggregateRoot);
    }

    /**
     * @return object reconstructed AggregateRoot
     */
    public function reconstituteAggregateFromHistory(AggregateType $aggregateType, \Iterator $historyEvents)
    {
        if (!$aggregateRootClass = $aggregateType->mappedClass()) {
            $aggregateRootClass = $aggregateType->toString();
        }

        return $this->getAggregateRootDecorator()
            ->fromHistory($aggregateRootClass, $historyEvents);
    }

    /**
     * @param object $anEventSourcedAggregateRoot
     *
     * @return Message[]
     */
    public function extractPendingStreamEvents($anEventSourcedAggregateRoot): array
    {
        return $this->getAggregateRootDecorator()->extractRecordedEvents($anEventSourcedAggregateRoot);
    }

    /**
     * @param object $anEventSourcedAggregateRoot
     */
    public function replayStreamEvents($anEventSourcedAggregateRoot, \Iterator $events): void
    {
        $this->getAggregateRootDecorator()->replayStreamEvents($anEventSourcedAggregateRoot, $events);
    }

    public function getAggregateRootDecorator(): AggregateRootDecorator
    {
        if (null === $this->aggregateRootDecorator) {
            $this->aggregateRootDecorator = AggregateRootDecorator::newInstance();
        }

        return $this->aggregateRootDecorator;
    }

    public function setAggregateRootDecorator(AggregateRootDecorator $anAggregateRootDecorator): void
    {
        $this->aggregateRootDecorator = $anAggregateRootDecorator;
    }
}
