<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Handler;

use App\Model\User\Command\AdminDeleteUser;
use App\Model\User\Exception\UserNotFound;
use App\Model\User\Handler\AdminDeleteUserHandler;
use App\Model\User\User;
use App\Model\User\UserId;
use App\Model\User\UserList;
use App\Tests\BaseTestCase;

class AdminDeleteUserHandlerTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $user = \Mockery::mock(User::class);
        $user->shouldReceive('delete')
            ->once();

        $command = AdminDeleteUser::now($faker->userId());

        $repo = \Mockery::mock(UserList::class);
        $repo->shouldReceive('get')
            ->with(\Mockery::type(UserId::class))
            ->andReturn($user);
        $repo->shouldReceive('save')
            ->once()
            ->with(\Mockery::type(User::class));

        (new AdminDeleteUserHandler($repo))($command);
    }

    public function testUserNotFound(): void
    {
        $faker = $this->faker();

        $command = AdminDeleteUser::now($faker->userId());

        $repo = \Mockery::mock(UserList::class);
        $repo->shouldReceive('get')
            ->with(\Mockery::type(UserId::class))
            ->andReturnNull();

        $this->expectException(UserNotFound::class);

        (new AdminDeleteUserHandler($repo))($command);
    }
}
