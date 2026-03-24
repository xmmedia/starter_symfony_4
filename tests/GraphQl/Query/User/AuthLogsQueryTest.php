<?php

declare(strict_types=1);

namespace App\Tests\GraphQl\Query\User;

use App\Entity\AuthLog;
use App\Entity\User;
use App\GraphQl\Query\User\AuthLogsQuery;
use App\Projection\AuthLog\AuthLogFinder;
use App\Tests\BaseTestCase;

class AuthLogsQueryTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();
        $userId = $faker->userId();
        $authLog = \Mockery::mock(AuthLog::class);

        $user = \Mockery::mock(User::class);
        $user->shouldReceive('userId')
            ->once()
            ->andReturn($userId);

        $authLogFinder = \Mockery::mock(AuthLogFinder::class);
        $authLogFinder->shouldReceive('findByUserId')
            ->once()
            ->with($userId, 30, 0)
            ->andReturn([$authLog]);

        $result = (new AuthLogsQuery($authLogFinder))($user);

        $this->assertEquals([$authLog], $result);
    }

    public function testWithOffsetAndLimit(): void
    {
        $faker = $this->faker();
        $userId = $faker->userId();
        $offset = $faker->numberBetween(1, 100);
        $limit = $faker->numberBetween(1, 50);

        $user = \Mockery::mock(User::class);
        $user->shouldReceive('userId')
            ->once()
            ->andReturn($userId);

        $authLogFinder = \Mockery::mock(AuthLogFinder::class);
        $authLogFinder->shouldReceive('findByUserId')
            ->once()
            ->with($userId, $limit, $offset)
            ->andReturn([]);

        $result = (new AuthLogsQuery($authLogFinder))($user, $offset, $limit);

        $this->assertEquals([], $result);
    }
}
