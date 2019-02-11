<?php

declare(strict_types=1);

namespace App\DataFixtures\Faker\Provider;

use Faker;

/**
 * @codeCoverageIgnore
 */
class NameFakerProvider extends Faker\Provider\Person
{
    public function name($gender = null): string
    {
        return substr(parent::name($gender), 0, 25);
    }
}
