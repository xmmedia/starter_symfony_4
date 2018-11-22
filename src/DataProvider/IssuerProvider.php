<?php

declare(strict_types=1);

namespace App\DataProvider;

use Symfony\Component\Security\Core\Security;

class IssuerProvider
{
    /** @var Security */
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function getIssuer(): string
    {
        if (null === $token = $this->security->getToken()) {
            return 'cli';
        }

        /** @var \App\Entity\User $user */
        $user = $this->security->getUser();

        if (!$user) {
            return 'anonymous';
        }

        return $user->id()->toString();
    }
}
