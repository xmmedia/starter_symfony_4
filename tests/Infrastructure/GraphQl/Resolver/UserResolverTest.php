<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\GraphQl\Resolver;

use App\Entity\User;
use App\Infrastructure\GraphQl\Resolver\UserResolver;
use App\Model\User\UserId;
use App\Repository\UserRepository;
use App\Tests\BaseTestCase;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

class UserResolverTest extends BaseTestCase
{
    use MockeryPHPUnitIntegration;

    public function testAll(): void
    {
        $user = Mockery::mock(User::class);

        $userRepo = Mockery::mock(UserRepository::class);
        $userRepo->shouldReceive('findBy')
            ->once()
            ->andReturn([$user]);

        $resolver = new UserResolver($userRepo);

        $result = $resolver->all();

        $this->assertEquals([$user], $result);
    }

    public function testAllNoneFound(): void
    {
        $userRepo = Mockery::mock(UserRepository::class);
        $userRepo->shouldReceive('findBy')
            ->once()
            ->andReturn([]);

        $resolver = new UserResolver($userRepo);

        $this->assertEquals([], $resolver->all());
    }

    public function testUserByUserId(): void
    {
        $faker = $this->faker();

        $userId = $faker->uuid;
        $user = Mockery::mock(User::class);

        $userRepo = Mockery::mock(UserRepository::class);
        $userRepo->shouldReceive('find')
            ->once()
            ->with(Mockery::type(UserId::class))
            ->andReturn($user);

        $resolver = new UserResolver($userRepo);

        $result = $resolver->userByUserId($userId);

        $this->assertEquals($user, $result);
    }

    public function testUserByUserIdNotFound(): void
    {
        $faker = $this->faker();

        $userId = $faker->uuid;

        $userRepo = Mockery::mock(UserRepository::class);
        $userRepo->shouldReceive('find')
            ->once()
            ->with(Mockery::type(UserId::class))
            ->andReturnNull();

        $resolver = new UserResolver($userRepo);

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
    }
}
