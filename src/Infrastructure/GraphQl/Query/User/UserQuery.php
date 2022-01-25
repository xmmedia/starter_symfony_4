<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Query\User;

use App\Entity\User;
use App\Model\User\UserId;
use App\Projection\User\UserFinder;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;

class UserQuery implements QueryInterface
{
    /** @var UserFinder */
    private $userFinder;

    public function __construct(UserFinder $userFinder)
    {
        $this->userFinder = $userFinder;
    }

    public function __invoke(string $userId): ?User
    {
        return $this->userFinder->find(UserId::fromString($userId));
    }
}
