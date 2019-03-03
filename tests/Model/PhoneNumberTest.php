<?php

declare(strict_types=1);

namespace App\Tests\Model;

use App\Model\PhoneNumber;
use App\Tests\BaseTestCase;
use libphonenumber\PhoneNumberUtil;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

class PhoneNumberTest extends BaseTestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @dataProvider phoneNumberProvider
     */
    public function testFromArray(string $void, array $data): void
    {
        $phoneNumber = PhoneNumber::fromArray($data);

        $this->assertEquals($data, $phoneNumber->toArray());
        $this->assertEquals($data['phoneNumber'], $phoneNumber->phoneNumber());
        $this->assertEquals($data['extension'], $phoneNumber->extension());
    }

    /**
     * @dataProvider phoneNumberProvider
     */
    public function testFromString(string $string, array $expected): void
    {
        $phoneNumber = PhoneNumber::fromString($string);

        $this->assertEquals($expected, $phoneNumber->toArray());
        $this->assertEquals($expected['phoneNumber'], $phoneNumber->phoneNumber());
        $this->assertEquals($expected['extension'], $phoneNumber->extension());
    }

    /**
     * @dataProvider phoneNumberProvider
     */
    public function testFromObject(string $string, array $expected): void
    {
        $util = PhoneNumberUtil::getInstance();

        $phoneNumber = PhoneNumber::fromObject($util->parse($string, 'CA'));

        $this->assertEquals($expected, $phoneNumber->toArray());
        $this->assertEquals($expected['phoneNumber'], $phoneNumber->phoneNumber());
        $this->assertEquals($expected['extension'], $phoneNumber->extension());
    }

    /**
     * @dataProvider phoneNumberProvider
     */
    public function testSameValueAs(string $string): void
    {
        $phoneNumber1 = PhoneNumber::fromString($string);
        $phoneNumber2 = PhoneNumber::fromString($string);

        $this->assertTrue($phoneNumber1->sameValueAs($phoneNumber2));
    }

    public function phoneNumberProvider(): \Generator
    {
        yield [
            '403-543-3233',
            ['phoneNumber' => '+14035433233', 'extension' => null],
        ];

        yield [
            '1-403-543-3233',
            ['phoneNumber' => '+14035433233', 'extension' => null],
        ];

        yield [
            '403.543.3233',
            ['phoneNumber' => '+14035433233', 'extension' => null],
        ];

        yield [
            '1.403.543.3233',
            ['phoneNumber' => '+14035433233', 'extension' => null],
        ];

        yield [
            '+1-403-543-3233',
            ['phoneNumber' => '+14035433233', 'extension' => null],
        ];

        yield [
            '+1-403-543-3233 x 3233',
            ['phoneNumber' => '+14035433233', 'extension' => '3233'],
        ];

        yield [
            '+1-403-543-3233 ext 3233',
            ['phoneNumber' => '+14035433233', 'extension' => '3233'],
        ];

        yield [
            '+1-403-543-3233 3233',
            ['phoneNumber' => '+140354332333233', 'extension' => null],
        ];

        yield [
            '+1-403-543-3233 x3233',
            ['phoneNumber' => '+14035433233', 'extension' => '3233'],
        ];

        yield [
            '(403) 543-3233',
            ['phoneNumber' => '+14035433233', 'extension' => null],
        ];

        yield [
            '(403)543-3233',
            ['phoneNumber' => '+14035433233', 'extension' => null],
        ];

        yield [
            '(403)543-3233',
            ['phoneNumber' => '+14035433233', 'extension' => null],
        ];

        yield [
            '(403)543-3233 ext 123',
            ['phoneNumber' => '+14035433233', 'extension' => '123'],
        ];

        yield [
            '(403)543-3233 x 123',
            ['phoneNumber' => '+14035433233', 'extension' => '123'],
        ];

        yield [
            '1 (403)543-3233 x 123',
            ['phoneNumber' => '+14035433233', 'extension' => '123'],
        ];

        yield [
            '+1 (403)543-3233 x 123',
            ['phoneNumber' => '+14035433233', 'extension' => '123'],
        ];

        yield [
            '201-886-0269 x3767',
            ['phoneNumber' => '+12018860269', 'extension' => '3767'],
        ];

        yield [
            '(888) 937-7238',
            ['phoneNumber' => '+18889377238', 'extension' => null],
        ];

        yield [
            '+27113456789',
            ['phoneNumber' => '+27113456789', 'extension' => null],
        ];

        yield [
            '+17113456789',
            ['phoneNumber' => '+17113456789', 'extension' => null],
        ];
    }
}
