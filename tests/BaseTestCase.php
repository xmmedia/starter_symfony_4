<?php

declare(strict_types=1);

namespace App\Tests;

use App\EventSourcing\Aggregate\AggregateRoot;
use App\EventSourcing\Aggregate\AggregateTranslator;
use App\EventSourcing\Aggregate\AggregateType;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

class BaseTestCase extends \PHPUnit\Framework\TestCase
{
    use MockeryPHPUnitIntegration;
    use UsesFaker;

    /** @var AggregateTranslator */
    private $aggregateTranslator;

    protected function assertRecordedEvent(
        string $eventName,
        array $payload,
        array $events,
        $assertNotRecorded = false
    ): void {
        $isRecorded = false;

        foreach ($events as $event) {
            if (null === $event) {
                continue;
            }

            if ($event instanceof $eventName) {
                $isRecorded = true;

                if (!$assertNotRecorded) {
                    $this->assertEquals(
                        $payload,
                        $event->payload(),
                        sprintf(
                            'Payload of recorded event %s does not match with expected payload.',
                            $eventName
                        )
                    );
                }
            }
        }

        if ($assertNotRecorded) {
            $this->assertFalse(
                $isRecorded,
                sprintf('Event %s was recorded.', $eventName)
            );
        } else {
            $this->assertTrue(
                $isRecorded,
                sprintf('Event %s was not recorded.', $eventName)
            );
        }
    }

    protected function assertNotRecordedEvent(string $eventName, array $events): void
    {
        $this->assertRecordedEvent($eventName, [], $events, true);
    }

    protected function assertUuid(string $uuid): void
    {
        try {
            \Webmozart\Assert\Assert::uuid($uuid);
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(false, sprintf('The "%s" is not a UUID.', $uuid));
        }
    }

    protected function popRecordedEvent(AggregateRoot $aggregateRoot): array
    {
        return $this->getAggregateTranslator()
            ->extractPendingStreamEvents($aggregateRoot);
    }

    /**
     * @return object
     */
    protected function reconstituteAggregateFromHistory(
        string $aggregateRootClass,
        array $events
    ) {
        return $this->getAggregateTranslator()->reconstituteAggregateFromHistory(
            AggregateType::fromAggregateRootClass($aggregateRootClass),
            new \ArrayIterator($events)
        );
    }

    private function getAggregateTranslator(): AggregateTranslator
    {
        if (null === $this->aggregateTranslator) {
            $this->aggregateTranslator = new AggregateTranslator();
        }

        return $this->aggregateTranslator;
    }
}
