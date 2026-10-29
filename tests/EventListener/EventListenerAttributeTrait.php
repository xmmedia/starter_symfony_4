<?php

declare(strict_types=1);

namespace App\Tests\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

trait EventListenerAttributeTrait
{
    private function eventListenerAttribute(string $class, string $method): AsEventListener
    {
        $attributes = new \ReflectionMethod($class, $method)->getAttributes(AsEventListener::class);

        $this->assertCount(1, $attributes);

        return $attributes[0]->newInstance();
    }
}
