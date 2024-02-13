<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Query\User;

use App\Entity\User;
use App\Projection\User\UserFilters;
use App\Projection\User\UserFinder;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;

final readonly class UsersQuery implements QueryInterface
{
    public function __construct(private UserFinder $userFinder)
    {
    }

    /**
     * @return User[]
     */
    public function __invoke(?array $filters): array
    {
        return $this->userFinder->findByFilters(UserFilters::fromArray($filters));
    }
}
