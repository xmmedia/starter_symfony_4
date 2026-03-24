<?php

declare(strict_types=1);

namespace App\Tests\GraphQl\Query\AuthLog;

use App\Entity\AuthLog;
use App\GraphQl\Query\AuthLog\AuthLogQuery;
use App\Model\AuthLog\AuthLogId;
use App\Projection\AuthLog\AuthLogFinder;
use App\Tests\BaseTestCase;

class AuthLogQueryTest extends BaseTestCase
{
    public function test(): void
    {
        $authLogId = AuthLogId::fromString($this->faker()->uuid());
        $authLog = \Mockery::mock(AuthLog::class);

        $authLogFinder = \Mockery::mock(AuthLogFinder::class);
        $authLogFinder->shouldReceive('find')
            ->once()
            ->with(\Mockery::type(AuthLogId::class))
            ->andReturn($authLog);

        $result = (new AuthLogQuery($authLogFinder))($authLogId);

        $this->assertEquals($authLog, $result);
    }

    public function testNotFound(): void
    {
        $authLogId = AuthLogId::fromString($this->faker()->uuid());

        $authLogFinder = \Mockery::mock(AuthLogFinder::class);
        $authLogFinder->shouldReceive('find')
            ->once()
            ->with(\Mockery::type(AuthLogId::class))
            ->andReturnNull();

        $result = (new AuthLogQuery($authLogFinder))($authLogId);

        $this->assertNull($result);
    }
}
