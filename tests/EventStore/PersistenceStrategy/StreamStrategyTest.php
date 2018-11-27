<?php

declare(strict_types=1);

namespace App\Tests\EventStore\PersistenceStrategy;

use App\EventSourcing\AggregateChanged;
use App\EventStore\PersistenceStrategy\StreamStrategy;
use Faker;
use PHPUnit\Framework\TestCase;
use Prooph\EventStore\Pdo\DefaultMessageConverter;
use Prooph\EventStore\StreamName;
use Webmozart\Assert\Assert;

class StreamStrategyTest extends TestCase
{
    public function testPrepareData(): void
    {
        $faker = Faker\Factory::create();

        $uuid = $faker->uuid;

        $strategy = new StreamStrategy(new DefaultMessageConverter());

        $events = [
            AggregateChanged::occur($uuid, []),
        ];

        $result = $strategy->prepareData(new \ArrayIterator($events));

        $this->assertCount(count($strategy->columnNames()), $result);

        Assert::uuid($result[0]);
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
