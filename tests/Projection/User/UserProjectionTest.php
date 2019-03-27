<?php

declare(strict_types=1);

namespace App\Tests\Projection\Member;

use App\Model\User\Event;
use App\Projection\User\UserProjection;
use App\Tests\BaseTestCase;
use Mockery;
use Prooph\EventStore\Projection\ReadModelProjector;

class UserProjectionTest extends BaseTestCase
{
    public function test(): void
    {
        $projectedEvents = [
            Event\UserWasCreatedByAdmin::class,
            Event\MinimalUserWasCreatedByAdmin::class,
            Event\AdminUpdatedUser::class,
            Event\AdminChangedPassword::class,
            Event\UserVerifiedByAdmin::class,
            Event\UserActivatedByAdmin::class,
            Event\UserDeactivatedByAdmin::class,
            Event\UserUpdatedProfile::class,
            Event\ChangedPassword::class,
            Event\UserVerified::class,
            Event\UserLoggedIn::class,
        ];

        $projection = new UserProjection();

        $projector = Mockery::mock(ReadModelProjector::class);
        $projector->shouldReceive('fromStream')
            ->once()
            ->with('user')
            ->andReturnSelf();

        $projector->shouldReceive('when')
            ->withArgs(function ($eventHandlers) use ($projectedEvents) {
                if (!is_array($eventHandlers)) {
                    return false;
                }

                foreach ($projectedEvents as $event) {
                    if (!array_key_exists($event, $eventHandlers)) {
                        return false;
                    }
                }

                return true;
            });

        $projection->project($projector);
    }
}
