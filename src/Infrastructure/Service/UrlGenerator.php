<?php

declare(strict_types=1);

namespace App\Infrastructure\Service;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class UrlGenerator
{
    /** @var RouterInterface|\Symfony\Bundle\FrameworkBundle\Routing\Router */
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    private function generate(string $name, array $params): string
    {
        return $this->router->generate(
            $name,
            $params,
            UrlGeneratorInterface::ABSOLUTE_URL,
        );
    }
}
