<?php

declare(strict_types=1);

namespace App\EventStore;

use App\DataProvider\IssuerProvider;
use Prooph\Common\Event\ActionEvent;
use Prooph\EventStore\ActionEventEmitterEventStore;
use Prooph\EventStore\Plugin\Plugin;
use Prooph\EventStore\Stream;

class EventMetadataIssuedByEnricher implements Plugin
{
    /** @var IssuerProvider */
    private $issuerProvider;

    /** @var array */
    private $eventStoreListeners = [];

    public function __construct(IssuerProvider $issuerProvider)
    {
        $this->issuerProvider = $issuerProvider;
    }

    public function attachToEventStore(ActionEventEmitterEventStore $eventStore): void
    {
        $this->eventStoreListeners[] = $eventStore->attach(
            ActionEventEmitterEventStore::EVENT_CREATE,
            function (ActionEvent $createEvent)
            {
                $stream = $createEvent->getParam('stream');

                if (!$stream instanceof Stream) {
                    return;
                }

                $streamEvents = $stream->streamEvents();
                $streamEvents = $this->handleRecordedEvents($streamEvents);

                $createEvent->setParam('stream', new Stream($stream->streamName(), $streamEvents));
            },
            1000
        );
        $this->eventStoreListeners[] = $eventStore->attach(
            ActionEventEmitterEventStore::EVENT_APPEND_TO,
            function (ActionEvent $appendToStreamEvent)
            {
                $streamEvents = $appendToStreamEvent->getParam('streamEvents');
                $streamEvents = $this->handleRecordedEvents($streamEvents);

                $appendToStreamEvent->setParam('streamEvents', $streamEvents);
            },
            1000
        );
    }

    public function detachFromEventStore(ActionEventEmitterEventStore $eventStore): void
    {
        foreach ($this->eventStoreListeners as $listenerHandler) {
            $eventStore->detach($listenerHandler);
        }

        $this->eventStoreListeners = [];
    }

    /**
     * This method takes domain events as argument which are going to be added
     * to the event stream and adds the issuedBy as metadata to each event.
     */
    private function handleRecordedEvents(\Iterator $recordedEvents)
    {
        $issuer = $this->issuerProvider->getIssuer();

        $enrichedRecordedEvents = [];

        foreach ($recordedEvents as $recordedEvent) {
            $enrichedRecordedEvents[] = $recordedEvent->withAddedMetadata(
                'issuedBy',
                $issuer
            );
        }

        return new \ArrayIterator($enrichedRecordedEvents);
    }
}
