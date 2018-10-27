<?php

declare(strict_types=1);

namespace App\DataProvider;

use Symfony\Component\Routing\RouterInterface;

class JsData
{
    /** @var RouterInterface|\Symfony\Bundle\FrameworkBundle\Routing\Router */
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
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
        return [];
    }
}
