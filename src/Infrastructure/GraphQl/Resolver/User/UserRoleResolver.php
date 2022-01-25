<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Resolver\User;

use App\Entity\User;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

class UserRoleResolver implements ResolverInterface
{
    /** @var RoleHierarchyInterface */
    private $roleHierarchy;

    public function __construct(RoleHierarchyInterface $roleHierarchy)
    {
        $this->roleHierarchy = $roleHierarchy;
    }

    public function __invoke(User $user): array
    {
        return array_unique(
            $this->roleHierarchy->getReachableRoleNames($user->roles()),
        );
    }
}
