<?php

declare(strict_types=1);

namespace App\Tests\Model\Auth\Command;

use App\Model\Auth\AuthId;
use App\Model\Auth\Command\UserLoginFailed;
use Faker;
use PHPUnit\Framework\TestCase;

class UserLoginFailedTest extends TestCase
{
    public function testNow(): void
    {
        $faker = Faker\Factory::create();

        $authId = AuthId::generate();
        $email = $faker->email;
        $userAgent = $faker->userAgent;
        $ipAddress = $faker->ipv4;
        $message = $faker->asciify(str_repeat('*', 100));

        $command = UserLoginFailed::now(
            $authId,
            $email,
            $userAgent,
            $ipAddress,
            $message
        );

        $this->assertTrue($authId->sameValueAs($command->authId()));
        $this->assertEquals($email, $command->email());
        $this->assertEquals($userAgent, $command->userAgent());
        $this->assertEquals($ipAddress, $command->ipAddress());
        $this->assertEquals($message, $command->exceptionMessage());
    }

    public function testNowMessageNull(): void
    {
        $faker = Faker\Factory::create();

        $command = UserLoginFailed::now(
            AuthId::generate(),
            $faker->email,
            $faker->userAgent,
            $faker->ipv4,
            null
        );

        $this->assertNull($command->exceptionMessage());
    }
}
