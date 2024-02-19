<?php

declare(strict_types=1);

namespace App\Tests\GraphQl\Query\User;

use App\Entity\User;
use App\GraphQl\Query\User\UsersQuery;
use App\Projection\User\UserFinder;
use App\Tests\BaseTestCase;

class UsersQueryTest extends BaseTestCase
{
    public function test(): void
    {
        $user = \Mockery::mock(User::class);

        $userFinder = \Mockery::mock(UserFinder::class);
        $userFinder->shouldReceive('findByFilters')
            ->once()
            ->andReturn([$user]);

        $result = (new UsersQuery($userFinder))([]);

        $this->assertEquals([$user], $result);
    }

    public function testNoneFound(): void
    {
        $userFinder = \Mockery::mock(UserFinder::class);
        $userFinder->shouldReceive('findByFilters')
            ->once()
            ->andReturn([]);

        $result = (new UsersQuery($userFinder))([]);

        $this->assertEquals([], $result);
    }
}
