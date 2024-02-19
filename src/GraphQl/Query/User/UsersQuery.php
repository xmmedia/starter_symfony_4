<?php

declare(strict_types=1);

namespace App\GraphQl\Query\User;

use App\Entity\User;
use App\Projection\User\UserFilters;
use App\Projection\User\UserFinder;
use JetBrains\PhpStorm\ArrayShape;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;

final readonly class UsersQuery implements QueryInterface
{
    public function __construct(private UserFinder $userFinder)
    {
    }

    #[ArrayShape([User::class])]
    public function __invoke(?array $filters): array
    {
        return $this->userFinder->findByFilters(UserFilters::fromArray($filters));
    }
}
