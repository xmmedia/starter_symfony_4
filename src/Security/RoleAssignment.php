<?php

declare(strict_types=1);

namespace App\Security;

use App\Model\User\Role;

/**
 * Whether the current user can give a user a role.
 * ROLE_ALLOWED_TO_SWITCH only comes from the role hierarchy, so it can't be given to anyone.
 * Only super admins can make someone a super admin, but anyone can keep a super admin's role.
 */
class RoleAssignment
{
    public function __construct(private readonly Security $security)
    {
    }

    public function allowed(Role $role, ?Role $currentRole = null): bool
    {
        if (Role::ROLE_ALLOWED_TO_SWITCH === $role) {
            return false;
        }

        if (Role::ROLE_SUPER_ADMIN !== $role) {
            return true;
        }

        if ($currentRole === $role) {
            return true;
        }

        return $this->security->isGranted(Role::ROLE_SUPER_ADMIN->value);
    }
}
