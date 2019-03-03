<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Event;

use App\Model\User\Event\UserLoggedIn;
use App\Tests\BaseTestCase;
use App\Tests\CanCreateEventFromArray;

class UserLoggedInTest extends BaseTestCase
{
    use CanCreateEventFromArray;

    public function testOccur(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;

        $event = UserLoggedIn::now($userId);

        $this->assertEquals($userId, $event->userId());
    }

    public function testFromArray(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;

        /** @var UserLoggedIn $event */
        $event = $this->createEventFromArray(
            UserLoggedIn::class,
            $userId->toString()
        );

        $this->assertInstanceOf(UserLoggedIn::class, $event);

        $this->assertEquals($userId, $event->userId());
    }
}
