<?php

declare(strict_types=1);

namespace App\DataFixtures\Faker\Provider;

use App\Model\Date;
use Faker;
use Faker\Provider\en_CA\Address as FakerAddress;

/**
 * @property Date $dateVoBetween
 *
 * @codeCoverageIgnore
 */
class DateFakerProvider extends FakerAddress
{
    public function dateVoBetween(
        string $min = '-30 years',
        string $max = 'now'
    ): Date {
        $faker = Faker\Factory::create('en_CA');

        return Date::fromDateTime($faker->dateTimeBetween($min, $max));
    }
}
