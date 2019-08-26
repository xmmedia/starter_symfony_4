<?php

declare(strict_types=1);

namespace App\Tests\Util;

use App\Util\Utils;
use App\Tests\BaseTestCase;

class UtilsTest extends BaseTestCase
{
    /**
     * @dataProvider serializeValidProvider
     */
    public function testSerializeValid($input, $expected): void
    {
        $this->assertSame($expected, Utils::serialize($input));
    }

    public function serializeValidProvider(): \Generator
    {
        yield [null, null];
        yield [true, true];
        yield ['string', 'string'];
        yield [1.3432, 1.3432];
        yield [2, 2];
        yield [['array'], ['array']];
        yield [new ClassWithToString(), 'string'];
        yield [new ClassWithGetValue(), 'string'];
        yield [new ClassWithToArray(), ['array']];
    }

    /**
     * @dataProvider serializeInvalidProvider
     */
    public function testSerializeInvalid($input): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Utils::serialize($input);
    }

    public function serializeInvalidProvider(): \Generator
    {
        yield [new \stdClass()];
    }

    /**
     * @dataProvider printSafeProvider
     */
    public function testPrintSafe($var, string $type): void
    {
        $this->assertSame($type, Utils::printSafe($var));
    }

    public function printSafeProvider(): \Generator
    {
        yield [new \stdClass(), 'instance of stdClass'];
        yield [function () {}, 'instance of Closure'];
        yield [[], 'array'];
        yield ['', '(empty string)'];
        yield [null, 'NULL'];
        yield [false, 'false (boolean)'];
        yield [true, 'true (boolean)'];
        yield ['string', 'string'];
        yield [12, '12'];
        yield [1.3234, '1.3234'];
    }
}

class ClassWithToString
{
    public function __toString(): string
    {
        return 'string';
    }
}

class ClassWithGetValue
{
    public function getValue(): string
    {
        return 'string';
    }
}

class ClassWithToArray
{
    public function toArray(): array
    {
        return ['array'];
    }
}
