<?php

declare(strict_types=1);

namespace App\Tests\Model;

use App\Exception\InvalidAddress;
use App\Model\Address;
use App\Model\Country;
use App\Model\PostalCode;
use App\Model\Province;
use App\Tests\BaseTestCase;
use App\Tests\FakeVo;

class AddressTest extends BaseTestCase
{
    /**
     * @dataProvider addressStringProvider
     */
    public function testFromStrings(
        string $line1,
        ?string $line2,
        string $city,
        string $province,
        string $postalCode,
        string $country,
        string $expectedPostalCode
    ): void {
        $address = Address::fromStrings(
            $line1,
            $line2,
            $city,
            $province,
            $postalCode,
            $country
        );

        $this->assertEquals($line1, $address->line1());
        $this->assertEquals($line2, $address->line2());
        $this->assertEquals($city, $address->city());
        $this->assertEquals($province, $address->province()->toString());
        $this->assertEquals($expectedPostalCode, $address->postalCode()->toString());
        $this->assertEquals($country, $address->country()->toString());
    }

    public function addressStringProvider(): \Generator
    {
        $faker = $this->faker();

        $postalCode = $faker->postcode;
        yield [
            $faker->streetAddress,
            $faker->streetAddress,
            $faker->city,
            $faker->stateAbbr,
            $postalCode,
            $faker->randomElement(['CA', 'US']),
            PostalCode::format($postalCode),
        ];

        $postalCode = $faker->postcode;
        yield [
            $faker->streetAddress,
            null,
            $faker->city,
            $faker->stateAbbr,
            $postalCode,
            $faker->randomElement(['CA', 'US']),
            PostalCode::format($postalCode),
        ];

        $postalCode = $faker->postcode;
        yield [
            $faker->streetAddress,
            '', // empty string is changed to null
            $faker->city,
            $faker->stateAbbr,
            $postalCode,
            $faker->randomElement(['CA', 'US']),
            PostalCode::format($postalCode),
        ];
    }

    /**
     * @dataProvider addressArrayProvider
     */
    public function testFromArray(array $data, string $expectedPostalCode): void
    {
        $address = Address::fromArray($data);

        $this->assertEquals($data['line1'], $address->line1());
        $this->assertEquals($data['line2'], $address->line2());
        $this->assertEquals($data['city'], $address->city());
        $this->assertEquals($data['province'], $address->province()->toString());
        $this->assertEquals($expectedPostalCode, $address->postalCode()->toString());
        $this->assertEquals($data['country'], $address->country()->toString());

        $data['postalCode'] = $expectedPostalCode;
        $this->assertEquals($data, $address->toArray());
    }

    public function addressArrayProvider(): \Generator
    {
        $faker = $this->faker();

        $postalCode = $faker->postcode;
        yield [
            [
                'line1'      => $faker->streetAddress,
                'line2'      => $faker->streetAddress,
                'city'       => $faker->city,
                'province'   => $faker->stateAbbr,
                'postalCode' => $postalCode,
                'country'    => $faker->randomElement(['CA', 'US']),
            ],
            PostalCode::format($postalCode),
        ];

        $postalCode = $faker->postcode;
        yield [
            [
                'line1'      => $faker->streetAddress,
                'line2'      => null,
                'city'       => $faker->city,
                'province'   => $faker->stateAbbr,
                'postalCode' => $postalCode,
                'country'    => $faker->randomElement(['CA', 'US']),
            ],
            PostalCode::format($postalCode),
        ];

        $postalCode = $faker->postcode;
        yield [
            [
                'line1'      => $faker->streetAddress,
                'line2'      => '', // empty string is changed to null
                'city'       => $faker->city,
                'province'   => $faker->stateAbbr,
                'postalCode' => $postalCode,
                'country'    => $faker->randomElement(['CA', 'US']),
            ],
            PostalCode::format($postalCode),
        ];

        $postalCode = $faker->postcode;
        yield [
            [
                'line1'      => $faker->streetAddress,
                'line2'      => $faker->streetAddress,
                'city'       => $faker->city,
                'province'   => Province::fromString($faker->stateAbbr),
                'postalCode' => PostalCode::fromString($postalCode),
                'country'    => Country::fromString(
                    $faker->randomElement(['CA', 'US'])
                ),
            ],
            PostalCode::format($postalCode),
        ];
    }

    /**
     * Only tests exceptions through directly in Address VO, not Province, etc.
     *
     * @dataProvider addressInvalidProvider
     */
    public function testInvalid(
        string $line1,
        ?string $line2,
        string $city,
        string $province,
        string $postalCode,
        string $country,
        string $exception
    ): void {
        $this->expectException($exception);

        Address::fromStrings(
            $line1,
            $line2,
            $city,
            $province,
            $postalCode,
            $country
        );
    }

    public function addressInvalidProvider(): \Generator
    {
        $faker = $this->faker();

        yield [
            'a',
            '',
            'Calgary',
            'AB',
            'T3L 2H9',
            $faker->randomElement(['CA', 'US']),
            InvalidAddress::class,
        ];

        yield [
            $faker->string(101),
            '',
            'Calgary',
            'AB',
            'T3L 2H9',
            $faker->randomElement(['CA', 'US']),
            InvalidAddress::class,
        ];

        yield [
            $faker->streetAddress,
            'a',
            'Calgary',
            'AB',
            'T3L 2H9',
            $faker->randomElement(['CA', 'US']),
            InvalidAddress::class,
        ];

        yield [
            $faker->streetAddress,
            $faker->string(101),
            'Calgary',
            'AB',
            'T3L 2H9',
            $faker->randomElement(['CA', 'US']),
            InvalidAddress::class,
        ];

        yield [
            $faker->streetAddress,
            $faker->streetAddress,
            'a',
            'AB',
            'T3L 2H9',
            $faker->randomElement(['CA', 'US']),
            InvalidAddress::class,
        ];

        yield [
            $faker->streetAddress,
            $faker->streetAddress,
            $faker->string(101),
            'AB',
            'T3L 2H9',
            $faker->randomElement(['CA', 'US']),
            InvalidAddress::class,
        ];
    }

    /**
     * @dataProvider addressArrayProvider
     */
    public function testSameAs(array $data): void
    {
        $address1 = Address::fromArray($data);
        $address2 = Address::fromArray($data);

        $this->assertTrue($address1->sameValueAs($address2));
    }

    /**
     * @dataProvider addressArrayProvider
     */
    public function testSameAsFalse(array $data): void
    {
        $address1 = Address::fromArray($data);
        $address2 = Address::fromArray(['line1' => 'diff'] + $data);

        $this->assertFalse($address1->sameValueAs($address2));
    }

    public function testSameValueAsDiffClass(): void
    {
        $faker = $this->faker();

        $address = $faker->addressVo;

        $this->assertFalse($address->sameValueAs(FakeVo::create()));
    }
}
