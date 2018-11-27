<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Event;

use App\Model\User\Event\UserLoggedIn;
use App\Model\User\UserId;
use App\Tests\CanCreateEventFromArray;
use PHPUnit\Framework\TestCase;

class UserLoggedInTest extends TestCase
{
    use CanCreateEventFromArray;

    public function testOccur(): void
    {
        $userId = UserId::generate();

        $event = UserLoggedIn::now($userId);

        $this->assertEquals($userId, $event->userId());
    }

    public function testFromArray(): void
    {
        $userId = UserId::generate();

        /** @var UserLoggedIn $event */
        $event = $this->createEventFromArray(
            UserLoggedIn::class,
            $userId->toString()
        );

        $this->assertInstanceOf(UserLoggedIn::class, $event);

        $this->assertEquals($userId, $event->userId());
    }
}
