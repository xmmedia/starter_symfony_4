<?php

declare(strict_types=1);

namespace App\Tests\Model\User;

use App\Model\User\Event;
use App\Model\User\Exception;
use App\Tests\BaseTestCase;
use Xm\SymfonyBundle\Model\EmailGatewayMessageId;

class UserPasswordTest extends BaseTestCase
{
    use UserTestTrait;

    public function testChangePasswordByAdmin(): void
    {
        $user = $this->getUserActive();

        $user->changePasswordByAdmin();

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\AdminChangedPassword::class,
            [],
            $events,
        );

        $this->assertCount(1, $events);
    }

    public function testChangePasswordByAdminDeleted(): void
    {
        $user = $this->getUserActive();
        $user->delete();

        $this->expectException(Exception\UserIsDeleted::class);
        $this->expectExceptionMessageIsOrContains(
            \sprintf('Tried to change password (by admin) deleted User with ID "%s"', $user->userId()),
        );

        $user->changePasswordByAdmin();
    }

    public function testPasswordRecoverySent(): void
    {
        $faker = $this->faker();

        $user = $this->getUserActive();
        $this->popRecordedEvent($user);

        $messageId = EmailGatewayMessageId::fromString($faker->uuid());

        $user->passwordRecoverySent($messageId);

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\PasswordRecoverySent::class,
            [
                'messageId' => $messageId->toString(),
            ],
            $events,
        );

        $this->assertCount(1, $events);
    }

    public function testPasswordRecoverySentInactive(): void
    {
        $faker = $this->faker();

        $user = $this->getUserInactive();

        $messageId = EmailGatewayMessageId::fromString($faker->uuid());

        $this->expectException(Exception\InvalidUserActiveStatus::class);

        $user->passwordRecoverySent($messageId);
    }

    public function testPasswordRecoverySentDeleted(): void
    {
        $faker = $this->faker();

        $user = $this->getUserInactive();
        $user->delete();

        $messageId = EmailGatewayMessageId::fromString($faker->uuid());

        $this->expectException(Exception\UserIsDeleted::class);
        $this->expectExceptionMessageIsOrContains(
            \sprintf('Tried to send password recovery to deleted User with ID "%s"', $user->userId()),
        );

        $user->passwordRecoverySent($messageId);
    }

    public function testChangePassword(): void
    {
        $user = $this->getUserActive();

        $user->changePassword();

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\ChangedPassword::class,
            [],
            $events,
        );

        $this->assertCount(1, $events);
    }

    public function testChangePasswordInactive(): void
    {
        $user = $this->getUserInactive();

        $this->expectException(Exception\InvalidUserActiveStatus::class);

        $user->changePassword();
    }

    public function testChangePasswordDeleted(): void
    {
        $user = $this->getUserInactive();
        $user->delete();

        $this->expectException(Exception\UserIsDeleted::class);
        $this->expectExceptionMessageIsOrContains(
            \sprintf('Tried to change password deleted User with ID "%s"', $user->userId()),
        );

        $user->changePassword();
    }

    public function testUpgradePassword(): void
    {
        $user = $this->getUserActive();

        $user->upgradePassword();

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\PasswordUpgraded::class,
            [],
            $events,
        );

        $this->assertCount(1, $events);
    }

    public function testUpgradePasswordInactive(): void
    {
        $user = $this->getUserInactive();

        $this->expectException(Exception\InvalidUserActiveStatus::class);

        $user->upgradePassword();
    }

    public function testUpgradePasswordDeleted(): void
    {
        $user = $this->getUserInactive();
        $user->delete();

        $this->expectException(Exception\UserIsDeleted::class);
        $this->expectExceptionMessageIsOrContains(
            \sprintf('Tried to upgrade password deleted User with ID "%s"', $user->userId()),
        );

        $user->upgradePassword();
    }
}
