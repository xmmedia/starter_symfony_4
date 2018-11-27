<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Event;

use App\Model\User\Event\UserVerifiedByAdmin;
use App\Model\User\UserId;
use App\Tests\CanCreateEvent;
use PHPUnit\Framework\TestCase;

class UserVerifiedByAdminTest extends TestCase
{
    use CanCreateEvent;

    public function testOccur(): void
    {
        $userId = UserId::generate();

        $event = UserVerifiedByAdmin::now($userId);

        $this->assertEquals($userId, $event->userId());
    }

    public function testFromArray(): void
    {
        $userId = UserId::generate();

        /** @var UserVerifiedByAdmin $event */
        $event = $this->createEvent(
            UserVerifiedByAdmin::class,
            $userId->toString()
        );

        $this->assertInstanceOf(UserVerifiedByAdmin::class, $event);

        $this->assertEquals($userId, $event->userId());
    }
}
