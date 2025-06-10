<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Event;

use App\Model\User\Event\UserActivated;
use App\Tests\BaseTestCase;
use Xm\SymfonyBundle\Tests\CanCreateEventFromArray;

class UserActivatedTest extends BaseTestCase
{
    use CanCreateEventFromArray;

    public function testOccur(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId();

        $event = UserActivated::now($userId);

        $this->assertEquals($userId, $event->userId());
    }

    public function testFromArray(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId();

        /** @var UserActivated $event */
        $event = $this->createEventFromArray(
            UserActivated::class,
            $userId->toString(),
        );

        $this->assertInstanceOf(UserActivated::class, $event);

        $this->assertEquals($userId, $event->userId());
    }
}
