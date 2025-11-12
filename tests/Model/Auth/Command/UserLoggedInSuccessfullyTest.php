<?php

declare(strict_types=1);

namespace App\Tests\Model\Auth\Command;

use App\Model\Auth\Command\UserLoggedInSuccessfully;
use App\Tests\BaseTestCase;

class UserLoggedInSuccessfullyTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $authId = $faker->authId();
        $userId = $faker->userId();
        $email = $faker->emailVo();
        $userAgent = $faker->userAgent();
        $ipAddress = $faker->ipv4();
        $route = $faker->slug();

        $command = UserLoggedInSuccessfully::now(
            $authId,
            $userId,
            $email,
            $userAgent,
            $ipAddress,
            $route,
        );

        $this->assertSameValueAs($authId, $command->authId());
        $this->assertSameValueAs($userId, $command->userId());
        $this->assertSameValueAs($email, $command->email());
        $this->assertSame($userAgent, $command->userAgent());
        $this->assertSame($ipAddress, $command->ipAddress());
        $this->assertSame($route, $command->route());
    }

    public function testNulls(): void
    {
        $faker = $this->faker();

        $authId = $faker->authId();
        $userId = $faker->userId();
        $email = $faker->emailVo();
        $ipAddress = $faker->ipv4();
        $route = $faker->slug();

        $command = UserLoggedInSuccessfully::now(
            $authId,
            $userId,
            $email,
            null,
            $ipAddress,
            $route,
        );

        $this->assertSameValueAs($authId, $command->authId());
        $this->assertSameValueAs($userId, $command->userId());
        $this->assertSameValueAs($email, $command->email());
        $this->assertNull($command->userAgent());
        $this->assertSame($ipAddress, $command->ipAddress());
        $this->assertSame($route, $command->route());
    }
}
