<?php

declare(strict_types=1);

namespace App\Tests;

use App\EventSourcing\Aggregate\AggregateRoot;
use App\EventSourcing\Aggregate\AggregateTranslator;
use App\EventSourcing\Aggregate\AggregateType;
use App\DataFixtures\Faker\Provider;
use Faker;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

class BaseTestCase extends \PHPUnit\Framework\TestCase
{
    use MockeryPHPUnitIntegration;

    /** @var AggregateTranslator */
    private $aggregateTranslator;

    /** @var Faker\Generator */
    private $faker;

    /**
     * @return Faker\Generator|Provider\AddressFakerProvider|Provider\EmailFakerProvider|Provider\NameFakerProvider|Provider\PhoneNumberFakerProvider|Provider\StringFakerProvider|Provider\UuidFakerProvider
     */
    protected function faker(): Faker\Generator
    {
        return is_null($this->faker) ? $this->makeFaker() : $this->faker;
    }

    private function makeFaker(): Faker\Generator
    {
        $this->faker = Faker\Factory::create();
        $this->faker->addProvider(new Provider\AddressFakerProvider($this->faker));
        $this->faker->addProvider(new Provider\EmailFakerProvider($this->faker));
        $this->faker->addProvider(new Provider\NameFakerProvider($this->faker));
        $this->faker->addProvider(new Provider\PhoneNumberFakerProvider($this->faker));
        $this->faker->addProvider(new Provider\StringFakerProvider($this->faker));
        $this->faker->addProvider(new Provider\UuidFakerProvider($this->faker));

        return $this->faker;
    }

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
