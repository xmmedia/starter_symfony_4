<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use App\Model\User\Role;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Core\User\UserInterface;

class Security
{
    public function __construct(private readonly \Symfony\Bundle\SecurityBundle\Security $security)
    {
    }

    public function getUser(): User|UserInterface|null
    {
        return $this->security->getUser();
    }

    public function isGranted(mixed $attributes, mixed $subject = null): bool
    {
        return $this->security->isGranted($attributes, $subject);
    }

    public function isLoggedIn(): bool
    {
        if (null === $this->getToken()) {
            return false;
        }

        return $this->isGranted(
            AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED,
        );
    }

    public function hasAdminRole(): bool
    {
        return $this->isGranted(Role::ROLE_ADMIN()->getValue());
    }

    public function getToken(): ?TokenInterface
    {
        return $this->security->getToken();
    }
}
