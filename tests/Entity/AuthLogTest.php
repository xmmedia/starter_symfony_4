<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\AuthLog;
use App\Entity\User;
use App\Model\AuthLog\AuthLogId;
use App\Tests\BaseTestCase;

class AuthLogTest extends BaseTestCase
{
    public function testAuthLogId(): void
    {
        $faker = $this->faker();

        $authLogId = AuthLogId::fromString($faker->uuid());

        $authLog = new AuthLog();
        $reflection = new \ReflectionClass(AuthLog::class);
        $reflection->getProperty('authLogId')
            ->setValue($authLog, $authLogId->uuid());

        $this->assertSame($authLogId->toString(), $authLog->authLogId()->toString());
    }

    public function testEventType(): void
    {
        $faker = $this->faker();

        $eventType = $faker->word();

        $authLog = new AuthLog();
        $reflection = new \ReflectionClass(AuthLog::class);
        $reflection->getProperty('eventType')
            ->setValue($authLog, $eventType);

        $this->assertSame($eventType, $authLog->eventType());
    }

    public function testUser(): void
    {
        $user = new User();

        $authLog = new AuthLog();
        $reflection = new \ReflectionClass(AuthLog::class);
        $reflection->getProperty('user')
            ->setValue($authLog, $user);

        $this->assertSame($user, $authLog->user());
    }

    public function testUserNull(): void
    {
        $authLog = new AuthLog();

        $this->assertNull($authLog->user());
    }

    public function testImpersonatedUser(): void
    {
        $user = new User();

        $authLog = new AuthLog();
        $reflection = new \ReflectionClass(AuthLog::class);
        $reflection->getProperty('impersonatedUser')
            ->setValue($authLog, $user);

        $this->assertSame($user, $authLog->impersonatedUser());
    }

    public function testImpersonatedUserNull(): void
    {
        $authLog = new AuthLog();

        $this->assertNull($authLog->impersonatedUser());
    }

    public function testEmail(): void
    {
        $faker = $this->faker();

        $email = $faker->email();

        $authLog = new AuthLog();
        $reflection = new \ReflectionClass(AuthLog::class);
        $reflection->getProperty('email')
            ->setValue($authLog, $email);

        $this->assertSame($email, $authLog->email());
    }

    public function testEmailNull(): void
    {
        $authLog = new AuthLog();

        $this->assertNull($authLog->email());
    }

    public function testIpAddress(): void
    {
        $faker = $this->faker();

        $ipAddress = $faker->ipv4();

        $authLog = new AuthLog();
        $reflection = new \ReflectionClass(AuthLog::class);
        $reflection->getProperty('ipAddress')
            ->setValue($authLog, $ipAddress);

        $this->assertSame($ipAddress, $authLog->ipAddress());
    }

    public function testUserAgent(): void
    {
        $faker = $this->faker();

        $userAgent = $faker->userAgent();

        $authLog = new AuthLog();
        $reflection = new \ReflectionClass(AuthLog::class);
        $reflection->getProperty('userAgent')
            ->setValue($authLog, $userAgent);

        $this->assertSame($userAgent, $authLog->userAgent());
    }

    public function testUserAgentNull(): void
    {
        $authLog = new AuthLog();

        $this->assertNull($authLog->userAgent());
    }

    public function testRoute(): void
    {
        $faker = $this->faker();

        $route = $faker->word();

        $authLog = new AuthLog();
        $reflection = new \ReflectionClass(AuthLog::class);
        $reflection->getProperty('route')
            ->setValue($authLog, $route);

        $this->assertSame($route, $authLog->route());
    }

    public function testRouteNull(): void
    {
        $authLog = new AuthLog();

        $this->assertNull($authLog->route());
    }

    public function testErrorMessage(): void
    {
        $faker = $this->faker();

        $errorMessage = $faker->sentence();

        $authLog = new AuthLog();
        $reflection = new \ReflectionClass(AuthLog::class);
        $reflection->getProperty('errorMessage')
            ->setValue($authLog, $errorMessage);

        $this->assertSame($errorMessage, $authLog->errorMessage());
    }

    public function testErrorMessageNull(): void
    {
        $authLog = new AuthLog();

        $this->assertNull($authLog->errorMessage());
    }

    public function testOccurredAt(): void
    {
        $faker = $this->faker();

        $occurredAt = \DateTimeImmutable::createFromMutable($faker->dateTime());

        $authLog = new AuthLog();
        $reflection = new \ReflectionClass(AuthLog::class);
        $reflection->getProperty('occurredAt')
            ->setValue($authLog, $occurredAt);

        $this->assertSame($occurredAt, $authLog->occurredAt());
    }
}
