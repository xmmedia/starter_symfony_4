<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\GraphQl\Resolver;

use App\Infrastructure\GraphQl\Resolver\CountryResolver;
use App\Model\Country;
use App\Tests\BaseTestCase;

class CountryResolverTest extends BaseTestCase
{
    public function test(): void
    {
        $all = (new CountryResolver())();

        $this->assertCount(2, $all);
        $this->assertInstanceOf(Country::class, $all[0]);
    }
}
