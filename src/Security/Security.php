<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Core\User\UserInterface;

class Security
{
    public function __construct(private \Symfony\Component\Security\Core\Security $security)
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
        return $this->isGranted('ROLE_ADMIN');
    }

    public function getToken(): ?TokenInterface
    {
        return $this->security->getToken();
    }
}
