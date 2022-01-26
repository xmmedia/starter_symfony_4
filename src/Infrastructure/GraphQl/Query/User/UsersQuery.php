<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Query\User;

use App\Entity\User;
use App\Projection\User\UserFilters;
use App\Projection\User\UserFinder;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;

class UsersQuery implements QueryInterface
{
    private UserFinder $userFinder;

    public function __construct(UserFinder $userFinder)
    {
        $this->userFinder = $userFinder;
    }

    /**
     * @return User[]
     */
    public function __invoke(?array $filters): array
    {
        return $this->userFinder->findByUserFilters(
            UserFilters::fromArray($filters),
        );
    }
}
