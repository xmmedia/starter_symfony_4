<?php

declare(strict_types=1);

namespace App\DataFixtures\Faker\Provider;

use App\Model\Date;
use Faker;

/**
 * @property Date $dateVoBetween
 *
 * @codeCoverageIgnore
 */
class DateFakerProvider extends Faker\Provider\Base
{
    public function dateVoBetween(
        string $min = '-30 years',
        string $max = 'now'
    ): Date {
        $faker = Faker\Factory::create('en_CA');

        return Date::fromDateTime($faker->dateTimeBetween($min, $max));
    }
}
