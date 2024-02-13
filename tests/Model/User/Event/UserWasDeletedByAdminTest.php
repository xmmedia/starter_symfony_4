<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Event;

use App\Model\User\Event\UserWasDeletedByAdmin;
use App\Tests\BaseTestCase;
use Xm\SymfonyBundle\Tests\CanCreateEventFromArray;

class UserWasDeletedByAdminTest extends BaseTestCase
{
    use CanCreateEventFromArray;

    public function testOccur(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId();

        $event = UserWasDeletedByAdmin::now($userId);

        $this->assertEquals($userId, $event->userId());
    }

    public function testFromArray(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId();

        /** @var UserWasDeletedByAdmin $event */
        $event = $this->createEventFromArray(
            UserWasDeletedByAdmin::class,
            $userId->toString(),
        );

        $this->assertInstanceOf(UserWasDeletedByAdmin::class, $event);

        $this->assertEquals($userId, $event->userId());
    }
}
