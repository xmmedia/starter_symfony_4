<?php

declare(strict_types=1);

namespace App\Tests\Security;

use App\Model\User\Role;
use App\Security\RoleAssignment;
use App\Security\Security;
use App\Tests\BaseTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class RoleAssignmentTest extends BaseTestCase
{
    #[DataProvider('allowedProvider')]
    public function testAllowed(Role $role, ?Role $currentRole, bool $isSuperAdmin, bool $expected): void
    {
        $security = \Mockery::mock(Security::class);
        $security->shouldReceive('isGranted')
            ->with(Role::ROLE_SUPER_ADMIN->value)
            ->andReturn($isSuperAdmin);

        $this->assertSame($expected, new RoleAssignment($security)->allowed($role, $currentRole));
    }

    public static function allowedProvider(): \Generator
    {
        yield 'user' => [Role::ROLE_USER, null, false, true];
        yield 'admin' => [Role::ROLE_ADMIN, Role::ROLE_USER, false, true];
        yield 'switch as admin' => [Role::ROLE_ALLOWED_TO_SWITCH, null, false, false];
        yield 'switch as super admin' => [Role::ROLE_ALLOWED_TO_SWITCH, null, true, false];
        yield 'switch kept' => [Role::ROLE_ALLOWED_TO_SWITCH, Role::ROLE_ALLOWED_TO_SWITCH, true, false];
        yield 'super admin as admin' => [Role::ROLE_SUPER_ADMIN, null, false, false];
        yield 'promote to super admin as admin' => [Role::ROLE_SUPER_ADMIN, Role::ROLE_ADMIN, false, false];
        yield 'super admin kept as admin' => [Role::ROLE_SUPER_ADMIN, Role::ROLE_SUPER_ADMIN, false, true];
        yield 'super admin as super admin' => [Role::ROLE_SUPER_ADMIN, null, true, true];
    }
}
