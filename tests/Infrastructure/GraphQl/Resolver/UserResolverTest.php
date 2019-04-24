<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\GraphQl\Resolver;

use App\Entity\User;
use App\Infrastructure\GraphQl\Resolver\UserResolver;
use App\Model\User\UserId;
use App\Projection\User\UserFinder;
use App\Tests\BaseTestCase;
use Mockery;

class UserResolverTest extends BaseTestCase
{
    public function testAll(): void
    {
        $user = Mockery::mock(User::class);

        $userFinder = Mockery::mock(UserFinder::class);
        $userFinder->shouldReceive('findBy')
            ->once()
            ->andReturn([$user]);

        $resolver = new UserResolver($userFinder);

        $result = $resolver->all();

        $this->assertEquals([$user], $result);
    }

    public function testAllNoneFound(): void
    {
        $userFinder = Mockery::mock(UserFinder::class);
        $userFinder->shouldReceive('findBy')
            ->once()
            ->andReturn([]);

        $resolver = new UserResolver($userFinder);

        $this->assertEquals([], $resolver->all());
    }

    public function testUserByUserId(): void
    {
        $faker = $this->faker();

        $userId = $faker->uuid;
        $user = Mockery::mock(User::class);

        $userFinder = Mockery::mock(UserFinder::class);
        $userFinder->shouldReceive('find')
            ->once()
            ->with(Mockery::type(UserId::class))
            ->andReturn($user);

        $resolver = new UserResolver($userFinder);

        $result = $resolver->userByUserId($userId);

        $this->assertEquals($user, $result);
    }

    public function testUserByUserIdNotFound(): void
    {
        $faker = $this->faker();

        $userId = $faker->uuid;

        $userFinder = Mockery::mock(UserFinder::class);
        $userFinder->shouldReceive('find')
            ->once()
            ->with(Mockery::type(UserId::class))
            ->andReturnNull();

        $resolver = new UserResolver($userFinder);

        $result = $resolver->userByUserId($userId);

        $this->assertNull($result);
    }

    public function testAliases(): void
    {
        $result = UserResolver::getAliases();

        $expected = [
            'all'          => 'app.graphql.resolver.user.all',
            'userByUserId' => 'app.graphql.resolver.user.by.userId',
        ];

        $this->assertEquals($expected, $result);

        $this->assertHasAllResolverMethods(
            new UserResolver(Mockery::mock(UserFinder::class))
        );
    }
}
