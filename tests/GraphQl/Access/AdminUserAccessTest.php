<?php

declare(strict_types=1);

namespace App\Tests\GraphQl\Access;

use App\Entity\User;
use App\GraphQl\Access\AdminUserAccess;
use App\Model\User\UserId;
use App\Projection\User\UserFinder;
use App\Security\Security;
use App\Tests\BaseTestCase;

class AdminUserAccessTest extends BaseTestCase
{
    public function testDeniedForNonAdmin(): void
    {
        $security = \Mockery::mock(Security::class);
        $security->shouldReceive('hasAdminRole')->andReturnFalse();

        $access = new AdminUserAccess($security, \Mockery::mock(UserFinder::class));

        $this->assertFalse($access->allowed($this->faker()->userId()));
    }

    public function testAllowedWhenUserNotFound(): void
    {
        $userId = $this->faker()->userId();

        $security = \Mockery::mock(Security::class);
        $security->shouldReceive('hasAdminRole')->andReturnTrue();

        $access = new AdminUserAccess($security, $this->userFinder($userId, null));

        $this->assertTrue($access->allowed($userId));
    }

    public function testDeniedWhenTargetHasHigherRole(): void
    {
        $userId = $this->faker()->userId();

        $user = \Mockery::mock(User::class);
        $user->shouldReceive('roles')->andReturn(['ROLE_SUPER_ADMIN', 'ROLE_USER']);

        $security = \Mockery::mock(Security::class);
        $security->shouldReceive('hasAdminRole')->andReturnTrue();
        $security->shouldReceive('isGranted')->with('ROLE_SUPER_ADMIN')->andReturnFalse();
        $security->shouldReceive('isGranted')->with('ROLE_USER')->andReturnTrue();

        $access = new AdminUserAccess($security, $this->userFinder($userId, $user));

        $this->assertFalse($access->allowed($userId));
    }

    public function testAllowed(): void
    {
        $userId = $this->faker()->userId();

        $user = \Mockery::mock(User::class);
        $user->shouldReceive('roles')->andReturn(['ROLE_ADMIN', 'ROLE_USER']);

        $security = \Mockery::mock(Security::class);
        $security->shouldReceive('hasAdminRole')->andReturnTrue();
        $security->shouldReceive('isGranted')->with('ROLE_ADMIN')->andReturnTrue();
        $security->shouldReceive('isGranted')->with('ROLE_USER')->andReturnTrue();

        $access = new AdminUserAccess($security, $this->userFinder($userId, $user));

        $this->assertTrue($access->allowed($userId));
    }

    private function userFinder(UserId $userId, ?User $user): UserFinder
    {
        $userFinder = \Mockery::mock(UserFinder::class);
        $userFinder->shouldReceive('find')
            ->with(\Mockery::on(static fn (UserId $id): bool => $id->sameValueAs($userId)))
            ->andReturn($user);

        return $userFinder;
    }
}
