<?php

declare(strict_types=1);

namespace App\DataFixtures\Faker\Provider;

use Faker\Provider\Internet as FakerInternet;

/**
 * @property string $password
 *
 * @codeCoverageIgnore
 */
class InternetFakerProvider extends FakerInternet
{
    public function password($minLength = 12, $maxLength = 250): string
    {
        return parent::password($minLength, $maxLength);
    }
}
