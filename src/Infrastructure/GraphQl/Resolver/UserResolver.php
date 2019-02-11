<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Resolver;

use App\Entity\User;
use App\Model\User\UserId;
use App\Repository\UserRepository;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

class UserResolver implements ResolverInterface, AliasedInterface
{
    /** @var UserRepository */
    private $userRepo;

    public function __construct(UserRepository $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    /**
     * @return User[]
     */
    public function all(): array
    {
        return $this->userRepo->findBy([], ['email' => 'ASC']);
    }

    public function userByUserId(string $userId): ?User
    {
        return $this->userRepo->find(UserId::fromString($userId));
    }

    public static function getAliases(): array
    {
        return [
            'all'          => 'app.graphql.resolver.user.all',
            'userByUserId' => 'app.graphql.resolver.user.by.userId',
        ];
    }
}
