<?php

declare(strict_types=1);

namespace App\EventSourcing\Aggregate;

use App\Messaging\Message;

interface AggregateTranslatorInterface
{
    /**
     * @param object $eventSourcedAggregateRoot
     */
    public function extractAggregateVersion($eventSourcedAggregateRoot): int;

    /**
     * @param object $eventSourcedAggregateRoot
     */
    public function extractAggregateId($eventSourcedAggregateRoot): string;

    /**
     * @return object reconstructed EventSourcedAggregateRoot
     */
    public function reconstituteAggregateFromHistory(AggregateType $aggregateType, \Iterator $historyEvents);

    /**
     * @param object $eventSourcedAggregateRoot
     *
     * @return Message[]
     */
    public function extractPendingStreamEvents($eventSourcedAggregateRoot): array;

    /**
     * @param object $eventSourcedAggregateRoot
     * @param \Iterator $events
     */
    public function replayStreamEvents($eventSourcedAggregateRoot, \Iterator $events): void;
}
