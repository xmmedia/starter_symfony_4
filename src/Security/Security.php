<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;

class Security
{
    /** @var \Symfony\Component\Security\Core\Security */
    private $security;

    public function __construct(
        \Symfony\Component\Security\Core\Security $security
    ) {
        $this->security = $security;
    }

    public function getUser(): ?User
    {
        return $this->security->getUser();
    }

    /**
     * @param mixed $attributes
     * @param mixed $subject
     */
    public function isGranted($attributes, $subject = null): bool
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
