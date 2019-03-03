<?php

declare(strict_types=1);

namespace App\Tests\Form\DataTransformer;

use App\Form\DataTransformer\AddressTransformer;
use App\Model\Address;
use Faker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Exception\TransformationFailedException;

class AddressTransformerTest extends TestCase
{
    /**
     * @dataProvider transformProvider
     */
    public function testTransform($value, $expected): void
    {
        $result = (new AddressTransformer())->transform($value);

        $this->assertEquals($expected, $result);
    }

    public function transformProvider(): \Generator
    {
        $faker = Faker\Factory::create();

        yield [null, null];

        $address = [
            'line1'      => $faker->address,
            'line2'      => $faker->address,
            'city'       => $faker->city,
            'province'   => $faker->stateAbbr,
            'postalCode' => $faker->postcode,
            'country'    => 'CA',
        ];

        yield [Address::fromArray($address), $address];
    }

    /**
     * @dataProvider reverseTransformProvider
     */
    public function testReverseTransform($value, $expected): void
    {
        $result = (new AddressTransformer())->reverseTransform($value);

        $this->assertEquals($expected, $result);
    }

    public function reverseTransformProvider(): \Generator
    {
        $faker = Faker\Factory::create();

        yield [null, null];

        $address = [
            'line1'      => $faker->address,
            'line2'      => $faker->address,
            'city'       => $faker->city,
            'province'   => $faker->stateAbbr,
            'postalCode' => $faker->postcode,
            'country'    => 'CA',
        ];

        yield [$address, Address::fromArray($address)];
    }

    public function testTransformationException(): void
    {
        $this->expectException(TransformationFailedException::class);

        (new AddressTransformer())->reverseTransform('asdf');
    }
}
