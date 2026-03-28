<?php

declare(strict_types=1);

namespace App\Tests\GraphQl\Query\MessengerQueue;

use App\GraphQl\Query\MessengerQueue\MessengerQueueMessageQuery;
use App\Projection\MessengerQueue\MessengerQueueMessageFinder;
use App\Tests\BaseTestCase;

class MessengerQueueMessageQueryTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $id = $faker->randomNumber();
        $row = ['id' => $id, 'queueName' => 'async', 'messageClass' => 'App\Message\Foo'];

        $finder = \Mockery::mock(MessengerQueueMessageFinder::class);
        $finder->shouldReceive('find')
            ->once()
            ->with($id)
            ->andReturn($row);

        $result = (new MessengerQueueMessageQuery($finder))($id);

        $this->assertEquals($row, $result);
    }

    public function testNotFound(): void
    {
        $faker = $this->faker();

        $id = $faker->randomNumber();

        $finder = \Mockery::mock(MessengerQueueMessageFinder::class);
        $finder->shouldReceive('find')
            ->once()
            ->with($id)
            ->andReturnNull();

        $result = (new MessengerQueueMessageQuery($finder))($id);

        $this->assertNull($result);
    }
}
