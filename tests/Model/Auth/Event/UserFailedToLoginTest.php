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

        $authId = $faker->authId;
        $email = $faker->email;
        $userAgent = $faker->userAgent;
        $ipAddress = $faker->ipv4;
        $message = $faker->asciify(str_repeat('*', 100));

        $event = UserFailedToLogin::now(
            $authId,
            $email,
            $userAgent,
            $ipAddress,
            $message
        );

        $this->assertEquals($authId, $event->authId());
        $this->assertEquals($email, $event->email());
        $this->assertEquals($userAgent, $event->userAgent());
        $this->assertEquals($ipAddress, $event->ipAddress());
        $this->assertEquals($message, $event->exceptionMessage());
    }

    public function testOccurNullValues(): void
    {
        $faker = $this->faker();

        $authId = $faker->authId;
        $ipAddress = $faker->ipv4;
        $message = $faker->asciify(str_repeat('*', 100));

        $event = UserFailedToLogin::now(
            $authId,
            null,
            null,
            $ipAddress,
            $message
        );

        $this->assertNull($event->email());
        $this->assertNull($event->userAgent());
    }

    public function testFromArray(): void
    {
        $faker = $this->faker();

        $authId = $faker->authId;
        $email = $faker->email;
        $userAgent = $faker->userAgent;
        $ipAddress = $faker->ipv4;
        $message = $faker->string(100);

        /** @var UserFailedToLogin $event */
        $event = $this->createEventFromArray(
            UserFailedToLogin::class,
            $authId->toString(),
            [
                'email'            => $email,
                'userAgent'        => $userAgent,
                'ipAddress'        => $ipAddress,
                'exceptionMessage' => $message,
            ]
        );

        $this->assertInstanceOf(UserFailedToLogin::class, $event);

        $this->assertEquals($authId, $event->authId());
        $this->assertEquals($email, $event->email());
        $this->assertEquals($userAgent, $event->userAgent());
        $this->assertEquals($ipAddress, $event->ipAddress());
        $this->assertEquals($message, $event->exceptionMessage());
    }

    public function testFromArrayNullValues(): void
    {
        $faker = $this->faker();

        $authId = $faker->authId;
        $ipAddress = $faker->ipv4;
        $message = $faker->asciify(str_repeat('*', 100));

        /** @var UserFailedToLogin $event */
        $event = $this->createEventFromArray(
            UserFailedToLogin::class,
            $authId->toString(),
            [
                'email'            => null,
                'userAgent'        => null,
                'ipAddress'        => $ipAddress,
                'exceptionMessage' => $message,
            ]
        );

        $this->assertInstanceOf(UserFailedToLogin::class, $event);

        $this->assertNull($event->email());
        $this->assertNull($event->userAgent());
    }
}
