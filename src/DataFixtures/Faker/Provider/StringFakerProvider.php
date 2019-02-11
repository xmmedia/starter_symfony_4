<?php

declare(strict_types=1);

namespace App\DataFixtures\Faker\Provider;

use Faker;

/**
 * @codeCoverageIgnore
 *
 * @property string cableGauge
 */
class StringFakerProvider extends Faker\Provider\Base
{
    public function string(int $length): string
    {
        $faker = Faker\Factory::create();

        return $faker->asciify(str_repeat('*', $length));
    }
}
