<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\GraphQl\Resolver\User;

use App\Entity\User;
use App\Infrastructure\GraphQl\Resolver\User\UsersResolver;
use App\Projection\User\UserFinder;
use App\Tests\BaseTestCase;
use Mockery;

class UsersResolverTest extends BaseTestCase
{
    public function test(): void
    {
        $user = Mockery::mock(User::class);

        $userFinder = Mockery::mock(UserFinder::class);
        $userFinder->shouldReceive('findByUserFilters')
            ->once()
            ->andReturn([$user]);

        $result = (new UsersResolver($userFinder))([]);

        $this->assertEquals([$user], $result);
    }

    public function testNoneFound(): void
    {
        $userFinder = Mockery::mock(UserFinder::class);
        $userFinder->shouldReceive('findByUserFilters')
            ->once()
            ->andReturn([]);

        $result = (new UsersResolver($userFinder))([]);

        $this->assertEquals([], $result);
    }
}
