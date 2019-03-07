<?php

declare(strict_types=1);

namespace App\Tests\DataProvider;

use App\DataProvider\RoleProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class RoleProviderTest extends KernelTestCase
{
    public function test(): void
    {
        self::bootKernel();
        /** @var \Symfony\Bundle\FrameworkBundle\Test\TestContainer $container */
        $container = self::$container;

        $result = ($container->get(RoleProvider::class))();

        // if changes are made to this list,
        // changes will also likely be needed in JS or other PHP code
        $expected = [
            'ROLE_SUPER_ADMIN',
            'ROLE_ADMIN',
            'ROLE_USER',
        ];

        $this->assertEquals($expected, $result);
    }
}
