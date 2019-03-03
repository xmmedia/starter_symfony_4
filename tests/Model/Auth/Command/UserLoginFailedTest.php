<?php

declare(strict_types=1);

namespace App\Tests\Model\Auth\Command;

use App\Model\Auth\Command\UserLoginFailed;
use App\Tests\BaseTestCase;

class UserLoginFailedTest extends BaseTestCase
{
    public function testNow(): void
    {
        $faker = $this->faker();

        $authId = $faker->authId;
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

    public function testNowNullValues(): void
    {
        $faker = $this->faker();

        $command = UserLoginFailed::now(
            $faker->authId,
            null,
            null,
            $faker->ipv4,
            null
        );

        $this->assertNull($command->email());
        $this->assertNull($command->userAgent());
        $this->assertNull($command->exceptionMessage());
    }
}
