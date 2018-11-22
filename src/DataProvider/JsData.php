<?php

declare(strict_types=1);

namespace App\DataProvider;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class JsData
{
    /** @var RouterInterface|\Symfony\Bundle\FrameworkBundle\Routing\Router */
    private $router;

    /** @var Security */
    private $security;

    /** @var CsrfTokenManagerInterface */
    private $csrfTokenManager;

    public function __construct(
        RouterInterface $router,
        Security $security,
        CsrfTokenManagerInterface $csrfTokenManager
    ) {
        $this->router = $router;
        $this->security = $security;
        $this->csrfTokenManager = $csrfTokenManager;
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
            'user'      => $this->getUser(),
            'csrfToken' => $this->csrfTokenManager->getToken('admin')->getValue(),
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
            'firstName' => $user->firstName(),
            'lastName'  => $user->lastName(),
        ];
    }
}
