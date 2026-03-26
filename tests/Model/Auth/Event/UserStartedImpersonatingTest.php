<?php

declare(strict_types=1);

namespace App\Tests\Model\Auth\Event;

use App\Model\Auth\Event\UserStartedImpersonating;
use App\Tests\BaseTestCase;
use Xm\SymfonyBundle\Model\Email;
use Xm\SymfonyBundle\Tests\CanCreateEventFromArray;

class UserStartedImpersonatingTest extends BaseTestCase
{
    use CanCreateEventFromArray;

    public function testOccur(): void
    {
        $faker = $this->faker();

        $authId = $faker->authId();
        $adminUserId = $faker->userId();
        $impersonatedUserId = $faker->userId();
        $impersonatedEmail = Email::fromString($faker->email());
        $userAgent = $faker->userAgent();
        $ipAddress = $faker->ipv4();
        $route = $faker->slug();

        $event = UserStartedImpersonating::now(
            $authId,
            $adminUserId,
            $impersonatedUserId,
            $impersonatedEmail,
            $userAgent,
            $ipAddress,
            $route,
        );

        $this->assertSameValueAs($authId, $event->authId());
        $this->assertSameValueAs($adminUserId, $event->adminUserId());
        $this->assertSameValueAs($impersonatedUserId, $event->impersonatedUserId());
        $this->assertSameValueAs($impersonatedEmail, $event->impersonatedEmail());
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
        $impersonatedEmail = Email::fromString($faker->email());
        $ipAddress = $faker->ipv4();
        $route = $faker->slug();

        $event = UserStartedImpersonating::now(
            $authId,
            $adminUserId,
            $impersonatedUserId,
            $impersonatedEmail,
            null,
            $ipAddress,
            $route,
        );

        $this->assertNull($event->userAgent());

        $this->assertSameValueAs($authId, $event->authId());
        $this->assertSameValueAs($adminUserId, $event->adminUserId());
        $this->assertSameValueAs($impersonatedUserId, $event->impersonatedUserId());
        $this->assertSameValueAs($impersonatedEmail, $event->impersonatedEmail());
        $this->assertSame($ipAddress, $event->ipAddress());
        $this->assertSame($route, $event->route());
    }

    public function testFromArray(): void
    {
        $faker = $this->faker();

        $authId = $faker->authId();
        $adminUserId = $faker->userId();
        $impersonatedUserId = $faker->userId();
        $impersonatedEmail = Email::fromString($faker->email());
        $userAgent = $faker->userAgent();
        $ipAddress = $faker->ipv4();
        $route = $faker->slug();

        /** @var UserStartedImpersonating $event */
        $event = $this->createEventFromArray(
            UserStartedImpersonating::class,
            $authId->toString(),
            [
                'adminUserId'        => $adminUserId->toString(),
                'impersonatedUserId' => $impersonatedUserId->toString(),
                'impersonatedEmail'  => $impersonatedEmail->toString(),
                'userAgent'          => $userAgent,
                'ipAddress'          => $ipAddress,
                'route'              => $route,
            ],
        );

        $this->assertInstanceOf(UserStartedImpersonating::class, $event);

        $this->assertSameValueAs($authId, $event->authId());
        $this->assertSameValueAs($adminUserId, $event->adminUserId());
        $this->assertSameValueAs($impersonatedUserId, $event->impersonatedUserId());
        $this->assertSameValueAs($impersonatedEmail, $event->impersonatedEmail());
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
        $impersonatedEmail = Email::fromString($faker->email());
        $ipAddress = $faker->ipv4();
        $route = $faker->slug();

        /** @var UserStartedImpersonating $event */
        $event = $this->createEventFromArray(
            UserStartedImpersonating::class,
            $authId->toString(),
            [
                'adminUserId'        => $adminUserId->toString(),
                'impersonatedUserId' => $impersonatedUserId->toString(),
                'impersonatedEmail'  => $impersonatedEmail->toString(),
                'userAgent'          => null,
                'ipAddress'          => $ipAddress,
                'route'              => $route,
            ],
        );

        $this->assertInstanceOf(UserStartedImpersonating::class, $event);

        $this->assertNull($event->userAgent());

        $this->assertSameValueAs($authId, $event->authId());
        $this->assertSameValueAs($adminUserId, $event->adminUserId());
        $this->assertSameValueAs($impersonatedUserId, $event->impersonatedUserId());
        $this->assertSameValueAs($impersonatedEmail, $event->impersonatedEmail());
        $this->assertSame($ipAddress, $event->ipAddress());
        $this->assertSame($route, $event->route());
    }
}
