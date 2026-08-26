<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Event;

use App\Model\User\Event\PasswordUpgraded;
use App\Tests\BaseTestCase;
use Xm\SymfonyBundle\Tests\CanCreateEventFromArray;

class PasswordUpgradedTest extends BaseTestCase
{
    use CanCreateEventFromArray;

    public function testOccur(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId();

        $event = PasswordUpgraded::now($userId);

        $this->assertEquals($userId, $event->userId());
    }

    public function testFromArray(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId();

        $event = $this->createEventFromArray(
            PasswordUpgraded::class,
            $userId->toString(),
        );

        $this->assertInstanceOf(PasswordUpgraded::class, $event);

        $this->assertEquals($userId, $event->userId());
    }
}
