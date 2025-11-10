<?php

declare(strict_types=1);

namespace App\Tests\Model\Auth\Event;

use App\Model\Auth\Event\UserLoggedIn;
use App\Tests\BaseTestCase;
use Xm\SymfonyBundle\Tests\CanCreateEventFromArray;

class UserLoggedInTest extends BaseTestCase
{
    use CanCreateEventFromArray;

    public function testOccur(): void
    {
        $faker = $this->faker();

        $authId = $faker->authId();
        $userId = $faker->userId();
        $email = $faker->emailVo();
        $userAgent = $faker->userAgent();
        $ipAddress = $faker->ipv4();
        $route = $faker->slug();

        $event = UserLoggedIn::now(
            $authId,
            $userId,
            $email,
            $userAgent,
            $ipAddress,
            $route,
        );

        $this->assertSameValueAs($authId, $event->authId());
        $this->assertSameValueAs($userId, $event->userId());
        $this->assertSameValueAs($email, $event->email());
        $this->assertSame($userAgent, $event->userAgent());
        $this->assertSame($ipAddress, $event->ipAddress());
        $this->assertSame($route, $event->route());
    }

    public function testOccurNullValues(): void
    {
        $faker = $this->faker();

        $authId = $faker->authId();
        $userId = $faker->userId();
        $email = $faker->emailVo();
        $ipAddress = $faker->ipv4();
        $route = $faker->slug();

        $event = UserLoggedIn::now(
            $authId,
            $userId,
            $email,
            null,
            $ipAddress,
            $route,
        );

        $this->assertSameValueAs($authId, $event->authId());
        $this->assertSameValueAs($userId, $event->userId());
        $this->assertSameValueAs($email, $event->email());
        $this->assertNull($event->userAgent());
        $this->assertSame($ipAddress, $event->ipAddress());
        $this->assertSame($route, $event->route());
    }

    public function testFromArray(): void
    {
        $faker = $this->faker();

        $authId = $faker->authId();
        $userId = $faker->userId();
        $email = $faker->emailVo();
        $userAgent = $faker->userAgent();
        $ipAddress = $faker->ipv4();
        $route = $faker->slug();

        /** @var UserLoggedIn $event */
        $event = $this->createEventFromArray(
            UserLoggedIn::class,
            $authId->toString(),
            [
                'userId'    => $userId->toString(),
                'email'     => $email->toString(),
                'userAgent' => $userAgent,
                'ipAddress' => $ipAddress,
                'route'     => $route,
            ],
        );

        $this->assertInstanceOf(UserLoggedIn::class, $event);

        $this->assertSameValueAs($authId, $event->authId());
        $this->assertSameValueAs($userId, $event->userId());
        $this->assertSameValueAs($email, $event->email());
        $this->assertSame($userAgent, $event->userAgent());
        $this->assertSame($ipAddress, $event->ipAddress());
        $this->assertSame($route, $event->route());
    }

    public function testFromArrayNullValues(): void
    {
        $faker = $this->faker();

        $authId = $faker->authId();
        $userId = $faker->userId();
        $email = $faker->emailVo();
        $ipAddress = $faker->ipv4();
        $route = $faker->slug();

        /** @var UserLoggedIn $event */
        $event = $this->createEventFromArray(
            UserLoggedIn::class,
            $authId->toString(),
            [
                'userId'    => $userId->toString(),
                'email'     => $email->toString(),
                'userAgent' => null,
                'ipAddress' => $ipAddress,
                'route'     => $route,
            ],
        );

        $this->assertInstanceOf(UserLoggedIn::class, $event);

        $this->assertSameValueAs($authId, $event->authId());
        $this->assertSameValueAs($userId, $event->userId());
        $this->assertSameValueAs($email, $event->email());
        $this->assertNull($event->userAgent());
        $this->assertSame($ipAddress, $event->ipAddress());
        $this->assertSame($route, $event->route());
    }
}
