<?php

declare(strict_types=1);

namespace App\DataFixtures\Faker\Provider;

use App\Model\Email;
use Faker;

/**
 * @property Email $emailVo
 *
 * @codeCoverageIgnore
 */
class EmailFakerProvider extends Faker\Provider\Internet
{
    public function emailVo(): Email
    {
        return Email::fromString(static::email());
    }
}
