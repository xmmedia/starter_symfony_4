<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Handler;

use App\Model\User\Command\UpgradePassword;
use App\Model\User\Exception\UserNotFound;
use App\Model\User\Handler\UpgradePasswordHandler;
use App\Model\User\User;
use App\Model\User\UserId;
use App\Model\User\UserList;
use App\Tests\BaseTestCase;
use Mockery;

class UpgradePasswordHandlerTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId();
        $password = $faker->password();

        $user = Mockery::mock(User::class);
        $user->shouldReceive('upgradePassword')
            ->once();

        $command = UpgradePassword::forUser($userId, $password);

        $repo = Mockery::mock(UserList::class);
        $repo->shouldReceive('get')
            ->with(Mockery::type(UserId::class))
            ->andReturn($user);
        $repo->shouldReceive('save')
            ->once()
            ->with(Mockery::type(User::class));

        (new UpgradePasswordHandler($repo))($command);
    }

    public function testNotFound(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId();
        $password = $faker->password();

        $command = UpgradePassword::forUser($userId, $password);

        $repo = Mockery::mock(UserList::class);
        $repo->shouldReceive('get')
            ->with(Mockery::type(UserId::class))
            ->andReturnNull();

        $this->expectException(UserNotFound::class);

        (new UpgradePasswordHandler($repo))($command);
    }
}
