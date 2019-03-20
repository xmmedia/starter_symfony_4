<?php

declare(strict_types=1);

namespace App\Tests\Form\DataTransformer;

use App\Form\DataTransformer\GenderTransformer;
use App\Model\Gender;
use App\Tests\BaseTestCase;
use Symfony\Component\Form\Exception\TransformationFailedException;

class GenderTransformerTest extends BaseTestCase
{
    /**
     * @dataProvider transformProvider
     */
    public function testTransform($value, $expected): void
    {
        $result = (new GenderTransformer())->transform($value);

        $this->assertEquals($expected, $result);
    }

    public function transformProvider(): \Generator
    {
        $faker = $this->faker();

        yield [null, null];

        $gender = $faker->gender;
        yield [Gender::byValue($gender), $gender];
    }

    /**
     * @dataProvider reverseTransformProvider
     */
    public function testReverseTransform($value, $expected): void
    {
        $result = (new GenderTransformer())->reverseTransform($value);

        $this->assertEquals($expected, $result);
    }

    public function reverseTransformProvider(): \Generator
    {
        $faker = $this->faker();

        yield [null, null];

        $gender = $faker->gender;
        yield [$gender, Gender::byValue($gender)];
    }

    public function testTransformationException(): void
    {
        $this->expectException(TransformationFailedException::class);

        (new GenderTransformer())->reverseTransform('u');
    }
}
