<?php

declare(strict_types=1);

namespace App\Tests\GraphQl\Query\AuthLog;

use App\GraphQl\Query\AuthLog\AuthLogsCountQuery;
use App\Projection\AuthLog\AuthLogFinder;
use App\Tests\BaseTestCase;

class AuthLogsCountQueryTest extends BaseTestCase
{
    public function test(): void
    {
        $count = $this->faker()->randomNumber();

        $authLogFinder = \Mockery::mock(AuthLogFinder::class);
        $authLogFinder->shouldReceive('countByFilters')
            ->once()
            ->andReturn($count);

        $result = (new AuthLogsCountQuery($authLogFinder))([]);

        $this->assertSame($count, $result);
    }
}
