<?php

declare(strict_types=1);

namespace App\Tests\Util;

use App\Util\StringUtil;
use App\Tests\BaseTestCase;

class StringUtilTest extends BaseTestCase
{
    /**
     * @dataProvider dataProvider
     * @param mixed $input
     * @param mixed $expected
     */
    public function test($input, $expected): void
    {
        $this->assertEquals($expected, StringUtil::trim($input));
    }

    public function dataProvider(): \Generator
    {
        yield ['string', 'string'];
        yield ['   string', 'string'];
        yield ['string    ', 'string'];
        yield ['    string    ', 'string'];
        yield ["\nstring\n", 'string'];
        yield ["\tstring\t", 'string'];
        yield ["  \n   string  \n   ", 'string'];
        yield ["  \t   string  \t   ", 'string'];
        yield ["st\nring", "st\nring"];
        yield [null, null];
        yield [1, 1];
        yield ['  1', '1'];
        yield ['  1   ', '1'];
        yield ['1   ', '1'];
        yield [[], []];
        yield [1.23, 1.23];
        yield [new \stdClass(), new \stdClass()];
        yield [function () {}, function () {}];

        $symbol = mb_convert_encoding(pack('H*', '2003'), 'UTF-8', 'UCS-2BE');
        yield [$symbol.'string'.$symbol, 'string'];
    }
}
