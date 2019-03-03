<?php

declare(strict_types=1);

namespace App\Tests\Form\DataTransformer;

use App\Form\DataTransformer\CountryTransformer;
use App\Model\Country;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Exception\TransformationFailedException;

class CountryTransformerTest extends TestCase
{
    /**
     * @dataProvider transformProvider
     */
    public function testTransform($value, $expected): void
    {
        $result = (new CountryTransformer())->transform($value);

        $this->assertEquals($expected, $result);
    }

    public function transformProvider(): \Generator
    {
        yield [null, null];

        yield [Country::fromString('CA'), 'CA'];
    }

    /**
     * @dataProvider reverseTransformProvider
     */
    public function testReverseTransform($value, $expected): void
    {
        $result = (new CountryTransformer())->reverseTransform($value);

        $this->assertEquals($expected, $result);
    }

    public function reverseTransformProvider(): \Generator
    {
        yield [null, null];

        yield ['CA', Country::fromString('CA')];
    }

    /**
     * @dataProvider reverseTransformInvalidProvider
     */
    public function testReverseTransformInvalid($value): void
    {
        $this->expectException(TransformationFailedException::class);

        (new CountryTransformer())->reverseTransform($value);
    }

    public function reverseTransformInvalidProvider(): \Generator
    {
        yield [''];

        yield ['A'];

        yield ['ABC'];

        yield ['XX'];
    }
}
