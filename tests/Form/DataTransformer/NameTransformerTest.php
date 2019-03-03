<?php

declare(strict_types=1);

namespace App\Tests\Form\DataTransformer;

use App\Form\DataTransformer\NameTransformer;
use App\Model\User\Name;
use App\Tests\BaseTestCase;

class NameTransformerTest extends BaseTestCase
{
    /**
     * @dataProvider transformProvider
     */
    public function testTransform($value, $expected): void
    {
        $result = (new NameTransformer())->transform($value);

        $this->assertEquals($expected, $result);
    }

    public function transformProvider(): \Generator
    {
        $faker = $this->faker();

        yield [null, null];

        $name = $faker->name;
        yield [Name::fromString($name), $name];
    }

    /**
     * @dataProvider reverseTransformProvider
     */
    public function testReverseTransform($value, $expected): void
    {
        $result = (new NameTransformer())->reverseTransform($value);

        $this->assertEquals($expected, $result);
    }

    public function reverseTransformProvider(): \Generator
    {
        $faker = $this->faker();

        yield [null, null];

        $name = $faker->email;
        yield [$name, Name::fromString($name)];
    }
}
