<?php

declare(strict_types=1);

namespace App\Tests\Model;

use App\Model\PhoneNumber;
use App\Tests\BaseTestCase;
use App\Tests\FakeVo;
use libphonenumber\PhoneNumberUtil;

class PhoneNumberTest extends BaseTestCase
{
    use PhoneNumberDataProvider;

    /**
     * @dataProvider phoneNumberValidProvider
     */
    public function testFromArray(string $void, array $data): void
    {
        $phoneNumber = PhoneNumber::fromArray($data);

        $this->assertEquals($data, $phoneNumber->toArray());
        $this->assertEquals($data['phoneNumber'], $phoneNumber->phoneNumber());
        $this->assertEquals($data['extension'], $phoneNumber->extension());
    }

    /**
     * @dataProvider phoneNumberInvalidProvider
     */
    public function testFromArrayInvalid(string $string): void
    {
        $this->expectException(\InvalidArgumentException::class);

        PhoneNumber::fromArray([
            'phoneNumber' => $string,
        ]);
    }

    /**
     * @dataProvider phoneNumberValidProvider
     */
    public function testFromString(string $string, array $expected): void
    {
        $phoneNumber = PhoneNumber::fromString($string);

        $this->assertEquals($expected, $phoneNumber->toArray());
        $this->assertEquals($expected['phoneNumber'], $phoneNumber->phoneNumber());
        $this->assertEquals($expected['extension'], $phoneNumber->extension());
    }

    /**
     * @dataProvider phoneNumberValidProvider
     */
    public function testToString(string $string, array $expected): void
    {
        $phoneNumber = PhoneNumber::fromString($string);

        $this->assertEquals($expected['phoneNumber'], (string) $phoneNumber);
    }

    /**
     * @dataProvider phoneNumberValidProvider
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
     * @dataProvider phoneNumberInvalidProvider
     */
    public function testFromStringInvalid(string $string): void
    {
        $this->expectException(\InvalidArgumentException::class);

        PhoneNumber::fromString($string);
    }

    /**
     * @dataProvider phoneNumberValidProvider
     */
    public function testSameValueAs(string $string): void
    {
        $phoneNumber1 = PhoneNumber::fromString($string);
        $phoneNumber2 = PhoneNumber::fromString($string);

        $this->assertTrue($phoneNumber1->sameValueAs($phoneNumber2));
    }

    public function testSameValueAsDiffClass(): void
    {
        $phoneNumber = PhoneNumber::fromString('403-543-3233');

        $this->assertFalse($phoneNumber->sameValueAs(FakeVo::create()));
    }
}
