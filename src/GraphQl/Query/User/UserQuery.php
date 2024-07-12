<?php

declare(strict_types=1);

namespace App\GraphQl\Query\User;

use App\Entity\User;
use App\Model\User\UserId;
use App\Projection\User\UserFinder;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;

final readonly class UserQuery implements QueryInterface
{
    public function __construct(private UserFinder $userFinder)
    {
    }

    public function __invoke(UserId $userId): ?User
    {
        return $this->userFinder->find($userId);
    }
}
