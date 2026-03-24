<?php

declare(strict_types=1);

namespace App\GraphQl\Query\AuthLog;

use App\Projection\AuthLog\AuthLogFilters;
use App\Projection\AuthLog\AuthLogFinder;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;

final readonly class AuthLogsQuery implements QueryInterface
{
    public function __construct(private AuthLogFinder $authLogFinder)
    {
    }

    public function __invoke(?array $filters): array
    {
        return $this->authLogFinder->findByFilters(AuthLogFilters::fromArray($filters));
    }
}
