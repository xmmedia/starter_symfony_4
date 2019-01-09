<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Resolver;

use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class CurrentUserResolver implements ResolverInterface
{
    /** @var Security */
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function __invoke(): ?UserInterface
    {
        return $this->security->getUser();
    }
}
