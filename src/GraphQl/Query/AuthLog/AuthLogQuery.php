<?php

declare(strict_types=1);

namespace App\GraphQl\Query\AuthLog;

use App\Entity\AuthLog;
use App\Model\AuthLog\AuthLogId;
use App\Projection\AuthLog\AuthLogFinder;
use JetBrains\PhpStorm\ArrayShape;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;

final readonly class AuthLogQuery implements QueryInterface
{
    public function __construct(private AuthLogFinder $authLogFinder)
    {
    }

    #[ArrayShape([AuthLog::class])]
    public function __invoke(AuthLogId $authLogId): ?AuthLog
    {
        return $this->authLogFinder->find($authLogId);
    }
}
