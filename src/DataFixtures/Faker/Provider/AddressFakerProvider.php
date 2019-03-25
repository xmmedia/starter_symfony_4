<?php

declare(strict_types=1);

namespace App\DataFixtures\Faker\Provider;

use App\Model\Address;
use Faker;

/**
 * @property Address $addressVo
 * @property array   $addressArray
 *
 * @codeCoverageIgnore
 */
class AddressFakerProvider extends Faker\Provider\Base
{
    public function addressVo(): Address
    {
        return Address::fromArray($this->addressArray());
    }

    public function addressArray(): array
    {
        $faker = Faker\Factory::create();

        return [
            'line1'      => $faker->streetAddress,
            'line2'      => $faker->streetAddress,
            'city'       => $faker->city,
            'province'   => $faker->stateAbbr,
            'postalCode' => $faker->postcode,
            'country'    => 'CA',
        ];
    }
}
