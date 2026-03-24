<?php

declare(strict_types=1);

namespace App\GraphQl\Query\User;

use App\Entity\User;
use App\Projection\AuthLog\AuthLogFinder;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;

final readonly class AuthLogsQuery implements QueryInterface
{
    public function __construct(private AuthLogFinder $authLogFinder)
    {
    }

    public function __invoke(User $user, int $offset = 0, int $limit = 30): array
    {
        return $this->authLogFinder->findByUserId($user->userId(), $limit, $offset);
    }
}
