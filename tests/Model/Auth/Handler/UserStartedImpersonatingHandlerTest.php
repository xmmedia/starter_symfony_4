<?php

declare(strict_types=1);

namespace App\Tests\Model\Auth\Handler;

use App\Model\Auth\Auth;
use App\Model\Auth\AuthList;
use App\Model\Auth\Command\UserStartedImpersonating;
use App\Model\Auth\Handler\UserStartedImpersonatingHandler;
use App\Tests\BaseTestCase;
use Xm\SymfonyBundle\Model\Email;

class UserStartedImpersonatingHandlerTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $authId = $faker->authId();
        $adminUserId = $faker->userId();
        $impersonatedUserId = $faker->userId();
        $impersonatedEmail = Email::fromString($faker->email());
        $userAgent = $faker->userAgent();
        $ipAddress = $faker->ipv4();
        $route = $faker->slug();

        $command = UserStartedImpersonating::now(
            $authId,
            $adminUserId,
            $impersonatedUserId,
            $impersonatedEmail,
            $userAgent,
            $ipAddress,
            $route,
        );

        $repo = \Mockery::mock(AuthList::class);
        $repo->shouldReceive('save')
            ->once()
            ->with(\Mockery::type(Auth::class));

        (new UserStartedImpersonatingHandler($repo))($command);
    }

    public function testNullUserAgent(): void
    {
        $faker = $this->faker();

        $authId = $faker->authId();
        $adminUserId = $faker->userId();
        $impersonatedUserId = $faker->userId();
        $impersonatedEmail = Email::fromString($faker->email());
        $ipAddress = $faker->ipv4();
        $route = $faker->slug();

        $command = UserStartedImpersonating::now(
            $authId,
            $adminUserId,
            $impersonatedUserId,
            $impersonatedEmail,
            null,
            $ipAddress,
            $route,
        );

        $repo = \Mockery::mock(AuthList::class);
        $repo->shouldReceive('save')
            ->once()
            ->with(\Mockery::type(Auth::class));

        (new UserStartedImpersonatingHandler($repo))($command);
    }
}
