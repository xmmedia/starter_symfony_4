<?php

declare(strict_types=1);

namespace App\Tests\Form\DataTransformer;

use App\Form\DataTransformer\EmailTransformer;
use App\Model\Email;
use Faker;
use PHPUnit\Framework\TestCase;

class EmailTransformerTest extends TestCase
{
    /**
     * @dataProvider transformProvider
     */
    public function testTransform($value, $expected): void
    {
        $result = (new EmailTransformer())->transform($value);

        $this->assertEquals($expected, $result);
    }

    public function transformProvider(): \Generator
    {
        $faker = Faker\Factory::create();

        yield [null, null];

        $email = $faker->email;
        yield [Email::fromString($email), $email];
    }

    /**
     * @dataProvider reverseTransformProvider
     */
    public function testReverseTransform($value, $expected): void
    {
        $result = (new EmailTransformer())->reverseTransform($value);

        $this->assertEquals($expected, $result);
    }

    public function reverseTransformProvider(): \Generator
    {
        $faker = Faker\Factory::create();

        yield [null, null];

        $email = $faker->email;
        yield [$email, Email::fromString($email)];
    }
}
