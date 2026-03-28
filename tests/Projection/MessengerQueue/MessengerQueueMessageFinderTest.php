<?php

declare(strict_types=1);

namespace App\Tests\Projection\MessengerQueue;

use App\Projection\MessengerQueue\MessengerQueueMessageFilters;
use App\Projection\MessengerQueue\MessengerQueueMessageFinder;
use App\Tests\BaseTestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

class MessengerQueueMessageFinderTest extends BaseTestCase
{
    public function testFindByFilters(): void
    {
        $messageClass = \stdClass::class;
        $body = 'some-serialized-body';

        $rawRow = [
            'id'           => '1',
            'body'         => $body,
            'queue_name'   => 'async',
            'created_at'   => '2024-03-15 10:00:00',
            'available_at' => '2024-03-15 10:00:00',
            'delivered_at' => null,
        ];

        $result = \Mockery::mock(Result::class);
        $result->shouldReceive('fetchAllAssociative')
            ->once()
            ->andReturn([$rawRow]);

        $connection = \Mockery::mock(Connection::class);
        $connection->shouldReceive('executeQuery')
            ->once()
            ->andReturn($result);

        $envelope = new Envelope(new \stdClass());
        $serializer = \Mockery::mock(SerializerInterface::class);
        $serializer->shouldReceive('decode')
            ->once()
            ->with(['body' => $body])
            ->andReturn($envelope);

        $rows = new MessengerQueueMessageFinder($connection, $serializer)
            ->findByFilters(MessengerQueueMessageFilters::fromArray([]));

        $this->assertCount(1, $rows);
        $this->assertSame(1, $rows[0]['id']);
        $this->assertSame('async', $rows[0]['queueName']);
        $this->assertSame($messageClass, $rows[0]['messageClass']);
        $this->assertArrayNotHasKey('body', $rows[0]);
    }

    public function testFindByFiltersDecodeFails(): void
    {
        $body = 'invalid-body';

        $rawRow = [
            'id'           => '1',
            'body'         => $body,
            'queue_name'   => 'async',
            'created_at'   => '2024-03-15 10:00:00',
            'available_at' => '2024-03-15 10:00:00',
            'delivered_at' => null,
        ];

        $result = \Mockery::mock(Result::class);
        $result->shouldReceive('fetchAllAssociative')
            ->once()
            ->andReturn([$rawRow]);

        $connection = \Mockery::mock(Connection::class);
        $connection->shouldReceive('executeQuery')
            ->once()
            ->andReturn($result);

        $serializer = \Mockery::mock(SerializerInterface::class);
        $serializer->shouldReceive('decode')
            ->once()
            ->andThrow(new \RuntimeException('Could not decode'));

        $rows = new MessengerQueueMessageFinder($connection, $serializer)
            ->findByFilters(MessengerQueueMessageFilters::fromArray([]));

        $this->assertNull($rows[0]['messageClass']);
    }

    public function testCountByFilters(): void
    {
        $result = \Mockery::mock(Result::class);
        $result->shouldReceive('fetchNumeric')
            ->once()
            ->andReturn([5]);

        $connection = \Mockery::mock(Connection::class);
        $connection->shouldReceive('executeQuery')
            ->once()
            ->andReturn($result);

        $serializer = \Mockery::mock(SerializerInterface::class);

        $count = new MessengerQueueMessageFinder($connection, $serializer)
            ->countByFilters(MessengerQueueMessageFilters::fromArray([]));

        $this->assertSame(5, $count);
    }

    public function testFind(): void
    {
        $messageClass = \stdClass::class;
        $body = 'some-serialized-body';

        $rawRow = [
            'id'           => '7',
            'body'         => $body,
            'queue_name'   => 'failed',
            'created_at'   => '2024-03-15 10:00:00',
            'available_at' => '2024-03-15 10:00:00',
            'delivered_at' => '2024-03-15 10:01:00',
        ];

        $result = \Mockery::mock(Result::class);
        $result->shouldReceive('fetchAssociative')
            ->once()
            ->andReturn($rawRow);

        $connection = \Mockery::mock(Connection::class);
        $connection->shouldReceive('executeQuery')
            ->once()
            ->andReturn($result);

        $envelope = new Envelope(new \stdClass());
        $serializer = \Mockery::mock(SerializerInterface::class);
        $serializer->shouldReceive('decode')
            ->once()
            ->with(['body' => $body])
            ->andReturn($envelope);

        $row = new MessengerQueueMessageFinder($connection, $serializer)->find(7);

        $this->assertNotNull($row);
        $this->assertSame(7, $row['id']);
        $this->assertSame('failed', $row['queueName']);
        $this->assertSame($messageClass, $row['messageClass']);
        $this->assertSame('2024-03-15 10:01:00', $row['deliveredAt']);
    }

    public function testFindNotFound(): void
    {
        $result = \Mockery::mock(Result::class);
        $result->shouldReceive('fetchAssociative')
            ->once()
            ->andReturn(false);

        $connection = \Mockery::mock(Connection::class);
        $connection->shouldReceive('executeQuery')
            ->once()
            ->andReturn($result);

        $serializer = \Mockery::mock(SerializerInterface::class);

        $row = new MessengerQueueMessageFinder($connection, $serializer)->find(99);

        $this->assertNull($row);
    }
}
