<?php

declare(strict_types=1);

namespace App\Tests\Util;

use App\Exception\JsonException;
use App\Util\Json;
use PHPUnit\Framework\TestCase;

class JsonTest extends TestCase
{
    /**
     * @dataProvider provider
     */
    public function testEncode(string $value, string $expected): void
    {
        $this->assertEquals($expected, Json::encode($value));
    }

    /**
     * @dataProvider provider
     */
    public function testDecode(string $expected, string $json): void
    {
        $this->assertEquals($expected, Json::decode($json));
    }

    public function provider(): \Generator
    {
        yield ['😱', '"😱"'];
        yield ['/', '"/"'];
        yield [floatval(-1), '"-1"'];
        yield [-1, '"-1"'];
        yield [1343232323, '"1343232323"'];
        yield ['<', '"<"'];
    }

    public function testEncodeError(): void
    {
        $this->expectException(JsonException::class);

        Json::encode("\xB1\x31");
    }

    public function testDecodeError(): void
    {
        $this->expectException(JsonException::class);

        Json::decode("asdf");
    }
}
