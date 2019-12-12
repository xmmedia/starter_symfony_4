<?php

declare(strict_types=1);

namespace App\Tests\Model\User;

use App\Model\User\UserId;
use App\Tests\BaseTestCase;

class UserIdTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $uuid = $faker->uuid;

        $userId = UserId::fromString($uuid);

        $this->assertEquals($uuid, $userId->toString());
    }
}
