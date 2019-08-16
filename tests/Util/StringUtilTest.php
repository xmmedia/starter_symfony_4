<?php

declare(strict_types=1);

namespace App\Tests\Util;

use App\Tests\BaseTestCase;
use App\Util\StringUtil;

class StringUtilTest extends BaseTestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function test($input, $expected): void
    {
        $this->assertSame($expected, StringUtil::trim($input));
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
        yield ["st\n   ring", "st\n   ring"];
        yield [null, null];
        yield ['', null];
        yield ['    ', null];
        yield [1, 1];
        yield ['  1', '1'];
        yield ['  1   ', '1'];
        yield ['1   ', '1'];
        yield [[], []];
        yield [123, 123];
        yield [1.23, 1.23];

        $class = new \stdClass();
        yield [$class, $class];

        $function = function () {
        };
        yield [$function, $function];

        $symbol = mb_convert_encoding(pack('H*', '2003'), 'UTF-8', 'UCS-2BE');
        yield [$symbol.'string'.$symbol, 'string'];
    }
}
