<?php

declare(strict_types=1);

namespace App\Tests\DataProvider;

use App\DataProvider\JsData;
use Faker;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouterInterface;

class JsDataTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /** @var JsData */
    private $object;

    /** @var RouterInterface|\Symfony\Bundle\FrameworkBundle\Routing\Router|Mockery\Mock */
    private $router;

    public function setUp(): void
    {
        $this->router = Mockery::mock(RouterInterface::class);

        $this->object = new JsData($this->router);
    }

    public function testGetPublic(): void
    {
        $this->assertSame([], $this->object->get('public'));
    }

    public function testGetAdmin(): void
    {
        $this->assertSame([], $this->object->get('admin'));
    }
}
