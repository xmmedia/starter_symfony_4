<?php

declare(strict_types=1);

namespace App\Tests\Model\Auth\Handler;

use App\Model\Auth\Auth;
use App\Model\Auth\AuthList;
use App\Model\Auth\Command\UserEndedImpersonating;
use App\Model\Auth\Handler\UserEndedImpersonatingHandler;
use App\Tests\BaseTestCase;

class UserEndedImpersonatingHandlerTest extends BaseTestCase
{
    public function test(): void
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

        $repo = \Mockery::mock(AuthList::class);
        $repo->shouldReceive('save')
            ->once()
            ->with(\Mockery::type(Auth::class));

        (new UserEndedImpersonatingHandler($repo))($command);
    }

    public function testNullUserAgent(): void
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

        $repo = \Mockery::mock(AuthList::class);
        $repo->shouldReceive('save')
            ->once()
            ->with(\Mockery::type(Auth::class));

        (new UserEndedImpersonatingHandler($repo))($command);
    }
}
