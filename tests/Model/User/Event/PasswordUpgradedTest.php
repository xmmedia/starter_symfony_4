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
        $password = $faker->password();

        $event = PasswordUpgraded::now($userId, $password);

        $this->assertEquals($userId, $event->userId());
        $this->assertEquals($password, $event->hashedPassword());
    }

    public function testFromArray(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId();
        $password = $faker->password();

        /** @var PasswordUpgraded $event */
        $event = $this->createEventFromArray(
            PasswordUpgraded::class,
            $userId->toString(),
            [
                'hashedPassword' => $password,
            ],
        );

        $this->assertInstanceOf(PasswordUpgraded::class, $event);

        $this->assertEquals($userId, $event->userId());
        $this->assertEquals($password, $event->hashedPassword());
    }
}
