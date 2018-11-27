<?php

declare(strict_types=1);

namespace App\Tests\Model\Auth\Event;

use App\Model\Auth\AuthId;
use App\Model\Auth\Event\UserFailedToLogin;
use Faker;
use PHPUnit\Framework\TestCase;

class UserFailedToLoginTest extends TestCase
{
    public function testOccur(): void
    {
        $faker = Faker\Factory::create();

        $authId = AuthId::generate();
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

    public function testFromArray(): void
    {
        $faker = Faker\Factory::create();

        $authId = AuthId::generate();
        $email = $faker->email;
        $userAgent = $faker->userAgent;
        $ipAddress = $faker->ipv4;
        $message = $faker->asciify(str_repeat('*', 100));

        $event = UserFailedToLogin::fromArray([
            'message_name' => UserFailedToLogin::class,
            'uuid'         => $faker->uuid,
            'payload'      => [
                'email'            => $email,
                'userAgent'        => $userAgent,
                'ipAddress'        => $ipAddress,
                'exceptionMessage' => $message,
            ],
            'metadata' => [
                '_aggregate_id' => $authId->toString(),
            ],
            'created_at' => new \DateTimeImmutable(),
        ]);

        $this->assertInstanceOf(UserFailedToLogin::class, $event);

        $this->assertEquals($authId, $event->authId());
        $this->assertEquals($email, $event->email());
        $this->assertEquals($userAgent, $event->userAgent());
        $this->assertEquals($ipAddress, $event->ipAddress());
        $this->assertEquals($message, $event->exceptionMessage());
    }
}
