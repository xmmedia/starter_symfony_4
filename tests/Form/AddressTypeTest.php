<?php

declare(strict_types=1);

namespace App\Tests\Form;

use App\Form\AddressType;
use App\Model\Address;
use Faker;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Symfony\Component\Form\Test\Traits\ValidatorExtensionTrait;
use Symfony\Component\Form\Test\TypeTestCase;

class AddressTypeTest extends TypeTestCase
{
    use ValidatorExtensionTrait;
    use MockeryPHPUnitIntegration;

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
        $faker = Faker\Factory::create();

        yield [[
            'line1'      => $faker->address,
            'line2'      => $faker->address,
            'city'       => $faker->city,
            'province'   => $faker->stateAbbr,
            'postalCode' => $faker->postcode,
            'country'    => 'CA',
        ]];

        yield [[
            'line1'      => $faker->address,
            'line2'      => null,
            'city'       => $faker->city,
            'province'   => $faker->stateAbbr,
            'postalCode' => $faker->postcode,
            'country'    => 'CA',
        ]];

        yield [[
            'line1'      => $faker->address,
            'line2'      => '',
            'city'       => $faker->city,
            'province'   => $faker->stateAbbr,
            'postalCode' => $faker->postcode,
            'country'    => 'CA',
        ]];
    }
}
