<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Event;

use App\Model\User\Event\UserDeactivatedByAdmin;
use App\Model\User\UserId;
use App\Tests\CanCreateEvent;
use PHPUnit\Framework\TestCase;

class UserDeactivatedByAdminTest extends TestCase
{
    use CanCreateEvent;

    public function testOccur(): void
    {
        $userId = UserId::generate();

        $event = UserDeactivatedByAdmin::now($userId);

        $this->assertEquals($userId, $event->userId());
    }

    public function testFromArray(): void
    {
        $userId = UserId::generate();

        /** @var UserDeactivatedByAdmin $event */
        $event = $this->createEvent(
            UserDeactivatedByAdmin::class,
            $userId->toString()
        );

        $this->assertInstanceOf(UserDeactivatedByAdmin::class, $event);

        $this->assertEquals($userId, $event->userId());
    }
}
