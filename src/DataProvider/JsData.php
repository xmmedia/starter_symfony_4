<?php

declare(strict_types=1);

namespace App\DataProvider;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Security;

class JsData
{
    /** @var RouterInterface|\Symfony\Bundle\FrameworkBundle\Routing\Router */
    private $router;

    /** @var Security */
    private $security;

    public function __construct(RouterInterface $router, Security $security)
    {
        $this->router = $router;
        $this->security = $security;
    }

    /**
     * Retrieves the data for JS, based on the base JS file.
     * Options: public or admin.
     */
    public function get(string $jsBase): array
    {
        return $this->{'get'.$jsBase}();
    }

    private function getPublic(): array
    {
        return [];
    }

    private function getAdmin(): array
    {
        return [
            'user' => $this->getUser(),
        ];
    }

    private function getUser(): ?array
    {
        /** @var \App\Entity\User $user */
        $user = $this->security->getUser();

        if (null === $user) {
            return null;
        }

        return [
            'email'     => $user->email()->toString(),
            'firstName' => null !== $user->firstName() ? $user->firstName()->toString() : null,
            'lastName'  => null !== $user->lastName() ? $user->lastName()->toString() : null,
        ];
    }
}
