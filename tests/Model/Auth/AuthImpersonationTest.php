<?php

declare(strict_types=1);

namespace App\Tests\Model\Auth;

use App\Model\Auth\Auth;
use App\Model\Auth\Event\UserEndedImpersonating;
use App\Model\Auth\Event\UserStartedImpersonating;
use App\Tests\BaseTestCase;

class AuthImpersonationTest extends BaseTestCase
{
    public function testStartedImpersonating(): void
    {
        $faker = $this->faker();

        $authId = $faker->authId();
        $adminUserId = $faker->userId();
        $impersonatedUserId = $faker->userId();
        $impersonatedEmail = $faker->emailVo();
        $userAgent = $faker->userAgent();
        $ipAddress = $faker->ipv4();
        $route = $faker->slug();

        $auth = Auth::startedImpersonating(
            $authId,
            $adminUserId,
            $impersonatedUserId,
            $impersonatedEmail,
            $userAgent,
            $ipAddress,
            $route,
        );

        $this->assertInstanceOf(Auth::class, $auth);

        $events = $this->popRecordedEvent($auth);

        $this->assertRecordedEvent(UserStartedImpersonating::class, [
            'adminUserId'        => $adminUserId->toString(),
            'impersonatedUserId' => $impersonatedUserId->toString(),
            'impersonatedEmail'  => $impersonatedEmail->toString(),
            'userAgent'          => $userAgent,
            'ipAddress'          => $ipAddress,
            'route'              => $route,
        ], $events);

        $this->assertCount(1, $events);

        $this->assertEquals($authId, $auth->authId());
    }

    public function testStartedImpersonatingNullUserAgent(): void
    {
        $faker = $this->faker();

        $authId = $faker->authId();
        $adminUserId = $faker->userId();
        $impersonatedUserId = $faker->userId();
        $impersonatedEmail = $faker->emailVo();
        $ipAddress = $faker->ipv4();
        $route = $faker->slug();

        $auth = Auth::startedImpersonating(
            $authId,
            $adminUserId,
            $impersonatedUserId,
            $impersonatedEmail,
            null,
            $ipAddress,
            $route,
        );

        $this->assertInstanceOf(Auth::class, $auth);

        $events = $this->popRecordedEvent($auth);

        $this->assertRecordedEvent(UserStartedImpersonating::class, [
            'adminUserId'        => $adminUserId->toString(),
            'impersonatedUserId' => $impersonatedUserId->toString(),
            'impersonatedEmail'  => $impersonatedEmail->toString(),
            'userAgent'          => null,
            'ipAddress'          => $ipAddress,
            'route'              => $route,
        ], $events);

        $this->assertCount(1, $events);

        $this->assertEquals($authId, $auth->authId());
    }

    public function testEndedImpersonating(): void
    {
        $faker = $this->faker();

        $authId = $faker->authId();
        $adminUserId = $faker->userId();
        $userAgent = $faker->userAgent();
        $ipAddress = $faker->ipv4();
        $route = $faker->slug();

        $auth = Auth::endedImpersonating(
            $authId,
            $adminUserId,
            $userAgent,
            $ipAddress,
            $route,
        );

        $this->assertInstanceOf(Auth::class, $auth);

        $events = $this->popRecordedEvent($auth);

        $this->assertRecordedEvent(UserEndedImpersonating::class, [
            'adminUserId' => $adminUserId->toString(),
            'userAgent'   => $userAgent,
            'ipAddress'   => $ipAddress,
            'route'       => $route,
        ], $events);

        $this->assertCount(1, $events);

        $this->assertEquals($authId, $auth->authId());
    }

    public function testEndedImpersonatingNullUserAgent(): void
    {
        $faker = $this->faker();

        $authId = $faker->authId();
        $adminUserId = $faker->userId();
        $ipAddress = $faker->ipv4();
        $route = $faker->slug();

        $auth = Auth::endedImpersonating(
            $authId,
            $adminUserId,
            null,
            $ipAddress,
            $route,
        );

        $this->assertInstanceOf(Auth::class, $auth);

        $events = $this->popRecordedEvent($auth);

        $this->assertRecordedEvent(UserEndedImpersonating::class, [
            'adminUserId' => $adminUserId->toString(),
            'userAgent'   => null,
            'ipAddress'   => $ipAddress,
            'route'       => $route,
        ], $events);

        $this->assertCount(1, $events);

        $this->assertEquals($authId, $auth->authId());
    }
}
