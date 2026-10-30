<?php

declare(strict_types=1);

namespace App\Security;

use App\Model\User\Role;

/**
 * Whether the current user can give a user a role: only roles they have themselves,
 * eg only super admins can make someone a super admin.
 * ROLE_ALLOWED_TO_SWITCH only comes from the role hierarchy, so it can't be given to anyone.
 */
class RoleAssignment
{
    public function __construct(private readonly Security $security)
    {
    }

    public function allowed(Role $role): bool
    {
        if (Role::ROLE_ALLOWED_TO_SWITCH === $role) {
            return false;
        }

        return $this->security->isGranted($role->value);
    }
}
