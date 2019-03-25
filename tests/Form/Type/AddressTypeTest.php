<?php

declare(strict_types=1);

namespace App\Tests\Form\Type;

use App\Form\Type\AddressType;
use App\Model\Address;
use App\Tests\TypeTestCase;

class AddressTypeTest extends TypeTestCase
{
    /**
     * @dataProvider addressProvider
     */
    public function test(array $formData): void
    {
        $form = $this->factory->create(AddressType::class)
            ->submit($formData);

        $this->assertTrue($form->isValid());

        $this->assertInstanceOf(Address::class, $form->getData());
    }

    public function addressProvider(): \Generator
    {
        $faker = $this->faker();

        yield [[
            'line1'      => $faker->streetAddress,
            'line2'      => $faker->streetAddress,
            'city'       => $faker->city,
            'province'   => $faker->stateAbbr,
            'postalCode' => $faker->postcode,
            'country'    => 'CA',
        ]];

        yield [[
            'line1'      => $faker->streetAddress,
            'line2'      => null,
            'city'       => $faker->city,
            'province'   => $faker->stateAbbr,
            'postalCode' => $faker->postcode,
            'country'    => 'CA',
        ]];

        yield [[
            'line1'      => $faker->streetAddress,
            'line2'      => '',
            'city'       => $faker->city,
            'province'   => $faker->stateAbbr,
            'postalCode' => $faker->postcode,
            'country'    => 'CA',
        ]];
    }
}
