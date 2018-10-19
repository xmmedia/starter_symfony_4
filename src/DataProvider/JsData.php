<?php

namespace App\DataProvider;

use Symfony\Component\Routing\RouterInterface;

class JsData
{
    /** @var RouterInterface|\Symfony\Bundle\FrameworkBundle\Routing\Router */
    private $router;

    /**
     * GlobalJsData constructor.
     *
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * Retrieves the data for JS, based on the base JS file.
     * Options: public or admin.
     *
     * @param string $jsBase
     *
     * @return array
     */
    public function get(string $jsBase): array
    {
        return $this->{'get'.$jsBase}();
    }

    /**
     * Retrieve the data for the public JS file.
     *
     * @return array
     */
    private function getPublic(): array
    {
        return [];
    }

    /**
     * Retrieve the data for the admin JS file.
     *
     * @return array
     */
    private function getAdmin(): array
    {
        return [];
    }
}