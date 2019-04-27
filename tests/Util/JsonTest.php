<?php

declare(strict_types=1);

namespace App\Tests\Util;

use App\Util\Json;
use PHPUnit\Framework\TestCase;

class JsonTest extends TestCase
{
    /**
     * @dataProvider provider
     */
    public function testEncode($value, $expected): void
    {
        $this->assertEquals($expected, Json::encode($value));
    }

    /**
     * @dataProvider provider
     */
    public function testDecode($expected, $json): void
    {
        $this->assertEquals($expected, Json::decode($json));
    }

    public function provider(): \Generator
    {
        yield ['😱', '"😱"'];
        yield ['/', '"/"'];
        yield [floatval(-1), '-1.0'];
        yield [-1.0, '-1.0'];
        yield [-1, '-1'];
        yield [0, '0'];
        yield [0.1, '0.1'];
        yield [true, 'true'];
        yield [1343232323, '1343232323'];
        yield ['<>\'&"', '"<>\'&\""'];
        yield [[[1, 2, 3]], '[[1,2,3]]'];
    }

    public function testEncodeError(): void
    {
        $this->expectException(\JsonException::class);

        Json::encode("\xB1\x31");
    }

    public function testDecodeError(): void
    {
        $this->expectException(\JsonException::class);

        Json::decode('asdf');
    }
}
