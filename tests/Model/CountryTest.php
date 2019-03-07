<?php

declare(strict_types=1);

namespace App\Tests\Model;

use App\Exception\InvalidCountry;
use App\Model\Country;
use PHPUnit\Framework\TestCase;

class CountryTest extends TestCase
{
    /**
     * @dataProvider countryProvider
     */
    public function testFromString(string $code, string $expected, string $name): void
    {
        $country = Country::fromString($code);

        $this->assertEquals($expected, $country->abbreviation());
        $this->assertEquals($name, $country->name());
        $this->assertEquals($expected, $country->toString());
        $this->assertEquals($expected, (string) $country);
    }

    public function countryProvider(): \Generator
    {
        yield ['CA', 'CA', 'Canada'];
        yield ['ca', 'CA', 'Canada'];

        yield ['US', 'US', 'United States'];
        yield ['us', 'US', 'United States'];
    }

    /**
     * @dataProvider invalidProvider
     */
    public function testInvalid(?string $value): void
    {
        $this->expectException(InvalidCountry::class);

        Country::fromString($value);
    }

    public function invalidProvider(): \Generator
    {
        yield ['UK'];
        yield [''];
        yield ['A'];
    }

    public function testSameValueAs(): void
    {
        $country1 = Country::fromString('CA');
        $country2 = Country::fromString('CA');

        $this->assertTrue($country1->sameValueAs($country2));
    }

    public function testSameValueAsFalse(): void
    {
        $country1 = Country::fromString('CA');
        $country2 = Country::fromString('US');

        $this->assertFalse($country1->sameValueAs($country2));
    }

    public function testSameValueAsDiffObject(): void
    {
        $country = Country::fromString('CA');
        $email = \App\Model\Email::fromString('email@example.com');

        $this->assertFalse($country->sameValueAs($email));
    }
}
