<?php

declare(strict_types=1);

namespace App\Tests\Model;

use App\Model\UuidId;
use App\Model\ValueObject;
use Faker;
use PHPUnit\Framework\TestCase;

class UuidIdTest extends TestCase
{
    public function testFromString(): void
    {
        $faker = Faker\Factory::create();

        $uuidString = $faker->uuid;

        $uuid = UuidIdId::fromString($uuidString);

        $this->assertEquals($uuidString, $uuid->toString());
        $this->assertEquals($uuidString, (string) $uuid);

        $this->assertEquals(
            \Ramsey\Uuid\Uuid::fromString($uuidString),
            $uuid->uuid()
        );
    }

    public function testFromUuid(): void
    {
        $faker = Faker\Factory::create();

        $uuidString = $faker->uuid;

        $uuid = UuidIdId::fromUuid(\Ramsey\Uuid\Uuid::fromString($uuidString));

        $this->assertEquals($uuidString, $uuid->toString());
        $this->assertEquals($uuidString, (string) $uuid);

        $this->assertEquals(
            \Ramsey\Uuid\Uuid::fromString($uuidString),
            $uuid->uuid()
        );
    }

    public function testSameValueAs(): void
    {
        $faker = Faker\Factory::create();

        $uuidString = $faker->uuid;

        $uuid1 = UuidIdId::fromString($uuidString);
        $uuid2 = UuidIdId::fromString($uuidString);

        $this->assertTrue($uuid1->sameValueAs($uuid2));
    }
}

class UuidIdId implements ValueObject
{
    use UuidId;
}
