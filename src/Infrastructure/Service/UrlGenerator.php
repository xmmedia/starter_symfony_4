<?php

declare(strict_types=1);

namespace App\Infrastructure\Service;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class cannot be readonly because tests include a mock of this class.
 */
class UrlGenerator
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

    public function profile(): string
    {
        return $this->generate('user_profile');
    }
}
