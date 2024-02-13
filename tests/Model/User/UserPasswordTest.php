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
        $faker = $this->faker();

        $user = $this->getUserActive();

        $password = $faker->password();

        $user->changePasswordByAdmin($password);

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\AdminChangedPassword::class,
            [
                'hashedPassword' => $password,
            ],
            $events,
        );

        $this->assertCount(1, $events);
    }

    public function testChangePasswordByAdminDeleted(): void
    {
        $faker = $this->faker();

        $user = $this->getUserActive();
        $user->delete();

        $this->expectException(Exception\UserIsDeleted::class);
        $this->expectExceptionMessage(
            sprintf('Tried to change password (by admin) deleted User with ID "%s"', $user->userId()),
        );

        $user->changePasswordByAdmin($faker->password());
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
        $this->expectExceptionMessage(
            sprintf('Tried to send password recovery to deleted User with ID "%s"', $user->userId()),
        );

        $user->passwordRecoverySent($messageId);
    }

    public function testChangePassword(): void
    {
        $faker = $this->faker();

        $password = $faker->password();

        $user = $this->getUserActive();

        $user->changePassword($password);

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\ChangedPassword::class,
            ['hashedPassword' => $password],
            $events,
        );

        $this->assertCount(1, $events);
    }

    public function testChangePasswordInactive(): void
    {
        $faker = $this->faker();

        $password = $faker->password();

        $user = $this->getUserInactive();

        $this->expectException(Exception\InvalidUserActiveStatus::class);

        $user->changePassword($password);
    }

    public function testChangePasswordDeleted(): void
    {
        $faker = $this->faker();

        $user = $this->getUserInactive();
        $user->delete();

        $this->expectException(Exception\UserIsDeleted::class);
        $this->expectExceptionMessage(
            sprintf('Tried to change password deleted User with ID "%s"', $user->userId()),
        );

        $user->changePassword($faker->password());
    }

    public function testUpgradePassword(): void
    {
        $faker = $this->faker();

        $password = $faker->password();

        $user = $this->getUserActive();

        $user->upgradePassword($password);

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\PasswordUpgraded::class,
            ['hashedPassword' => $password],
            $events,
        );

        $this->assertCount(1, $events);
    }

    public function testUpgradePasswordInactive(): void
    {
        $faker = $this->faker();

        $user = $this->getUserInactive();

        $this->expectException(Exception\InvalidUserActiveStatus::class);

        $user->upgradePassword($faker->password());
    }

    public function testUpgradePasswordDeleted(): void
    {
        $faker = $this->faker();

        $user = $this->getUserInactive();
        $user->delete();

        $this->expectException(Exception\UserIsDeleted::class);
        $this->expectExceptionMessage(
            sprintf('Tried to upgrade password deleted User with ID "%s"', $user->userId()),
        );

        $user->upgradePassword($faker->password());
    }
}
