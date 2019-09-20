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
    public function testAll(): void
    {
        $user = Mockery::mock(User::class);

        $userFinder = Mockery::mock(UserFinder::class);
        $userFinder->shouldReceive('findBy')
            ->once()
            ->andReturn([$user]);

        $resolver = new UsersResolver($userFinder);

        $result = $resolver();

        $this->assertEquals([$user], $result);
    }

    public function testAllNoneFound(): void
    {
        $userFinder = Mockery::mock(UserFinder::class);
        $userFinder->shouldReceive('findBy')
            ->once()
            ->andReturn([]);

        $resolver = new UsersResolver($userFinder);

        $this->assertEquals([], $resolver());
    }
}
