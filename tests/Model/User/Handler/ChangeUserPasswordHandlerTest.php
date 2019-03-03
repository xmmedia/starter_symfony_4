<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Handler;

use App\Model\User\Command\ChangePassword;
use App\Model\User\Exception\UserNotFound;
use App\Model\User\Handler\ChangeUserPasswordHandler;
use App\Model\User\User;
use App\Model\User\UserId;
use App\Model\User\UserList;
use App\Tests\BaseTestCase;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

class ChangeUserPasswordHandlerTest extends BaseTestCase
{
    use MockeryPHPUnitIntegration;

    public function test(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $password = $faker->password;

        $user = Mockery::mock(User::class);
        $user->shouldReceive('changePassword')
            ->once();

        $command = ChangePassword::forUser($userId, $password);

        $repo = Mockery::mock(UserList::class);
        $repo->shouldReceive('get')
            ->with(Mockery::type(UserId::class))
            ->andReturn($user);
        $repo->shouldReceive('save')
            ->once()
            ->with(Mockery::type(User::class));

        (new ChangeUserPasswordHandler($repo))($command);
    }

    public function testNonUnique(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $password = $faker->password;

        $command = ChangePassword::forUser($userId, $password);

        $repo = Mockery::mock(UserList::class);
        $repo->shouldReceive('get')
            ->with(Mockery::type(UserId::class))
            ->andReturnNull();

        $this->expectException(UserNotFound::class);

        (new ChangeUserPasswordHandler($repo))($command);
    }
}
