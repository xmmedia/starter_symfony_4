<?php

declare(strict_types=1);

namespace App\Tests\Form\DataTransformer;

use App\Form\DataTransformer\ProvinceTransformer;
use App\Model\Province;
use Faker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Exception\TransformationFailedException;

class ProvinceTransformerTest extends TestCase
{
    /**
     * @dataProvider transformProvider
     */
    public function testTransform($value, $expected): void
    {
        $result = (new ProvinceTransformer())->transform($value);

        $this->assertEquals($expected, $result);
    }

    public function transformProvider(): \Generator
    {
        $faker = Faker\Factory::create();

        yield [null, null];

        $province = $faker->stateAbbr;
        yield [Province::fromString($province), $province];
    }

    /**
     * @dataProvider reverseTransformProvider
     */
    public function testReverseTransform($value, $expected): void
    {
        $result = (new ProvinceTransformer())->reverseTransform($value);

        $this->assertEquals($expected, $result);
    }

    public function reverseTransformProvider(): \Generator
    {
        $faker = Faker\Factory::create();

        yield [null, null];

        $province = $faker->stateAbbr;
        yield [$province, Province::fromString($province)];
    }

    /**
     * @dataProvider reverseTransformInvalidProvider
     */
    public function testReverseTransformInvalid($value): void
    {
        $this->expectException(TransformationFailedException::class);

        (new ProvinceTransformer())->reverseTransform($value);
    }

    public function reverseTransformInvalidProvider(): \Generator
    {
        yield [''];

        yield ['A'];

        yield ['ABC'];

        yield ['XX'];
    }
}
