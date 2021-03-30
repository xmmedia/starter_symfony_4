<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Handler;

use App\Model\User\Command\DeactivateUserByAdmin;
use App\Model\User\Exception\UserNotFound;
use App\Model\User\Handler\DeactivateUserByAdminHandler;
use App\Model\User\User;
use App\Model\User\UserId;
use App\Model\User\UserList;
use App\Tests\BaseTestCase;
use Mockery;

class DeactivateUserByAdminHandlerTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $user = Mockery::mock(User::class);
        $user->shouldReceive('deactivateByAdmin')
            ->once();

        $command = DeactivateUserByAdmin::user($faker->userId());

        $repo = Mockery::mock(UserList::class);
        $repo->shouldReceive('get')
            ->with(Mockery::type(UserId::class))
            ->andReturn($user);
        $repo->shouldReceive('save')
            ->once()
            ->with(Mockery::type(User::class));

        (new DeactivateUserByAdminHandler($repo))($command);
    }

    public function testUserNotFound(): void
    {
        $faker = $this->faker();

        $command = DeactivateUserByAdmin::user($faker->userId());

        $repo = Mockery::mock(UserList::class);
        $repo->shouldReceive('get')
            ->with(Mockery::type(UserId::class))
            ->andReturnNull();

        $this->expectException(UserNotFound::class);

        (new DeactivateUserByAdminHandler($repo))($command);
    }
}
