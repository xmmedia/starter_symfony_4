<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Handler;

use App\Model\User\Command\ActivateUserByAdmin;
use App\Model\User\Exception\UserNotFound;
use App\Model\User\Handler\ActivateUserByAdminHandler;
use App\Model\User\User;
use App\Model\User\UserId;
use App\Model\User\UserList;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class ActivateUserByAdminHandlerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test(): void
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('activateByAdmin')
            ->once();

        $command = ActivateUserByAdmin::user(UserId::generate());

        $repo = Mockery::mock(UserList::class);
        $repo->shouldReceive('get')
            ->with(Mockery::type(UserId::class))
            ->andReturn($user);
        $repo->shouldReceive('save')
            ->once()
            ->with(Mockery::type(User::class));

        (new ActivateUserByAdminHandler($repo))($command);
    }

    public function testUserNotFound(): void
    {
        $command = ActivateUserByAdmin::user(UserId::generate());

        $repo = Mockery::mock(UserList::class);
        $repo->shouldReceive('get')
            ->with(Mockery::type(UserId::class))
            ->andReturnNull();

        $this->expectException(UserNotFound::class);

        (new ActivateUserByAdminHandler($repo))($command);
    }
}
