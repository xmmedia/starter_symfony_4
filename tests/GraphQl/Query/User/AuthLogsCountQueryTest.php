<?php

declare(strict_types=1);

namespace App\Tests\GraphQl\Query\User;

use App\Entity\User;
use App\GraphQl\Query\User\AuthLogsCountQuery;
use App\Projection\AuthLog\AuthLogFinder;
use App\Tests\BaseTestCase;

class AuthLogsCountQueryTest extends BaseTestCase
{
    public function test(): void
    {
        $count = $this->faker()->randomNumber();

        $user = \Mockery::mock(User::class);

        $authLogFinder = \Mockery::mock(AuthLogFinder::class);
        $authLogFinder->shouldReceive('countByUserId')
            ->once()
            ->with($user)
            ->andReturn($count);

        $result = (new AuthLogsCountQuery($authLogFinder))($user);

        $this->assertSame($count, $result);
    }
}
