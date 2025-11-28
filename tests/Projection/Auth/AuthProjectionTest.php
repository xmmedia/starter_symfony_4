<?php

declare(strict_types=1);

namespace App\Tests\Projection\Auth;

use App\Model\Auth\Event;
use App\Model\Auth\Event\UserLoggedIn;
use App\Projection\Auth\AuthProjection;
use App\Projection\Auth\AuthReadModel;
use App\Tests\BaseTestCase;
use DateTime;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Prooph\EventStore\Projection\AbstractReadModel;
use Prooph\EventStore\Projection\ReadModelProjector;
use Xm\SymfonyBundle\Tests\ProjectionWhenArgs;

class AuthProjectionTest extends BaseTestCase
{
    use ProjectionWhenArgs;

    public function test(): void
    {
        $projectedEvents = [
            Event\UserLoggedIn::class,
        ];

        $projection = new AuthProjection();

        $projector = \Mockery::mock(ReadModelProjector::class);
        $projector->shouldReceive('fromStream')
            ->once()
            ->with('auth')
            ->andReturnSelf();

        $projector->shouldReceive('when')
            ->withArgs($this->whenArgs($projectedEvents));

        $projection->project($projector);
    }

    public function testProjectConfiguresProjectorWithAuthStream(): void
    {
        $projector = \Mockery::mock(ReadModelProjector::class);
        $projector->shouldReceive('fromStream')
            ->once()
            ->with('auth')
            ->andReturnSelf();
        $projector->shouldReceive('when')
            ->once()
            ->with(
                \Mockery::on(fn($handlers): bool => \array_key_exists(UserLoggedIn::class, $handlers)
                    && \is_callable($handlers[UserLoggedIn::class])),
            )
            ->andReturnSelf();

        $result = new AuthProjection()->project($projector);

        $this->assertSame($projector, $result);
    }

    public function testProjectHandlesUserLoggedInEvent(): void
    {
        $projector = \Mockery::mock(ReadModelProjector::class);
        $projector->shouldReceive('fromStream')
            ->once()
            ->with('auth')
            ->andReturnSelf();
        $projector->shouldReceive('when')
            ->once()
            ->with(
                \Mockery::on(fn($handlers): bool => isset($handlers[UserLoggedIn::class])
                    && \is_callable($handlers[UserLoggedIn::class])),
            )
            ->andReturnSelf();

        new AuthProjection()->project($projector);
    }

    public function testUserLoggedInCallsReadModelStack(): void
    {
        $faker = $this->faker();
        $authId = $faker->authId();
        $userId = $faker->userId();
        $email = $faker->emailVo();
        $userAgent = $faker->userAgent();
        $ipAddress = $faker->ipv4();
        $route = $faker->string(10);

        $event = Event\UserLoggedIn::now(
            $authId,
            $userId,
            $email,
            $userAgent,
            $ipAddress,
            $route,
        );

        $connection = \Mockery::mock(Connection::class);
        $readModel = new AuthReadModel($connection);

        //Spy on the stack to verify what gets stored
        $projector = \Mockery::mock(ReadModelProjector::class);
        $projector->shouldReceive('fromStream')
            ->once()
            ->with('auth')
            ->andReturnSelf();
        $projector->shouldReceive('when')
            ->once()
            ->andReturnUsing(function ($handlers) use ($event, $readModel, $projector) {
                $this->assertArrayHasKey(UserLoggedIn::class, $handlers);
                $handler = $handlers[UserLoggedIn::class];

                $projectorMock = \Mockery::mock(ReadModelProjector::class);
                $projectorMock->shouldReceive('readModel')
                    ->andReturn($readModel);

                $handler->call($projectorMock, [], $event);
                return $projector;
            });

        $projection = new AuthProjection();
        $projection->project($projector);

        // Use reflection to access the protected stack property from parent class
        $reflection = new \ReflectionClass(AbstractReadModel::class);

        $stackedProperty = $reflection->getProperty('stack');

        $stack = $stackedProperty->getValue($readModel);

        $createdAt = DateTime::createFromImmutable($event->createdAt());

        $this->assertCount(1, $stack);
        $this->assertEquals('loggedIn', $stack[0][0]);
        $this->assertEquals([$userId->toString(), $createdAt], $stack[0][1]);
    }
}
