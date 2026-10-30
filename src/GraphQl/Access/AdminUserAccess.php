<?php

declare(strict_types=1);

namespace App\GraphQl\Access;

use App\Model\User\UserId;
use App\Projection\User\UserFinder;
use App\Security\Security;

/**
 * Admins can only change users whose roles they have themselves,
 * eg an admin can't change a super admin.
 */
final readonly class AdminUserAccess
{
    public function __construct(private Security $security, private UserFinder $userFinder)
    {
    }

    public function allowed(UserId $userId): bool
    {
        if (!$this->security->hasAdminRole()) {
            return false;
        }

        // left to the mutation to say it doesn't exist
        $user = $this->userFinder->find($userId);
        if (null === $user) {
            return true;
        }

        return array_all($user->roles(), fn (string $role): bool => $this->security->isGranted($role));
    }
}
