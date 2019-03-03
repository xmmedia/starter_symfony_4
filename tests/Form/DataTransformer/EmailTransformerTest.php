<?php

declare(strict_types=1);

namespace App\Tests\Form\DataTransformer;

use App\Form\DataTransformer\EmailTransformer;
use App\Model\Email;
use App\Tests\BaseTestCase;

class EmailTransformerTest extends BaseTestCase
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
        $faker = $this->faker();

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
        $faker = $this->faker();

        yield [null, null];

        $email = $faker->email;
        yield [$email, Email::fromString($email)];
    }
}
