<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Query\User;

use App\Entity\User;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

final readonly class UserRoleQuery implements QueryInterface
{
    public function __construct(private RoleHierarchyInterface $roleHierarchy)
    {
    }

    public function __invoke(User $user): array
    {
        return array_unique($this->roleHierarchy->getReachableRoleNames($user->roles()));
    }
}
