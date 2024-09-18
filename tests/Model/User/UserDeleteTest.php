<?php

declare(strict_types=1);

namespace App\Tests\Model\User;

use App\Model\User\Event;
use App\Model\User\Exception;
use App\Tests\BaseTestCase;

class UserDeleteTest extends BaseTestCase
{
    use UserTestTrait;

    public function testDelete(): void
    {
        $user = $this->getUserActive();

        $user->delete();

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\UserWasDeletedByAdmin::class,
            [
            ],
            $events,
        );

        $this->assertCount(1, $events);
    }

    public function testDeleteAlreadyDeleted(): void
    {
        $user = $this->getUserActive();
        $user->delete();

        $this->expectException(Exception\UserIsDeleted::class);
        $this->expectExceptionMessage(
            \sprintf('Tried to delete User with ID "%s" that\'s already deleted', $user->userId()),
        );

        $user->delete();
    }
}
