<?php

declare(strict_types=1);

namespace App\Tests\Form\DataTransformer;

use App\Form\DataTransformer\UserIdTransformer;
use App\Model\User\UserId;
use Faker;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Exception\InvalidUuidStringException;

class UserIdTransformerTest extends TestCase
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
        $faker = Faker\Factory::create();

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
        $faker = Faker\Factory::create();

        yield [null, null];

        $uuid = $faker->uuid;
        yield [$uuid, UserId::fromString($uuid)];
    }

    /**
     * @dataProvider reverseTransformInvalidProvider
     */
    public function testReverseTransformInvalid($value): void
    {
        $this->expectException(InvalidUuidStringException::class);

        (new UserIdTransformer())->reverseTransform($value);
    }

    public function reverseTransformInvalidProvider(): \Generator
    {
        yield [''];

        yield ['1234'];

        yield ['asdf'];
    }
}
