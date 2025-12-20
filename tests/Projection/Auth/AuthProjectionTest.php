<?php

declare(strict_types=1);

namespace App\Tests\Projection\Auth;

use App\Model\Auth\Event;
use App\Projection\Auth\AuthProjection;
use App\Projection\Auth\AuthReadModel;
use App\Tests\BaseTestCase;
use Doctrine\DBAL\Connection;
use Prooph\EventStore\Projection\ReadModelProjector;
use Xm\SymfonyBundle\Tests\ProjectionReadModel;
use Xm\SymfonyBundle\Tests\ProjectionWhenArgs;

class AuthProjectionTest extends BaseTestCase
{
    use ProjectionReadModel;
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

    public function testConfiguresProjectorWithAuthStream(): void
    {
        $projector = \Mockery::mock(ReadModelProjector::class);
        $projector->shouldReceive('fromStream')
            ->once()
            ->with('auth')
            ->andReturnSelf();
        $projector->shouldReceive('when')
            ->once()
            ->with(
                \Mockery::on(fn ($handlers): bool => \array_key_exists(Event\UserLoggedIn::class, $handlers)
                    && \is_callable($handlers[Event\UserLoggedIn::class])),
            )
            ->andReturnSelf();

        $result = new AuthProjection()->project($projector);

        $this->assertSame($projector, $result);
    }

    public function testUserLoggedIn(): void
    {
        $faker = $this->faker();
        $userId = $faker->userId();

        $event = Event\UserLoggedIn::now(
            $faker->authId(),
            $userId,
            $faker->emailVo(),
            $faker->userAgent(),
            $faker->ipv4(),
            $faker->string(10),
        );

        $readModel = new AuthReadModel(\Mockery::mock(Connection::class));

        $projector = $this->createReadModelMock('auth', $event, $readModel);

        new AuthProjection()->project($projector);

        $stack = $this->getReadModelStack($readModel);

        $createdAt = \DateTime::createFromImmutable($event->createdAt());

        $this->assertCount(1, $stack);
        $this->assertSame('loggedIn', $stack[0][0]);
        $this->assertEquals([$userId->toString(), $createdAt], $stack[0][1]);
    }
}
