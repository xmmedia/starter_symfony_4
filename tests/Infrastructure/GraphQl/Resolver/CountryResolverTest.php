<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\GraphQl\Resolver;

use App\Infrastructure\GraphQl\Resolver\CountryResolver;
use App\Model\Country;
use PHPUnit\Framework\TestCase;

class CountryResolverTest extends TestCase
{
    public function test(): void
    {
        $all = (new CountryResolver())->all();

        $this->assertCount(2, $all);
        $this->assertInstanceOf(Country::class, $all[0]);
    }

    public function testAliases(): void
    {
        $result = CountryResolver::getAliases();

        $expected = [
            'all' => 'app.graphql.resolver.country.all',
        ];

        $this->assertEquals($expected, $result);
    }
}
