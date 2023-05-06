<?php

declare(strict_types=1);

namespace App\Infrastructure\Service;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

final readonly class UrlGenerator
{
    public function __construct(private readonly RouterInterface $router)
    {
    }

    private function generate(string $name, array $params = []): string
    {
        return $this->router->generate(
            $name,
            $params,
            UrlGeneratorInterface::ABSOLUTE_URL,
        );
    }
}
