<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Event;

use App\Model\User\Event\UserVerified;
use App\Tests\BaseTestCase;
use Xm\SymfonyBundle\Tests\CanCreateEventFromArray;

class UserVerifiedTest extends BaseTestCase
{
    use CanCreateEventFromArray;

    public function testOccur(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId();

        $event = UserVerified::now($userId);

        $this->assertEquals($userId, $event->userId());
    }

    public function testFromArray(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId();

        /** @var UserVerified $event */
        $event = $this->createEventFromArray(
            UserVerified::class,
            $userId->toString(),
        );

        $this->assertInstanceOf(UserVerified::class, $event);

        $this->assertEquals($userId, $event->userId());
    }
}
