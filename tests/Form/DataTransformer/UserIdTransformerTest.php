<?php

declare(strict_types=1);

namespace App\Tests\Form\DataTransformer;

use App\Form\DataTransformer\UserIdTransformer;
use App\Model\User\UserId;
use App\Tests\BaseTestCase;
use Symfony\Component\Form\Exception\TransformationFailedException;

class UserIdTransformerTest extends BaseTestCase
{
    /**
     * @dataProvider transformProvider
     */
    public function testTransform($value, $expected): void
    {
        $result = (new UserIdTransformer())->transform($value);

        $this->assertEquals($expected, $result);
    }

    public function transformProvider(): \Generator
    {
        $faker = $this->faker();

        yield [null, null];

        $uuid = $faker->uuid;
        yield [UserId::fromString($uuid), $uuid];
    }

    /**
     * @dataProvider reverseTransformProvider
     */
    public function testReverseTransform($value, $expected): void
    {
        $result = (new UserIdTransformer())->reverseTransform($value);

        $this->assertEquals($expected, $result);
    }

    public function reverseTransformProvider(): \Generator
    {
        $faker = $this->faker();

        yield [null, null];

        $uuid = $faker->uuid;
        yield [$uuid, UserId::fromString($uuid)];
    }

    /**
     * @dataProvider reverseTransformInvalidProvider
     */
    public function testReverseTransformInvalid($value): void
    {
        $this->expectException(TransformationFailedException::class);

        (new UserIdTransformer())->reverseTransform($value);
    }

    public function reverseTransformInvalidProvider(): \Generator
    {
        yield [''];

        yield ['1234'];

        yield ['asdf'];
    }
}
