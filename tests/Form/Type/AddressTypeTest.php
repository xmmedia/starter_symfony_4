<?php

declare(strict_types=1);

namespace App\Tests\Form\Type;

use App\Form\Type\AddressType;
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

        $this->assertFormIsValid($form);
        $this->hasAllFormFields($form, $formData);
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

    /**
     * @dataProvider addressInvalidProvider
     */
    public function testInvalid(array $formData, ?string $atPath): void
    {
        $form = $this->factory->create(AddressType::class)
            ->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertFalse($form->isValid());

        $this->hasAllFormFields($form, $formData);

        // make sure only 1 error on the form
        $this->assertCount(
            1,
            $form->getErrors(true, true),
            'The following fields are invalid: '.implode(', ', $this->getFormErrors($form))
        );
        if (null !== $atPath) {
            // make sure error at the right location
            $this->assertCount(
                1,
                $form->get($atPath)->getErrors(true, true),
                sprintf('There is more than 1 error at: %s.', $atPath)
            );
        }
    }

    public function addressInvalidProvider(): \Generator
    {
        $faker = $this->faker();

        // line 1
        foreach (['12', $faker->string(101)] as $line1) {
            yield [
                [
                    'line1'      => $line1,
                    'line2'      => $faker->streetAddress,
                    'city'       => $faker->city,
                    'province'   => $faker->stateAbbr,
                    'postalCode' => $faker->postcode,
                    'country'    => 'CA',
                ],
                'line1',
            ];
        }
        // line 2
        foreach (['12', $faker->string(101)] as $line2) {
            yield [
                [
                    'line1'      => $faker->streetAddress,
                    'line2'      => $line2,
                    'city'       => $faker->city,
                    'province'   => $faker->stateAbbr,
                    'postalCode' => $faker->postcode,
                    'country'    => 'CA',
                ],
                'line2',
            ];
        }
        // city
        foreach (['1', $faker->string(101)] as $city) {
            yield [
                [
                    'line1'      => $faker->streetAddress,
                    'line2'      => null,
                    'city'       => $city,
                    'province'   => $faker->stateAbbr,
                    'postalCode' => $faker->postcode,
                    'country'    => 'CA',
                ],
                'city',
            ];
        }
        // province
        foreach (['A', 'AC'] as $province) {
            yield [
                [
                    'line1'      => $faker->streetAddress,
                    'line2'      => null,
                    'city'       => $faker->city,
                    'province'   => $province,
                    'postalCode' => $faker->postcode,
                    'country'    => 'CA',
                ],
                'province',
            ];
        }
        // postal code
        foreach (['1', 'T'] as $postalCode) {
            yield [
                [
                    'line1'      => $faker->streetAddress,
                    'line2'      => null,
                    'city'       => $faker->city,
                    'province'   => $faker->stateAbbr,
                    'postalCode' => $postalCode,
                    'country'    => 'CA',
                ],
                'postalCode',
            ];
        }
    }
}
