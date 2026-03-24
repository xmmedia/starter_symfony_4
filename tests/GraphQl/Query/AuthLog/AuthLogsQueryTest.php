<?php

declare(strict_types=1);

namespace App\Tests\GraphQl\Query\AuthLog;

use App\Entity\AuthLog;
use App\GraphQl\Query\AuthLog\AuthLogsQuery;
use App\Projection\AuthLog\AuthLogFinder;
use App\Tests\BaseTestCase;

class AuthLogsQueryTest extends BaseTestCase
{
    public function test(): void
    {
        $authLog = \Mockery::mock(AuthLog::class);

        $authLogFinder = \Mockery::mock(AuthLogFinder::class);
        $authLogFinder->shouldReceive('findByFilters')
            ->once()
            ->andReturn([$authLog]);

        $result = (new AuthLogsQuery($authLogFinder))([]);

        $this->assertEquals([$authLog], $result);
    }

    public function testNoneFound(): void
    {
        $authLogFinder = \Mockery::mock(AuthLogFinder::class);
        $authLogFinder->shouldReceive('findByFilters')
            ->once()
            ->andReturn([]);

        $result = (new AuthLogsQuery($authLogFinder))([]);

        $this->assertEquals([], $result);
    }
}
