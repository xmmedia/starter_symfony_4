<?php

declare(strict_types=1);

namespace App\DataFixtures\Faker\Provider;

use App\Model\User\UserData;
use Faker;

/**
 * @codeCoverageIgnore
 */
class UserFakerProvider extends Faker\Provider\Base
{
    public function userData(): UserData
    {
        return UserData::fromArray([
            'phoneNumber' => $this->generator->boolean() ? $this->generator->phoneNumberVo()->national() : null,
        ]);
    }
}
