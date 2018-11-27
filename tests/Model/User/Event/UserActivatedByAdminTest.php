<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Event;

use App\Model\User\Event\UserActivatedByAdmin;
use App\Model\User\UserId;
use App\Tests\CanCreateEventFromArray;
use PHPUnit\Framework\TestCase;

class UserActivatedByAdminTest extends TestCase
{
    use CanCreateEventFromArray;

    public function testOccur(): void
    {
        $userId = UserId::generate();

        $event = UserActivatedByAdmin::now($userId);

        $this->assertEquals($userId, $event->userId());
    }

    public function testFromArray(): void
    {
        $userId = UserId::generate();

        /** @var UserActivatedByAdmin $event */
        $event = $this->createEventFromArray(
            UserActivatedByAdmin::class,
            $userId->toString()
        );

        $this->assertInstanceOf(UserActivatedByAdmin::class, $event);

        $this->assertEquals($userId, $event->userId());
    }
}
