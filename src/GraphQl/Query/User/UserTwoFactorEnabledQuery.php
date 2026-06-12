<?php

declare(strict_types=1);

namespace App\GraphQl\Query\User;

use App\Entity\User;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;

final class UserTwoFactorEnabledQuery implements QueryInterface
{
    public function __invoke(User $user): bool
    {
        return $user->isTotpAuthenticationEnabled();
    }
}
