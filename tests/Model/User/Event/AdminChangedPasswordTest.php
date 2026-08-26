<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Event;

use App\Model\User\Event\AdminChangedPassword;
use App\Tests\BaseTestCase;
use Xm\SymfonyBundle\Tests\CanCreateEventFromArray;

class AdminChangedPasswordTest extends BaseTestCase
{
    use CanCreateEventFromArray;

    public function testOccur(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId();

        $event = AdminChangedPassword::now($userId);

        $this->assertEquals($userId, $event->userId());
    }

    public function testFromArray(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId();

        $event = $this->createEventFromArray(
            AdminChangedPassword::class,
            $userId->toString(),
        );

        $this->assertInstanceOf(AdminChangedPassword::class, $event);

        $this->assertEquals($userId, $event->userId());
    }
}
