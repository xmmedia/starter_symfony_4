<?php

declare(strict_types=1);

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Security\Core\Role\Role;

class SecurityRoleTransformer implements DataTransformerInterface
{
    /**
     * @param Role|null $role
     */
    public function transform($role): ?string
    {
        if (null === $role) {
            return null;
        }

        return $role->getRole();
    }

    /**
     * @param string|null $role
     */
    public function reverseTransform($role): ?Role
    {
        if (null === $role) {
            return null;
        }

        return new Role($role);
    }
}
