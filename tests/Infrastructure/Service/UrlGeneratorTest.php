<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Service;

use App\Infrastructure\Service\UrlGenerator;
use App\Tests\BaseTestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class UrlGeneratorTest extends BaseTestCase
{
    public function test(): void
    {
        $router = \Mockery::mock(RouterInterface::class);
        $router->shouldReceive('generate')
            ->once()
            ->with('test', ['test' => 'test'], UrlGeneratorInterface::ABSOLUTE_URL)
            ->andReturn('/test');

        $generator = new UrlGenerator($router);

        $reflection = new \ReflectionClass(\get_class($generator));
        $method = $reflection->getMethod('generate');
        $method->setAccessible(true);

        $result = $method->invokeArgs($generator, ['test', ['test' => 'test']]);

        $this->assertEquals('/test', $result);
    }
}
