<?php

declare(strict_types=1);

namespace App\Tests\Model\Auth\Event;

use App\Model\Auth\Event\UserEndedImpersonating;
use App\Tests\BaseTestCase;
use Xm\SymfonyBundle\Tests\CanCreateEventFromArray;

class UserEndedImpersonatingTest extends BaseTestCase
{
    use CanCreateEventFromArray;

    public function testOccur(): void
    {
        $faker = $this->faker();

        $authId = $faker->authId();
        $adminUserId = $faker->userId();
        $impersonatedUserId = $faker->userId();
        $userAgent = $faker->userAgent();
        $ipAddress = $faker->ipv4();
        $route = $faker->slug();

        $event = UserEndedImpersonating::now(
            $authId,
            $adminUserId,
            $impersonatedUserId,
            $userAgent,
            $ipAddress,
            $route,
        );

        $this->assertSameValueAs($authId, $event->authId());
        $this->assertSameValueAs($adminUserId, $event->adminUserId());
        $this->assertSameValueAs($impersonatedUserId, $event->impersonatedUserId());
        $this->assertSame($userAgent, $event->userAgent());
        $this->assertSame($ipAddress, $event->ipAddress());
        $this->assertSame($route, $event->route());
    }

    public function testOccurNullUserAgent(): void
    {
        $faker = $this->faker();

        $authId = $faker->authId();
        $adminUserId = $faker->userId();
        $impersonatedUserId = $faker->userId();
        $ipAddress = $faker->ipv4();
        $route = $faker->slug();

        $event = UserEndedImpersonating::now(
            $authId,
            $adminUserId,
            $impersonatedUserId,
            null,
            $ipAddress,
            $route,
        );

        $this->assertNull($event->userAgent());

        $this->assertSameValueAs($authId, $event->authId());
        $this->assertSameValueAs($adminUserId, $event->adminUserId());
        $this->assertSameValueAs($impersonatedUserId, $event->impersonatedUserId());
        $this->assertSame($ipAddress, $event->ipAddress());
        $this->assertSame($route, $event->route());
    }

    public function testFromArray(): void
    {
        $faker = $this->faker();

        $authId = $faker->authId();
        $adminUserId = $faker->userId();
        $impersonatedUserId = $faker->userId();
        $userAgent = $faker->userAgent();
        $ipAddress = $faker->ipv4();
        $route = $faker->slug();

        /** @var UserEndedImpersonating $event */
        $event = $this->createEventFromArray(
            UserEndedImpersonating::class,
            $authId->toString(),
            [
                'adminUserId'        => $adminUserId->toString(),
                'impersonatedUserId' => $impersonatedUserId->toString(),
                'userAgent'          => $userAgent,
                'ipAddress'          => $ipAddress,
                'route'              => $route,
            ],
        );

        $this->assertInstanceOf(UserEndedImpersonating::class, $event);

        $this->assertSameValueAs($authId, $event->authId());
        $this->assertSameValueAs($adminUserId, $event->adminUserId());
        $this->assertSameValueAs($impersonatedUserId, $event->impersonatedUserId());
        $this->assertSame($userAgent, $event->userAgent());
        $this->assertSame($ipAddress, $event->ipAddress());
        $this->assertSame($route, $event->route());
    }

    public function testFromArrayNullUserAgent(): void
    {
        $faker = $this->faker();

        $authId = $faker->authId();
        $adminUserId = $faker->userId();
        $impersonatedUserId = $faker->userId();
        $ipAddress = $faker->ipv4();
        $route = $faker->slug();

        /** @var UserEndedImpersonating $event */
        $event = $this->createEventFromArray(
            UserEndedImpersonating::class,
            $authId->toString(),
            [
                'adminUserId'        => $adminUserId->toString(),
                'impersonatedUserId' => $impersonatedUserId->toString(),
                'userAgent'          => null,
                'ipAddress'          => $ipAddress,
                'route'              => $route,
            ],
        );

        $this->assertInstanceOf(UserEndedImpersonating::class, $event);

        $this->assertNull($event->userAgent());

        $this->assertSameValueAs($authId, $event->authId());
        $this->assertSameValueAs($adminUserId, $event->adminUserId());
        $this->assertSameValueAs($impersonatedUserId, $event->impersonatedUserId());
        $this->assertSame($ipAddress, $event->ipAddress());
        $this->assertSame($route, $event->route());
    }
}
