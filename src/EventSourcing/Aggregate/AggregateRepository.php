<?php

declare(strict_types=1);

namespace App\EventSourcing\Aggregate;

use App\Messaging\Message;
use Prooph\EventStore\EventStore;
use Prooph\EventStore\Exception\StreamNotFound;
use Prooph\EventStore\Metadata\MetadataMatcher;
use Prooph\EventStore\Metadata\Operator;
use Prooph\EventStore\StreamName;

class AggregateRepository
{
    /** @var EventStore */
    protected $eventStore;

    /** @var AggregateTranslator */
    protected $aggregateTranslator;

    /** @var AggregateType */
    protected $aggregateType;

    /** @var array */
    protected $identityMap = [];

    /** @var StreamName */
    protected $streamName;

    public function __construct(
        EventStore $eventStore,
        AggregateType $aggregateType,
        AggregateTranslator $aggregateTranslator,
        StreamName $streamName = null
    ) {
        $this->eventStore = $eventStore;
        $this->aggregateType = $aggregateType;
        $this->aggregateTranslator = $aggregateTranslator;
        $this->streamName = $streamName;
    }

    /**
     * @param object $eventSourcedAggregateRoot
     */
    public function saveAggregateRoot($eventSourcedAggregateRoot): void
    {
        $this->assertAggregateType($eventSourcedAggregateRoot);

        $domainEvents = $this->aggregateTranslator->extractPendingStreamEvents($eventSourcedAggregateRoot);
        $aggregateId = $this->aggregateTranslator->extractAggregateId($eventSourcedAggregateRoot);
        $streamName = $this->determineStreamName($aggregateId);

        $firstEvent = \reset($domainEvents);

        if (false === $firstEvent) {
            return;
        }

        $enrichedEvents = array_map(function ($event) use ($aggregateId) {
            return $this->enrichEventMetadata($event, $aggregateId);
        }, $domainEvents);

        $this->eventStore->appendTo($streamName, new \ArrayIterator($enrichedEvents));

        if (isset($this->identityMap[$aggregateId])) {
            unset($this->identityMap[$aggregateId]);
        }
    }

    /**
     * Returns null if no stream events can be found for aggregate root otherwise the reconstituted aggregate root.
     *
     * @return object|null
     */
    public function getAggregateRoot(string $aggregateId)
    {
        if (isset($this->identityMap[$aggregateId])) {
            return $this->identityMap[$aggregateId];
        }

        $streamName = $this->determineStreamName($aggregateId);

        $metadataMatcher = new MetadataMatcher();
        $metadataMatcher = $metadataMatcher->withMetadataMatch(
            '_aggregate_type',
            Operator::EQUALS(),
            $this->aggregateType->toString()
        );
        $metadataMatcher = $metadataMatcher->withMetadataMatch(
            '_aggregate_id',
            Operator::EQUALS(),
            $aggregateId
        );

        try {
            $streamEvents = $this->eventStore->load($streamName, 1, null, $metadataMatcher);
        } catch (StreamNotFound $e) {
            return null;
        }

        if (!$streamEvents->valid()) {
            return null;
        }

        $eventSourcedAggregateRoot = $this->aggregateTranslator->reconstituteAggregateFromHistory(
            $this->aggregateType,
            $streamEvents
        );

        // Cache aggregate root in the identity map but without pending events
        $this->identityMap[$aggregateId] = $eventSourcedAggregateRoot;

        return $eventSourcedAggregateRoot;
    }

    /**
     * @param object $aggregateRoot
     */
    public function extractAggregateVersion($aggregateRoot): int
    {
        return $this->aggregateTranslator->extractAggregateVersion($aggregateRoot);
    }

    /**
     * Empties the identity map. Use this if you load thousands of aggregates to free memory e.g. modulo 500.
     */
    public function clearIdentityMap(): void
    {
        $this->identityMap = [];
    }

    protected function isFirstEvent(Message $message): bool
    {
        return 1 === $message->metadata()['_aggregate_version'];
    }

    /**
     * Default stream name generation.
     * Override this method in an extending repository to provide a custom name.
     */
    protected function determineStreamName(string $aggregateId): StreamName
    {
        if (null === $this->streamName) {
            return new StreamName('event_stream');
        }

        return $this->streamName;
    }

    /**
     * Add aggregate_id and aggregate_type as metadata to $domainEvent
     * Override this method in an extending repository to add more or different metadata.
     */
    protected function enrichEventMetadata(Message $domainEvent, string $aggregateId): Message
    {
        $domainEvent = $domainEvent->withAddedMetadata('_aggregate_id', $aggregateId);
        $domainEvent = $domainEvent->withAddedMetadata('_aggregate_type', $this->aggregateType->toString());

        return $domainEvent;
    }

    /**
     * @param object $eventSourcedAggregateRoot
     */
    protected function assertAggregateType($eventSourcedAggregateRoot)
    {
        $this->aggregateType->assert($eventSourcedAggregateRoot);
    }
}
