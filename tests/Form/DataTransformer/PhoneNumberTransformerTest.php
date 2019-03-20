<?php

declare(strict_types=1);

namespace App\Tests\Form\DataTransformer;

use App\Form\DataTransformer\PhoneNumberTransformer;
use App\Model\PhoneNumber;
use App\Tests\BaseTestCase;
use App\Tests\Model\PhoneNumberDataProvider;
use libphonenumber\PhoneNumberUtil;
use Symfony\Component\Form\Exception\TransformationFailedException;

class PhoneNumberTransformerTest extends BaseTestCase
{
    use PhoneNumberDataProvider;

    /**
     * @dataProvider phoneNumberValidProvider
     */
    public function testTransform(string $void, array $expected): void
    {
        $result = (new PhoneNumberTransformer())->transform(
            PhoneNumber::fromArray($expected)
        );

        $this->assertEquals($expected, $result);
    }

    public function testTransformNull(): void
    {
        $result = (new PhoneNumberTransformer())->transform(null);

        $this->assertNull($result);
    }

    /**
     * @dataProvider phoneNumberValidProvider
     */
    public function testReverseTransform($phoneNumber, $expected): void
    {
        $util = PhoneNumberUtil::getInstance();
        $value = $util->parse($phoneNumber, 'CA');

        $result = (new PhoneNumberTransformer())->reverseTransform($value);

        $this->assertInstanceOf(PhoneNumber::class, $result);
        $this->assertEquals($expected, $result->toArray());
    }

    public function testReverseTransformNull(): void
    {
        $result = (new PhoneNumberTransformer())->reverseTransform(null);

        $this->assertNull($result);
    }

    /**
     * @dataProvider phoneNumberInvalidProvider
     */
    public function testReverseTransformInvalid(string $value): void
    {
        $this->expectException(TransformationFailedException::class);

        (new PhoneNumberTransformer())->reverseTransform($value);
    }
}
