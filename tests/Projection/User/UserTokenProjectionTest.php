<?php

declare(strict_types=1);

namespace App\Tests\Projection\User;

use App\Model\User\Event;
use App\Projection\User\UserTokenProjection;
use App\Tests\BaseTestCase;
use Mockery;
use Prooph\EventStore\Projection\ReadModelProjector;

class UserTokenProjectionTest extends BaseTestCase
{
    public function test(): void
    {
        $projectedEvents = [
            Event\InviteSent::class,
            Event\PasswordRecoverySent::class,
            Event\UserVerified::class,
            Event\ChangedPassword::class,
        ];

        $projection = new UserTokenProjection();

        $projector = Mockery::mock(ReadModelProjector::class);
        $projector->shouldReceive('fromStream')
            ->once()
            ->with('user')
            ->andReturnSelf();

        $projector->shouldReceive('when')
            ->withArgs(function ($eventHandlers) use ($projectedEvents) {
                if (!\is_array($eventHandlers)) {
                    return false;
                }

                foreach ($projectedEvents as $event) {
                    if (!\array_key_exists($event, $eventHandlers)) {
                        return false;
                    }
                }

                return true;
            });

        $projection->project($projector);
    }
}
