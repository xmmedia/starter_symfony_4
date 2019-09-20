<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Resolver\User;

use App\Entity\User;
use App\Projection\User\UserFinder;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

class UsersResolver implements ResolverInterface
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
    public function __invoke(): array
    {
        return $this->userFinder->findBy([], ['email' => 'ASC']);
    }
}
