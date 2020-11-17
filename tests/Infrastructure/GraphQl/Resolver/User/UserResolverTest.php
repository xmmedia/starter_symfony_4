<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\GraphQl\Resolver\User;

use App\Entity\User;
use App\Infrastructure\GraphQl\Resolver\User\UserResolver;
use App\Model\User\UserId;
use App\Projection\User\UserFinder;
use App\Tests\BaseTestCase;
use Mockery;

class UserResolverTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $userId = $faker->uuid;
        $user = Mockery::mock(User::class);

        $userFinder = Mockery::mock(UserFinder::class);
        $userFinder->shouldReceive('find')
            ->once()
            ->with(Mockery::type(UserId::class))
            ->andReturn($user);

        $result = (new UserResolver($userFinder))($userId);

        $this->assertEquals($user, $result);
    }

    public function testNotFound(): void
    {
        $faker = $this->faker();

        $userId = $faker->uuid;

        $userFinder = Mockery::mock(UserFinder::class);
        $userFinder->shouldReceive('find')
            ->once()
            ->with(Mockery::type(UserId::class))
            ->andReturnNull();

        $result = (new UserResolver($userFinder))($userId);

        $this->assertNull($result);
    }
}
