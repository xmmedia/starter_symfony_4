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

    public function all(): array
    {
        return $this->userRepo->findBy([], ['email' => 'ASC']);
    }

    public function userById(string $id): ?User
    {
        return $this->userRepo->find(UserId::fromString($id));
    }

    public static function getAliases()
    {
        return [
            'all'      => 'app.graphql.resolver.user.all',
            'userById' => 'app.graphql.resolver.user.by.id',
        ];
    }
}
