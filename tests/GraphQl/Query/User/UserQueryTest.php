<?php

declare(strict_types=1);

namespace App\Tests\GraphQl\Query\User;

use App\Entity\User;
use App\GraphQl\Query\User\UserQuery;
use App\Model\User\UserId;
use App\Projection\User\UserFinder;
use App\Tests\BaseTestCase;

class UserQueryTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId();
        $user = \Mockery::mock(User::class);

        $userFinder = \Mockery::mock(UserFinder::class);
        $userFinder->shouldReceive('find')
            ->once()
            ->with(\Mockery::type(UserId::class))
            ->andReturn($user);

        $result = (new UserQuery($userFinder))($userId);

        $this->assertEquals($user, $result);
    }

    public function testNotFound(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId();

        $userFinder = \Mockery::mock(UserFinder::class);
        $userFinder->shouldReceive('find')
            ->once()
            ->with(\Mockery::type(UserId::class))
            ->andReturnNull();

        $result = (new UserQuery($userFinder))($userId);

        $this->assertNull($result);
    }
}
