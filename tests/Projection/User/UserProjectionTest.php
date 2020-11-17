<?php

declare(strict_types=1);

namespace App\Tests\Projection\User;

use App\Model\User\Event;
use App\Projection\User\UserProjection;
use App\Tests\BaseTestCase;
use Mockery;
use Prooph\EventStore\Projection\ReadModelProjector;
use Xm\SymfonyBundle\Tests\ProjectionWhenArgs;

class UserProjectionTest extends BaseTestCase
{
    use ProjectionWhenArgs;

    public function test(): void
    {
        $projectedEvents = [
            Event\UserWasAddedByAdmin::class,
            Event\MinimalUserWasAddedByAdmin::class,
            Event\UserWasUpdatedByAdmin::class,
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
            ->withArgs($this->whenArgs($projectedEvents));

        $projection->project($projector);
    }
}
