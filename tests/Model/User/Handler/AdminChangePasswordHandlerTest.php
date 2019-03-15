<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Handler;

use App\Model\User\Command\AdminChangePassword;
use App\Model\User\Exception\UserNotFound;
use App\Model\User\Handler\AdminChangePasswordHandler;
use App\Model\User\User;
use App\Model\User\UserId;
use App\Model\User\UserList;
use App\Tests\BaseTestCase;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

class AdminChangePasswordHandlerTest extends BaseTestCase
{
    use MockeryPHPUnitIntegration;

    public function test(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $password = $faker->password(12, 250);

        $user = Mockery::mock(User::class);
        $user->shouldReceive('changePasswordByAdmin')
            ->once();

        $command = AdminChangePassword::with($userId, $password);

        $repo = Mockery::mock(UserList::class);
        $repo->shouldReceive('get')
            ->with(Mockery::type(UserId::class))
            ->andReturn($user);
        $repo->shouldReceive('save')
            ->once()
            ->with(Mockery::type(User::class));

        (new AdminChangePasswordHandler($repo))($command);
    }

    public function testNonUnique(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $password = $faker->password(12, 250);

        $command = AdminChangePassword::with($userId, $password);

        $repo = Mockery::mock(UserList::class);
        $repo->shouldReceive('get')
            ->with(Mockery::type(UserId::class))
            ->andReturnNull();

        $this->expectException(UserNotFound::class);

        (new AdminChangePasswordHandler($repo))($command);
    }
}
