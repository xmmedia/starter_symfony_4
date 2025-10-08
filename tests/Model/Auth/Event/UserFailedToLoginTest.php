<?php

declare(strict_types=1);

namespace App\Tests\Model\Auth\Event;

use App\Model\Auth\Event\UserFailedToLogin;
use App\Tests\BaseTestCase;
use Xm\SymfonyBundle\Tests\CanCreateEventFromArray;

class UserFailedToLoginTest extends BaseTestCase
{
    use CanCreateEventFromArray;

    public function testOccur(): void
    {
        $faker = $this->faker();

        $authId = $faker->authId();
        $email = $faker->email();
        $userId = $faker->userId();
        $userAgent = $faker->userAgent();
        $ipAddress = $faker->ipv4();
        $message = $faker->asciify(str_repeat('*', 100));
        $route = $faker->slug();

        $event = UserFailedToLogin::now(
            $authId,
            $email,
            $userId,
            $userAgent,
            $ipAddress,
            $message,
            $route,
        );

        $this->assertSameValueAs($authId, $event->authId());
        $this->assertSame($email, $event->email());
        $this->assertSameValueAs($userId, $event->userId());
        $this->assertSame($userAgent, $event->userAgent());
        $this->assertSame($ipAddress, $event->ipAddress());
        $this->assertSame($message, $event->exceptionMessage());
        $this->assertSame($route, $event->route());
    }

    public function testOccurNullValues(): void
    {
        $faker = $this->faker();

        $authId = $faker->authId();
        $ipAddress = $faker->ipv4();
        $message = $faker->asciify(str_repeat('*', 100));
        $route = $faker->slug();

        $event = UserFailedToLogin::now(
            $authId,
            null,
            null,
            null,
            $ipAddress,
            $message,
            $route,
        );

        $this->assertNull($event->email());
        $this->assertNull($event->userId());
        $this->assertNull($event->userAgent());
    }

    public function testFromArray(): void
    {
        $faker = $this->faker();

        $authId = $faker->authId();
        $email = $faker->email();
        $userId = $faker->userId();
        $userAgent = $faker->userAgent();
        $ipAddress = $faker->ipv4();
        $message = $faker->string(100);
        $route = $faker->slug();

        /** @var UserFailedToLogin $event */
        $event = $this->createEventFromArray(
            UserFailedToLogin::class,
            $authId->toString(),
            [
                'email'            => $email,
                'userId'           => $userId->toString(),
                'userAgent'        => $userAgent,
                'ipAddress'        => $ipAddress,
                'exceptionMessage' => $message,
                'route'            => $route,
            ],
        );

        $this->assertInstanceOf(UserFailedToLogin::class, $event);

        $this->assertSameValueAs($authId, $event->authId());
        $this->assertSame($email, $event->email());
        $this->assertSameValueAs($userId, $event->userId());
        $this->assertSame($userAgent, $event->userAgent());
        $this->assertSame($ipAddress, $event->ipAddress());
        $this->assertSame($message, $event->exceptionMessage());
        $this->assertSame($route, $event->route());
    }

    public function testFromArrayNullValues(): void
    {
        $faker = $this->faker();

        $authId = $faker->authId();
        $ipAddress = $faker->ipv4();
        $message = $faker->asciify(str_repeat('*', 100));
        $route = $faker->slug();

        /** @var UserFailedToLogin $event */
        $event = $this->createEventFromArray(
            UserFailedToLogin::class,
            $authId->toString(),
            [
                'email'            => null,
                'userId'           => null,
                'userAgent'        => null,
                'ipAddress'        => $ipAddress,
                'exceptionMessage' => $message,
                'route'            => $route,
            ],
        );

        $this->assertInstanceOf(UserFailedToLogin::class, $event);

        $this->assertNull($event->email());
        $this->assertNull($event->userId());
        $this->assertNull($event->userAgent());
    }
}
