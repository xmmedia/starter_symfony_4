<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\GraphQl\Query\User;

use App\Entity\User;
use App\Infrastructure\GraphQl\Query\User\UserEmailUniqueQuery;
use App\Projection\User\UserFinder;
use App\Security\Security;
use App\Tests\BaseTestCase;
use Mockery;
use Xm\SymfonyBundle\Model\Email;

class UserEmailUniqueQueryTest extends BaseTestCase
{
    public function testSameUser(): void
    {
        $faker = $this->faker();
        $userId = $faker->userId();

        $currentUser = Mockery::mock(User::class);
        $currentUser->shouldReceive('userId')
            ->once()
            ->andReturn($userId);

        $otherUser = Mockery::mock(User::class);
        $otherUser->shouldReceive('userId')
            ->once()
            ->andReturn($userId);

        $userFinder = Mockery::mock(UserFinder::class);
        $userFinder->shouldReceive('findOneByEmail')
            ->once()
            ->with(Mockery::type(Email::class))
            ->andReturn($otherUser);

        $security = Mockery::mock(Security::class);
        $security->shouldReceive('getUser')
            ->once()
            ->andReturn($currentUser);

        $result = (new UserEmailUniqueQuery($userFinder, $security))(
            $faker->email()
        );

        $this->assertEquals(['unique' => true], $result);
    }

    public function testDifferentUser(): void
    {
        $faker = $this->faker();

        $currentUser = Mockery::mock(User::class);
        $currentUser->shouldReceive('userId')
            ->once()
            ->andReturn($faker->unique()->userId());

        $otherUser = Mockery::mock(User::class);
        $otherUser->shouldReceive('userId')
            ->once()
            ->andReturn($faker->unique()->userId());

        $userFinder = Mockery::mock(UserFinder::class);
        $userFinder->shouldReceive('findOneByEmail')
            ->once()
            ->with(Mockery::type(Email::class))
            ->andReturn($otherUser);

        $security = Mockery::mock(Security::class);
        $security->shouldReceive('getUser')
            ->once()
            ->andReturn($currentUser);

        $result = (new UserEmailUniqueQuery($userFinder, $security))(
            $faker->email()
        );

        $this->assertEquals(['unique' => false], $result);
    }

    public function testNoOtherUser(): void
    {
        $faker = $this->faker();

        $currentUser = Mockery::mock(User::class);

        $userFinder = Mockery::mock(UserFinder::class);
        $userFinder->shouldReceive('findOneByEmail')
            ->once()
            ->with(Mockery::type(Email::class))
            ->andReturnNull();

        $security = Mockery::mock(Security::class);
        $security->shouldReceive('getUser')
            ->once()
            ->andReturn($currentUser);

        $result = (new UserEmailUniqueQuery($userFinder, $security))(
            $faker->email()
        );

        $this->assertEquals(['unique' => true], $result);
    }

    public function testNotLoggedIn(): void
    {
        $faker = $this->faker();

        $userFinder = Mockery::mock(UserFinder::class);

        $security = Mockery::mock(Security::class);
        $security->shouldReceive('getUser')
            ->once()
            ->andReturnNull();

        $this->expectException(\RuntimeException::class);

        (new UserEmailUniqueQuery($userFinder, $security))($faker->email());
    }
}
