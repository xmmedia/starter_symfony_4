<?php

declare(strict_types=1);

namespace App\Tests\GraphQl\Query\MessengerQueue;

use App\GraphQl\Query\MessengerQueue\MessengerQueueMessagesQuery;
use App\Projection\MessengerQueue\MessengerQueueMessageFinder;
use App\Tests\BaseTestCase;

class MessengerQueueMessagesQueryTest extends BaseTestCase
{
    public function test(): void
    {
        $row = ['id' => 1, 'queueName' => 'async', 'messageClass' => 'App\Message\Foo'];

        $finder = \Mockery::mock(MessengerQueueMessageFinder::class);
        $finder->shouldReceive('findByFilters')
            ->once()
            ->andReturn([$row]);

        $result = (new MessengerQueueMessagesQuery($finder))([]);

        $this->assertEquals([$row], $result);
    }

    public function testNoneFound(): void
    {
        $finder = \Mockery::mock(MessengerQueueMessageFinder::class);
        $finder->shouldReceive('findByFilters')
            ->once()
            ->andReturn([]);

        $result = (new MessengerQueueMessagesQuery($finder))([]);

        $this->assertEquals([], $result);
    }
}
