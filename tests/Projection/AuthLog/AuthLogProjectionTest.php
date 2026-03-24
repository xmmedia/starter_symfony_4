<?php

declare(strict_types=1);

namespace App\Tests\Projection\AuthLog;

use App\Model\Auth\Event;
use App\Projection\AuthLog\AuthLogProjection;
use App\Projection\AuthLog\AuthLogReadModel;
use App\Tests\BaseTestCase;
use Doctrine\DBAL\Connection;
use Prooph\EventStore\Projection\ReadModelProjector;
use Xm\SymfonyBundle\EventSourcing\AggregateChanged;
use Xm\SymfonyBundle\Tests\ProjectionReadModel;
use Xm\SymfonyBundle\Tests\ProjectionWhenArgs;

class AuthLogProjectionTest extends BaseTestCase
{
    use ProjectionReadModel;
    use ProjectionWhenArgs;

    private const array EXPECTED_TYPES = ['occurred_at' => 'datetime_immutable'];

    public function test(): void
    {
        $projectedEvents = [
            Event\UserLoggedIn::class,
            Event\UserFailedToLogin::class,
            Event\UserStartedImpersonating::class,
            Event\UserEndedImpersonating::class,
        ];

        $projection = new AuthLogProjection();

        $projector = \Mockery::mock(ReadModelProjector::class);
        $projector->shouldReceive('fromStream')
            ->once()
            ->with('auth')
            ->andReturnSelf();

        $projector->shouldReceive('when')
            ->withArgs($this->whenArgs($projectedEvents));

        $projection->project($projector);
    }

    public function testUserLoggedIn(): void
    {
        $faker = $this->faker();
        $authId = $faker->authId();
        $userId = $faker->userId();
        $email = $faker->emailVo();
        $userAgent = $faker->userAgent();
        $ipAddress = $faker->ipv4();
        $route = $faker->slug();

        $event = Event\UserLoggedIn::now(
            $authId,
            $userId,
            $email,
            $userAgent,
            $ipAddress,
            $route,
        );

        $stack = $this->runReadModel($event);

        $this->assertCount(1, $stack);
        $this->assertSame('insert', $stack[0][0]);
        $this->assertSame([
            [
                'auth_log_id'          => $event->aggregateId(),
                'event_type'           => 'login',
                'user_id'              => $userId->toString(),
                'impersonated_user_id' => null,
                'email'                => $email->toString(),
                'ip_address'           => $ipAddress,
                'user_agent'           => $userAgent,
                'route'                => $route,
                'error_message'        => null,
                'occurred_at'          => $event->createdAt(),
            ],
            self::EXPECTED_TYPES,
        ], $stack[0][1]);
    }

    public function testUserFailedToLogin(): void
    {
        $faker = $this->faker();
        $authId = $faker->authId();
        $userId = $faker->userId();
        $email = $faker->email();
        $userAgent = $faker->userAgent();
        $ipAddress = $faker->ipv4();
        $exceptionMessage = $faker->string(50);
        $route = $faker->slug();

        $event = Event\UserFailedToLogin::now(
            $authId,
            $email,
            $userId,
            $userAgent,
            $ipAddress,
            $exceptionMessage,
            $route,
        );

        $stack = $this->runReadModel($event);

        $this->assertCount(1, $stack);
        $this->assertSame('insert', $stack[0][0]);
        $this->assertSame([
            [
                'auth_log_id'          => $event->aggregateId(),
                'event_type'           => 'login_failed',
                'user_id'              => $userId->toString(),
                'impersonated_user_id' => null,
                'email'                => $email,
                'ip_address'           => $ipAddress,
                'user_agent'           => $userAgent,
                'route'                => $route,
                'error_message'        => $exceptionMessage,
                'occurred_at'          => $event->createdAt(),
            ],
            self::EXPECTED_TYPES,
        ], $stack[0][1]);
    }

    public function testUserStartedImpersonating(): void
    {
        $faker = $this->faker();
        $authId = $faker->authId();
        $adminUserId = $faker->userId();
        $impersonatedUserId = $faker->userId();
        $impersonatedEmail = $faker->emailVo();
        $userAgent = $faker->userAgent();
        $ipAddress = $faker->ipv4();
        $route = $faker->slug();

        $event = Event\UserStartedImpersonating::now(
            $authId,
            $adminUserId,
            $impersonatedUserId,
            $impersonatedEmail,
            $userAgent,
            $ipAddress,
            $route,
        );

        $stack = $this->runReadModel($event);

        $this->assertCount(1, $stack);
        $this->assertSame('insert', $stack[0][0]);
        $this->assertSame([
            [
                'auth_log_id'          => $event->aggregateId(),
                'event_type'           => 'impersonation_started',
                'user_id'              => $adminUserId->toString(),
                'impersonated_user_id' => $impersonatedUserId->toString(),
                'email'                => $impersonatedEmail->toString(),
                'ip_address'           => $ipAddress,
                'user_agent'           => $userAgent,
                'route'                => $route,
                'error_message'        => null,
                'occurred_at'          => $event->createdAt(),
            ],
            self::EXPECTED_TYPES,
        ], $stack[0][1]);
    }

    public function testUserEndedImpersonating(): void
    {
        $faker = $this->faker();
        $authId = $faker->authId();
        $adminUserId = $faker->userId();
        $impersonatedUserId = $faker->userId();
        $userAgent = $faker->userAgent();
        $ipAddress = $faker->ipv4();
        $route = $faker->slug();

        $event = Event\UserEndedImpersonating::now(
            $authId,
            $adminUserId,
            $impersonatedUserId,
            $userAgent,
            $ipAddress,
            $route,
        );

        $stack = $this->runReadModel($event);

        $this->assertCount(1, $stack);
        $this->assertSame('insert', $stack[0][0]);
        $this->assertSame([
            [
                'auth_log_id'          => $event->aggregateId(),
                'event_type'           => 'impersonation_ended',
                'user_id'              => $adminUserId->toString(),
                'impersonated_user_id' => $impersonatedUserId->toString(),
                'email'                => null,
                'ip_address'           => $ipAddress,
                'user_agent'           => $userAgent,
                'route'                => $route,
                'error_message'        => null,
                'occurred_at'          => $event->createdAt(),
            ],
            self::EXPECTED_TYPES,
        ], $stack[0][1]);
    }

    private function runReadModel(AggregateChanged $event): mixed
    {
        $readModel = new AuthLogReadModel(\Mockery::mock(Connection::class));

        $projector = $this->createReadModelMock('auth', $event, $readModel);

        new AuthLogProjection()->project($projector);

        return $this->getReadModelStack($readModel);
    }
}
