<?php

declare(strict_types=1);

namespace App\GraphQl\Query\User;

use App\Security\Security;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;

final readonly class UserIsImpersonatingQuery implements QueryInterface
{
    public function __construct(private Security $security)
    {
    }

    public function __invoke(): bool
    {
        return $this->security->getToken() instanceof SwitchUserToken;
    }
}
