<?php

declare(strict_types=1);

namespace App\GraphQl\Query\User;

use App\Entity\User;
use App\Projection\AuthLog\AuthLogFinder;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;

final readonly class AuthLogsCountQuery implements QueryInterface
{
    public function __construct(private AuthLogFinder $authLogFinder)
    {
    }

    public function __invoke(User $user): int
    {
        return $this->authLogFinder->countByUserId($user);
    }
}
