<?php

declare(strict_types=1);

namespace App\DataFixtures\Faker\Provider;

use App\Model\Gender;
use Faker;

/**
 * @property string $gender
 *
 * @codeCoverageIgnore
 */
class GenderFakerProvider extends Faker\Provider\Person
{
    public function gender(): string
    {
        $faker = Faker\Factory::create();

        return $faker->randomElement(Gender::getValues());
    }
}
