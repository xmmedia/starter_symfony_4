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
    public function testAllowed(Role $role, bool $granted, bool $expected): void
    {
        $security = \Mockery::mock(Security::class);
        $security->shouldReceive('isGranted')
            ->with($role->value)
            ->andReturn($granted);

        $this->assertSame($expected, new RoleAssignment($security)->allowed($role));
    }

    public static function allowedProvider(): \Generator
    {
        yield 'user' => [Role::ROLE_USER, true, true];
        yield 'admin' => [Role::ROLE_ADMIN, true, true];
        yield 'super admin as admin' => [Role::ROLE_SUPER_ADMIN, false, false];
        yield 'super admin as super admin' => [Role::ROLE_SUPER_ADMIN, true, true];
        yield 'switch as admin' => [Role::ROLE_ALLOWED_TO_SWITCH, false, false];
        yield 'switch as super admin' => [Role::ROLE_ALLOWED_TO_SWITCH, true, false];
    }
}
