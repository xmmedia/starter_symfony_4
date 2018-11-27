<?php

declare(strict_types=1);

namespace App\Tests\Model\Auth\Event;

use App\Model\Auth\AuthId;
use App\Model\Auth\Event\UserLoggedIn;
use App\Model\Email;
use App\Model\User\UserId;
use App\Tests\CanCreateEventFromArray;
use Faker;
use PHPUnit\Framework\TestCase;

class UserLoggedInTest extends TestCase
{
    use CanCreateEventFromArray;

    public function testOccur(): void
    {
        $faker = Faker\Factory::create();

        $authId = AuthId::generate();
        $userId = UserId::generate();
        $email = Email::fromString($faker->email);
        $userAgent = $faker->userAgent;
        $ipAddress = $faker->ipv4;

        $event = UserLoggedIn::now(
            $authId,
            $userId,
            $email,
            $userAgent,
            $ipAddress
        );

        $this->assertEquals($authId, $event->authId());
        $this->assertEquals($userId, $event->userId());
        $this->assertEquals($email, $event->email());
        $this->assertEquals($userAgent, $event->userAgent());
        $this->assertEquals($ipAddress, $event->ipAddress());
    }

    public function testFromArray(): void
    {
        $faker = Faker\Factory::create();

        $authId = AuthId::generate();
        $userId = UserId::generate();
        $email = Email::fromString($faker->email);
        $userAgent = $faker->userAgent;
        $ipAddress = $faker->ipv4;

        /** @var UserLoggedIn $event */
        $event = $this->createEventFromArray(
            UserLoggedIn::class,
            $authId->toString(),
            [
                'userId'    => $userId->toString(),
                'email'     => $email->toString(),
                'userAgent' => $userAgent,
                'ipAddress' => $ipAddress,
            ]
        );

        $this->assertInstanceOf(UserLoggedIn::class, $event);

        $this->assertEquals($authId, $event->authId());
        $this->assertEquals($userId, $event->userId());
        $this->assertEquals($email, $event->email());
        $this->assertEquals($userAgent, $event->userAgent());
        $this->assertEquals($ipAddress, $event->ipAddress());
    }
}
