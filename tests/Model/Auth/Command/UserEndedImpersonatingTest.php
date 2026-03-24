<?php

declare(strict_types=1);

namespace App\Tests\Model\Auth\Command;

use App\Model\Auth\Command\UserEndedImpersonating;
use App\Tests\BaseTestCase;

class UserEndedImpersonatingTest extends BaseTestCase
{
    public function testNow(): void
    {
        $faker = $this->faker();

        $authId = $faker->authId();
        $adminUserId = $faker->userId();
        $impersonatedUserId = $faker->userId();
        $userAgent = $faker->userAgent();
        $ipAddress = $faker->ipv4();
        $route = $faker->slug();

        $command = UserEndedImpersonating::now(
            $authId,
            $adminUserId,
            $impersonatedUserId,
            $userAgent,
            $ipAddress,
            $route,
        );

        $this->assertSameValueAs($authId, $command->authId());
        $this->assertSameValueAs($adminUserId, $command->adminUserId());
        $this->assertSameValueAs($impersonatedUserId, $command->impersonatedUserId());
        $this->assertSame($userAgent, $command->userAgent());
        $this->assertSame($ipAddress, $command->ipAddress());
        $this->assertSame($route, $command->route());
    }

    public function testNowNullUserAgent(): void
    {
        $faker = $this->faker();

        $authId = $faker->authId();
        $adminUserId = $faker->userId();
        $impersonatedUserId = $faker->userId();
        $ipAddress = $faker->ipv4();
        $route = $faker->slug();

        $command = UserEndedImpersonating::now(
            $authId,
            $adminUserId,
            $impersonatedUserId,
            null,
            $ipAddress,
            $route,
        );

        $this->assertNull($command->userAgent());

        $this->assertSameValueAs($authId, $command->authId());
        $this->assertSameValueAs($adminUserId, $command->adminUserId());
        $this->assertSameValueAs($impersonatedUserId, $command->impersonatedUserId());
        $this->assertSame($ipAddress, $command->ipAddress());
        $this->assertSame($route, $command->route());
    }
}
