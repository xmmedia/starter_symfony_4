<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Query\User;

use App\Projection\User\UserFilters;
use App\Projection\User\UserFinder;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;

final readonly class UserCountQuery implements QueryInterface
{
    public function __construct(private UserFinder $userFinder)
    {
    }

    public function __invoke(?array $filters): int
    {
        return $this->userFinder->countByFilters(UserFilters::fromArray($filters));
    }
}
