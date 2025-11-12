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

        $authId = $faker->authId();
        $email = $faker->email();
        $userId = $faker->userId();
        $userAgent = $faker->userAgent();
        $ipAddress = $faker->ipv4();
        $message = $faker->asciify(str_repeat('*', 100));
        $route = $faker->slug();

        $command = UserLoginFailed::now(
            $authId,
            $email,
            $userId,
            $userAgent,
            $ipAddress,
            $message,
            $route,
        );

        $this->assertSameValueAs($authId, $command->authId());
        $this->assertSame($email, $command->email());
        $this->assertSameValueAs($userId, $command->userId());
        $this->assertSame($userAgent, $command->userAgent());
        $this->assertSame($ipAddress, $command->ipAddress());
        $this->assertSame($message, $command->exceptionMessage());
        $this->assertSame($route, $command->route());
    }

    public function testNowNulls(): void
    {
        $faker = $this->faker();

        $authId = $faker->authId();
        $ipAddress = $faker->ipv4();
        $route = $faker->slug();

        $command = UserLoginFailed::now(
            $authId,
            null,
            null,
            null,
            $ipAddress,
            null,
            $route,
        );

        $this->assertNull($command->email());
        $this->assertNull($command->userId());
        $this->assertNull($command->userAgent());
        $this->assertNull($command->exceptionMessage());

        $this->assertSameValueAs($authId, $command->authId());
        $this->assertSame($ipAddress, $command->ipAddress());
        $this->assertSame($route, $command->route());
    }
}
