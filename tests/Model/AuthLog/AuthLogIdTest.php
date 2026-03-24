<?php

declare(strict_types=1);

namespace App\Tests\Model\AuthLog;

use App\Model\AuthLog\AuthLogId;
use App\Tests\BaseTestCase;

class AuthLogIdTest extends BaseTestCase
{
    public function testFromString(): void
    {
        $uuid = $this->faker()->uuid();

        $authLogId = AuthLogId::fromString($uuid);

        $this->assertSame($uuid, $authLogId->toString());
    }

    public function testSameValueAs(): void
    {
        $uuid = $this->faker()->uuid();

        $authLogId1 = AuthLogId::fromString($uuid);
        $authLogId2 = AuthLogId::fromString($uuid);

        $this->assertTrue($authLogId1->sameValueAs($authLogId2));
    }

    public function testNotSameValueAs(): void
    {
        $faker = $this->faker();

        $authLogId1 = AuthLogId::fromString($faker->uuid());
        $authLogId2 = AuthLogId::fromString($faker->uuid());

        $this->assertFalse($authLogId1->sameValueAs($authLogId2));
    }
}
