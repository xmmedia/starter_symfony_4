<?php

declare(strict_types=1);

namespace App\EventStore;

use Prooph\Common\Event\ActionEvent;
use Prooph\EventStore\ActionEventEmitterEventStore;
use Prooph\EventStore\Plugin\AbstractPlugin;
use Symfony\Component\Messenger\MessageBusInterface;

final class EventStoreMessengerPlugin extends AbstractPlugin
{
    /** @var MessageBusInterface */
    private $eventBus;

    public function __construct(MessageBusInterface $eventBus)
    {
        $this->eventBus = $eventBus;
    }

    public function attachToEventStore(ActionEventEmitterEventStore $eventStore): void
    {
        $this->listenerHandlers[] = $eventStore->attach(
            ActionEventEmitterEventStore::EVENT_APPEND_TO,
            function (ActionEvent $event): void {
                $recordedEvents = $event->getParam('streamEvents', new \ArrayIterator());

                if ($event->getParam('streamNotFound', false)
                    || $event->getParam('concurrencyException', false)
                ) {
                    return;
                }

                foreach ($recordedEvents as $recordedEvent) {
                    $this->eventBus->dispatch($recordedEvent);
                }
            }
        );

        $this->listenerHandlers[] = $eventStore->attach(
            ActionEventEmitterEventStore::EVENT_CREATE,
            function (ActionEvent $event): void {
                $stream = $event->getParam('stream');
                $recordedEvents = $stream->streamEvents();

                if ($event->getParam('streamExistsAlready', false)) {
                    return;
                }

                foreach ($recordedEvents as $recordedEvent) {
                    $this->eventBus->dispatch($recordedEvent);
                }
            }
        );
    }
}
