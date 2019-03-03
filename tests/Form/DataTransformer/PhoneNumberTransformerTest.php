<?php

declare(strict_types=1);

namespace App\Tests\Form\DataTransformer;

use App\Form\DataTransformer\PhoneNumberTransformer;
use App\Model\PhoneNumber;
use App\Tests\BaseTestCase;
use libphonenumber\PhoneNumberUtil;
use Symfony\Component\Form\Exception\TransformationFailedException;

class PhoneNumberTransformerTest extends BaseTestCase
{
    /**
     * @dataProvider transformProvider
     */
    public function testTransform($value, $expected): void
    {
        $result = (new PhoneNumberTransformer())->transform($value);

        $this->assertEquals($expected, $result);
    }

    public function transformProvider(): \Generator
    {
        $faker = self::faker();

        yield [null, null];

        $phoneNumber = $faker->phoneNumberVo();
        yield [$phoneNumber, $phoneNumber->toArray()];
    }

    /**
     * @dataProvider reverseTransformProvider
     */
    public function testReverseTransform($value, $expected): void
    {
        $result = (new PhoneNumberTransformer())->reverseTransform($value);

        $this->assertEquals($expected, $result);
    }

    public function reverseTransformProvider(): \Generator
    {
        $faker = self::faker();

        yield [null, null];

        $util = PhoneNumberUtil::getInstance();
        $phoneNumber = $util->parse($faker->phoneNumber, 'CA');

        yield [$phoneNumber, PhoneNumber::fromObject($phoneNumber)];
    }

    public function testReverseTransformInvalid(): void
    {
        $this->expectException(TransformationFailedException::class);

        (new PhoneNumberTransformer())->reverseTransform('string');
    }
}
