<?php

declare(strict_types=1);

namespace App\Tests\Projection\Auth;

use App\Model\Auth\Event\UserLoggedIn;
use App\Projection\Auth\AuthProjection;
use App\Projection\Auth\AuthReadModel;
use App\Tests\BaseTestCase;
use Prooph\EventStore\Projection\ReadModelProjector;

class AuthProjectionTest extends BaseTestCase
{
    public function testProjectConfiguresProjectorWithAuthStream(): void
    {
        $projector = \Mockery::mock(ReadModelProjector::class);
        $projector->shouldReceive('fromStream')
            ->once()
            ->with('auth')
            ->andReturnSelf();
        $projector->shouldReceive('when')
            ->once()
            ->with(\Mockery::on(function ($handlers) {
                return \array_key_exists(UserLoggedIn::class, $handlers)
                    && \is_callable($handlers[UserLoggedIn::class]);
            }))
            ->andReturnSelf();

        $projection = new AuthProjection();
        $result = $projection->project($projector);

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
            ->with(\Mockery::on(function ($handlers) {
                // Verify the handler is callable
                return isset($handlers[UserLoggedIn::class])
                    && \is_callable($handlers[UserLoggedIn::class]);
            }))
            ->andReturnSelf();

        $projection = new AuthProjection();
        $projection->project($projector);
    }

    public function testProjectReturnsProjectorInstance(): void
    {
        $projector = \Mockery::mock(ReadModelProjector::class);
        $projector->shouldReceive('fromStream')
            ->once()
            ->with('auth')
            ->andReturnSelf();
        $projector->shouldReceive('when')
            ->once()
            ->with(\Mockery::type('array'))
            ->andReturnSelf();

        $projection = new AuthProjection();
        $result = $projection->project($projector);

        $this->assertInstanceOf(ReadModelProjector::class, $result);
    }

    public function testProjectOnlyHandlesUserLoggedInEvent(): void
    {
        $projector = \Mockery::mock(ReadModelProjector::class);
        $projector->shouldReceive('fromStream')
            ->once()
            ->with('auth')
            ->andReturnSelf();
        $projector->shouldReceive('when')
            ->once()
            ->with(\Mockery::on(function ($handlers) {
                // Should only have one handler
                return \count($handlers) === 1
                    && \array_key_exists(UserLoggedIn::class, $handlers);
            }))
            ->andReturnSelf();

        $projection = new AuthProjection();
        $projection->project($projector);
    }
}
