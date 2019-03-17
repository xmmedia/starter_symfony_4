<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Handler;

use App\Model\User\Command\UserLoggedIn;
use App\Model\User\Exception\UserNotFound;
use App\Model\User\Handler\UserLoggedInHandler;
use App\Model\User\User;
use App\Model\User\UserId;
use App\Model\User\UserList;
use App\Tests\BaseTestCase;
use Mockery;

class UserLoggedInHandlerTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $user = Mockery::mock(User::class);
        $user->shouldReceive('loggedIn')
            ->once();

        $command = UserLoggedIn::now($faker->userId);

        $repo = Mockery::mock(UserList::class);
        $repo->shouldReceive('get')
            ->with(Mockery::type(UserId::class))
            ->andReturn($user);
        $repo->shouldReceive('save')
            ->once()
            ->with(Mockery::type(User::class));

        (new UserLoggedInHandler($repo))($command);
    }

    public function testUserNotFound(): void
    {
        $faker = $this->faker();

        $command = UserLoggedIn::now($faker->userId);

        $repo = Mockery::mock(UserList::class);
        $repo->shouldReceive('get')
            ->with(Mockery::type(UserId::class))
            ->andReturnNull();

        $this->expectException(UserNotFound::class);

        (new UserLoggedInHandler($repo))($command);
    }
}
