<?php

declare(strict_types=1);

namespace App\DataProvider;

use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

class RoleProvider
{
    private const HIGHEST_ROLE = 'ROLE_SUPER_ADMIN';

    /** @var RoleHierarchyInterface */
    private $roleHierarchy;

    public function __construct(RoleHierarchyInterface $roleHierarchy)
    {
        $this->roleHierarchy = $roleHierarchy;
    }

    public function __invoke(): array
    {
        return array_map(function (Role $role) {
            return $role->getRole();
        }, $this->roleHierarchy->getReachableRoles([new Role(self::HIGHEST_ROLE)]));
    }
}
