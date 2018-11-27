<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Event;

use App\Model\User\Event\UserVerified;
use App\Model\User\UserId;
use App\Tests\CanCreateEventFromArray;
use PHPUnit\Framework\TestCase;

class UserVerifiedTest extends TestCase
{
    use CanCreateEventFromArray;

    public function testOccur(): void
    {
        $userId = UserId::generate();

        $event = UserVerified::now($userId);

        $this->assertEquals($userId, $event->userId());
    }

    public function testFromArray(): void
    {
        $userId = UserId::generate();

        /** @var UserVerified $event */
        $event = $this->createEventFromArray(
            UserVerified::class,
            $userId->toString()
        );

        $this->assertInstanceOf(UserVerified::class, $event);

        $this->assertEquals($userId, $event->userId());
    }
}
