<?php

declare(strict_types=1);

namespace App\Tests\Model;

use App\Model\MoneyFactory;
use PHPUnit\Framework\TestCase;

class MoneyFactoryTest extends TestCase
{
    /**
     * @dataProvider intDataProvider
     */
    public function testFromInt($cents, $expected): void
    {
        $res = MoneyFactory::fromInt($cents);

        $this->assertEquals($expected, $res->getAmount());
        $this->assertEquals('CAD', $res->getCurrency());
    }

    public function intDataProvider(): array
    {
        return [
            [533, '533'],
            [5330, '5330'],
            [53309, '53309'],
            [5, '5'],
            [53, '53'],
        ];
    }

    public function testZero(): void
    {
        $res = MoneyFactory::zero();

        $this->assertEquals('0', $res->getAmount());
        $this->assertEquals('CAD', $res->getCurrency());
    }
}
