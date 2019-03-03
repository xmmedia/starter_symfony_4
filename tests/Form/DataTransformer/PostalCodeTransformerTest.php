<?php

declare(strict_types=1);

namespace App\Tests\Form\DataTransformer;

use App\Form\DataTransformer\PostalCodeTransformer;
use App\Model\PostalCode;
use Faker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Exception\TransformationFailedException;

class PostalCodeTransformerTest extends TestCase
{
    /**
     * @dataProvider transformProvider
     */
    public function testTransform($value, $expected): void
    {
        $result = (new PostalCodeTransformer())->transform($value);

        $this->assertEquals($expected, $result);
    }

    public function transformProvider(): \Generator
    {
        $faker = Faker\Factory::create();

        yield [null, null];

        $postalCode = $faker->postcode;
        yield [PostalCode::fromString($postalCode), $postalCode];
    }

    /**
     * @dataProvider reverseTransformProvider
     */
    public function testReverseTransform($value, $expected): void
    {
        $result = (new PostalCodeTransformer())->reverseTransform($value);

        $this->assertEquals($expected, $result);
    }

    public function reverseTransformProvider(): \Generator
    {
        $faker = Faker\Factory::create();

        yield [null, null];

        $postalCode = $faker->postcode;
        yield [$postalCode, PostalCode::fromString($postalCode)];
    }

    /**
     * @dataProvider reverseTransformInvalidProvider
     */
    public function testReverseTransformInvalid($value): void
    {
        $this->expectException(TransformationFailedException::class);

        (new PostalCodeTransformer())->reverseTransform($value);
    }

    public function reverseTransformInvalidProvider(): \Generator
    {
        yield [''];

        yield ['A'];

        yield ['ABC ABC ABC ABC'];
    }
}
