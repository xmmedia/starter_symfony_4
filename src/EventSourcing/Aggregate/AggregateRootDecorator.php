<?php

declare(strict_types=1);

namespace App\EventSourcing\Aggregate;

use App\EventSourcing\AggregateChanged;

class AggregateRootDecorator extends AggregateRoot
{
    public static function newInstance(): self
    {
        return new static();
    }

    public function extractAggregateVersion(AggregateRoot $anAggregateRoot): int
    {
        return $anAggregateRoot->version;
    }

    /**
     * @return AggregateChanged[]
     */
    public function extractRecordedEvents(AggregateRoot $anAggregateRoot): array
    {
        return $anAggregateRoot->popRecordedEvents();
    }

    public function extractAggregateId(AggregateRoot $anAggregateRoot): string
    {
        return $anAggregateRoot->aggregateId();
    }

    public function fromHistory($arClass, \Iterator $aggregateChangedEvents): AggregateRoot
    {
        if (! \class_exists($arClass)) {
            throw new \RuntimeException(
                \sprintf('Aggregate root class %s cannot be found', $arClass)
            );
        }

        return $arClass::reconstituteFromHistory($aggregateChangedEvents);
    }

    public function replayStreamEvents(AggregateRoot $aggregateRoot, \Iterator $events): void
    {
        $aggregateRoot->replay($events);
    }

    protected function aggregateId(): string
    {
        throw new \BadMethodCallException('The AggregateRootDecorator does not have an id');
    }

    protected function apply(AggregateChanged $e): void
    {
    }
}
