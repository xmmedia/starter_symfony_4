<?php

declare(strict_types=1);

namespace App\Tests\GraphQl\Query\MessengerQueue;

use App\GraphQl\Query\MessengerQueue\MessengerQueueMessageCountQuery;
use App\Projection\MessengerQueue\MessengerQueueMessageFinder;
use App\Tests\BaseTestCase;

class MessengerQueueMessageCountQueryTest extends BaseTestCase
{
    public function test(): void
    {
        $count = $this->faker()->randomNumber();

        $finder = \Mockery::mock(MessengerQueueMessageFinder::class);
        $finder->shouldReceive('countByFilters')
            ->once()
            ->andReturn($count);

        $result = (new MessengerQueueMessageCountQuery($finder))([]);

        $this->assertSame($count, $result);
    }
}
