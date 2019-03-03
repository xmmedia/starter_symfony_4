<?php

declare(strict_types=1);

namespace App\Tests\EventStore\PersistenceStrategy;

use App\EventSourcing\AggregateChanged;
use App\EventStore\PersistenceStrategy\StreamStrategy;
use App\Tests\BaseTestCase;
use Prooph\EventStore\Pdo\DefaultMessageConverter;
use Prooph\EventStore\StreamName;

class StreamStrategyTest extends BaseTestCase
{
    public function testPrepareData(): void
    {
        $faker = $this->faker();

        $uuid = $faker->uuid;

        $strategy = new StreamStrategy(new DefaultMessageConverter());

        $events = [
            AggregateChanged::occur($uuid, []),
        ];

        $result = $strategy->prepareData(new \ArrayIterator($events));

        $this->assertCount(count($strategy->columnNames()), $result);

        $this->assertUuid($result[0]);
        $this->assertEquals(AggregateChanged::class, $result[1]);
        $this->assertEquals('[]', $result[2]);
        $this->assertEquals(\json_encode(['_aggregate_id' => $uuid, '_aggregate_version' => 1]), $result[3]);
        new \DateTimeImmutable($result[4]);
    }

    public function testGenerateTableName(): void
    {
        $strategy = new StreamStrategy(new DefaultMessageConverter());

        $streamName = new StreamName('test');

        $this->assertEquals(
            'test_event_stream',
            $strategy->generateTableName($streamName)
        );
    }
}
