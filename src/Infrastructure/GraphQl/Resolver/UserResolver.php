<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Resolver;

use App\Entity\User;
use App\Model\User\UserId;
use App\Projection\User\UserFinder;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

class UserResolver implements ResolverInterface, AliasedInterface
{
    /** @var UserFinder */
    private $userFinder;

    public function __construct(UserFinder $userFinder)
    {
        $this->userFinder = $userFinder;
    }

    /**
     * @return User[]
     */
    public function all(): array
    {
        return $this->userFinder->findBy([], ['email' => 'ASC']);
    }

    public function userByUserId(string $userId): ?User
    {
        return $this->userFinder->find(UserId::fromString($userId));
    }

    public static function getAliases(): array
    {
        return [
            'all'          => 'app.graphql.resolver.user.all',
            'userByUserId' => 'app.graphql.resolver.user.by.userId',
        ];
    }
}
