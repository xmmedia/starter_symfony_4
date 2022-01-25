<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\GraphQl\Query\User;

use App\Entity\User;
use App\Infrastructure\GraphQl\Query\User\UserRoleQuery;
use App\Model\User\Role;
use App\Tests\BaseTestCase;
use Mockery;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

class UserRoleQueryTest extends BaseTestCase
{
    public function test(): void
    {
        $userRoles = [Role::ROLE_USER()->getValue()];
        $resolvedRoles = Role::getValues();

        $roleHierarchy = Mockery::mock(RoleHierarchyInterface::class);
        $roleHierarchy->shouldReceive('getReachableRoleNames')
            ->once()
            ->with($userRoles)
            ->andReturn($resolvedRoles);

        $user = Mockery::mock(User::class);
        $user->shouldReceive('roles')
            ->once()
            ->andReturn($userRoles);

        (new UserRoleQuery($roleHierarchy))($user);
    }
}
