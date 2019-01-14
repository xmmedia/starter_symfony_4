<?php

declare(strict_types=1);

namespace App\Form\DataTransformer;

use App\DataProvider\RoleProvider;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Security\Core\Role\Role;

class SecurityRoleTransformer implements DataTransformerInterface
{
    /** @var RoleProvider */
    private $roleProvider;

    public function __construct(RoleProvider $roleProvider)
    {
        $this->roleProvider = $roleProvider;
    }

    /**
     * From Role to form value.
     *
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
     * From user input to Role.
     *
     * @param string|null $role
     */
    public function reverseTransform($role): ?Role
    {
        if (null === $role) {
            return null;
        }

        $role = strtoupper($role);

        if (!in_array($role, ($this->roleProvider)())) {
            throw new TransformationFailedException(
                sprintf('The role "%s" does not exist.', $role)
            );
        }

        return new Role($role);
    }
}
