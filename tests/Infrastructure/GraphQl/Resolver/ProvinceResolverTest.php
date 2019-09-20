<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\GraphQl\Resolver;

use App\Infrastructure\GraphQl\Resolver\ProvinceResolver;
use App\Model\Province;
use App\Tests\BaseTestCase;

class ProvinceResolverTest extends BaseTestCase
{
    public function test(): void
    {
        $all = (new ProvinceResolver())();

        $this->assertCount(64, $all);
        $this->assertInstanceOf(Province::class, $all[0]);
    }
}
