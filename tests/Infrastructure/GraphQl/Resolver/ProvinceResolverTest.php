<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\GraphQl\Resolver;

use App\Infrastructure\GraphQl\Resolver\ProvinceResolver;
use App\Model\Province;
use App\Tests\BaseTestCase;
use PHPUnit\Framework\TestCase;

class ProvinceResolverTest extends BaseTestCase
{
    public function test(): void
    {
        $all = (new ProvinceResolver())->all();

        $this->assertCount(13, $all);
        $this->assertInstanceOf(Province::class, $all[0]);
    }

    public function testAliases(): void
    {
        $result = ProvinceResolver::getAliases();

        $expected = [
            'all' => 'app.graphql.resolver.province.all',
        ];

        $this->assertEquals($expected, $result);

        $this->assertHasAllResolverMethods(new ProvinceResolver());
    }
}
