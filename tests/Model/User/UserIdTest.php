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

        $uuid = $faker->uuid();

        $userId = UserId::fromString($uuid);

        $this->assertEquals($uuid, $userId->toString());
    }

    public function testSameValueAs(): void
    {
        $uuid = $this->faker()->uuid();

        $authLogId1 = UserId::fromString($uuid);
        $authLogId2 = UserId::fromString($uuid);

        $this->assertTrue($authLogId1->sameValueAs($authLogId2));
    }

    public function testNotSameValueAs(): void
    {
        $faker = $this->faker();

        $authLogId1 = UserId::fromString($faker->uuid());
        $authLogId2 = UserId::fromString($faker->uuid());

        $this->assertFalse($authLogId1->sameValueAs($authLogId2));
    }
}
