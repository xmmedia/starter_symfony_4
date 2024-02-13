<?php

declare(strict_types=1);

namespace App\Tests\Model\User;

use App\Model\User\Event;
use App\Model\User\Exception;
use App\Tests\BaseTestCase;

class UserFlagsTest extends BaseTestCase
{
    use UserTestTrait;

    public function testVerifyByAdmin(): void
    {
        $user = $this->getUserActive(true);

        $user->verifyByAdmin();

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\UserVerifiedByAdmin::class,
            [],
            $events,
        );

        $this->assertCount(1, $events);

        $this->assertTrue($user->verified());
    }

    public function testVerifyByAdminAlreadyVerified(): void
    {
        $user = $this->getUserActive();

        $this->expectException(Exception\UserAlreadyVerified::class);

        $user->verifyByAdmin();
    }

    public function testVerifyByAdminDeleted(): void
    {
        $user = $this->getUserActive();
        $user->delete();
        $this->popRecordedEvent($user);

        $this->expectException(Exception\UserIsDeleted::class);
        $this->expectExceptionMessage(
            sprintf('Tried to verify (by admin) deleted User with ID "%s"', $user->userId()),
        );

        $user->verifyByAdmin();
    }

    public function testActivateByAdmin(): void
    {
        $user = $this->getUserInactive();

        $user->activateByAdmin();

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\UserActivatedByAdmin::class,
            [],
            $events,
        );

        $this->assertCount(1, $events);

        $this->assertTrue($user->active());
    }

    public function testActivateByAdminAlreadyActive(): void
    {
        $user = $this->getUserActive();

        $this->expectException(Exception\InvalidUserActiveStatus::class);

        $user->activateByAdmin();
    }

    public function testActivateByAdminDeleted(): void
    {
        $user = $this->getUserActive();
        $user->delete();
        $this->popRecordedEvent($user);

        $this->expectException(Exception\UserIsDeleted::class);
        $this->expectExceptionMessage(
            sprintf('Tried to activate (by admin) deleted User with ID "%s"', $user->userId()),
        );

        $user->activateByAdmin();
    }

    public function testDeactivateByAdmin(): void
    {
        $user = $this->getUserActive();

        $user->deactivateByAdmin();

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\UserDeactivatedByAdmin::class,
            [],
            $events,
        );

        $this->assertCount(1, $events);

        $this->assertFalse($user->active());
    }

    public function testDeactivateByAdminAlreadyInactive(): void
    {
        $user = $this->getUserInactive();

        $this->expectException(Exception\InvalidUserActiveStatus::class);

        $user->deactivateByAdmin();
    }

    public function testDeactivateByAdminDeleted(): void
    {
        $user = $this->getUserInactive();
        $user->delete();
        $this->popRecordedEvent($user);

        $this->expectException(Exception\UserIsDeleted::class);
        $this->expectExceptionMessage(
            sprintf('Tried to deactivate (by admin) deleted User with ID "%s"', $user->userId()),
        );

        $user->deactivateByAdmin();
    }

    public function testVerify(): void
    {
        $user = $this->getUserActive(true);

        $user->verify();

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(Event\UserVerified::class, [], $events);

        $this->assertCount(1, $events);

        $this->assertTrue($user->verified());
    }

    public function testVerifyAlreadyVerified(): void
    {
        $user = $this->getUserActive();

        $this->expectException(Exception\UserAlreadyVerified::class);

        $user->verify();
    }

    public function testVerifyInactive(): void
    {
        $user = $this->getUserActive(true);

        $user->deactivateByAdmin();

        $this->expectException(Exception\InvalidUserActiveStatus::class);

        $user->verify();
    }

    public function testVerifyDeleted(): void
    {
        $user = $this->getUserActive(true);
        $user->delete();

        $this->expectException(Exception\UserIsDeleted::class);
        $this->expectExceptionMessage(
            sprintf('Tried to verify deleted User with ID "%s"', $user->userId()),
        );

        $user->verify();
    }
}
