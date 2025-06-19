<?php

declare(strict_types=1);

namespace App\Tests\Model\User;

use App\Model\User\Event;
use App\Model\User\Exception;
use App\Model\User\Name;
use App\Model\User\Role;
use App\Model\User\User;
use App\Tests\BaseTestCase;
use Xm\SymfonyBundle\Model\EmailGatewayMessageId;

class UserActivateTest extends BaseTestCase
{
    use UserTestTrait;

    public function testActivateByAdmin(): void
    {
        $user = $this->getUser(true, false);
        $user->deactivateByAdmin();
        $this->popRecordedEvent($user);

        $user->activateByAdmin();
        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\UserActivatedByAdmin::class,
            [],
            $events,
        );

        $this->assertCount(1, $events);
    }

    public function testActivateByAdminAlreadyVerified(): void
    {
        $user = $this->getUser(true, false);

        $this->expectException(Exception\InvalidUserActiveStatus::class);

        $user->activateByAdmin();
    }

    public function testActivateByAdminDeleted(): void
    {
        $user = $this->getUserActive();
        $user->delete();

        $this->expectException(Exception\UserIsDeleted::class);
        $this->expectExceptionMessage(
            \sprintf('Tried to activate (by admin) deleted User with ID "%s"', $user->userId()),
        );

        $user->activateByAdmin();
    }


    public function testActivate(): void
    {
        $faker = $this->faker();

        $user = User::addByAdminMinimum(
            $faker->userId(),
            $faker->emailVo(),
            $faker->password(),
            Role::ROLE_USER(),
            Name::fromString($faker->firstName()),
            Name::fromString($faker->lastName()),
            true,
            $this->userUniquenessCheckerNone,
        );
        $this->popRecordedEvent($user);

        $user->activate();

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\UserActivated::class,
            [],
            $events,
        );

        $this->assertCount(1, $events);
    }

    public function testActivateAlreadyActivated(): void
    {
        $user = $this->getUserActive(false);

        $this->expectException(Exception\UserAlreadyVerified::class);

        $user->activate();

        $events = $this->popRecordedEvent($user);

        $this->assertCount(1, $events);
    }

    public function testActivateInactiveUserStatus(): void
    {
        $faker = $this->faker();

        $user = User::addByAdminMinimum(
            $faker->userId(),
            $faker->emailVo(),
            $faker->password(),
            Role::ROLE_USER(),
            Name::fromString($faker->firstName()),
            Name::fromString($faker->lastName()),
            true,
            $this->userUniquenessCheckerNone,
        );
        $this->popRecordedEvent($user);

        $user->deactivateByAdmin();
        $this->popRecordedEvent($user);

        $this->expectException(Exception\InvalidUserActiveStatus::class);
        $user->activate();
    }

    public function testActivateDeleted(): void
    {
        $user = $this->getUserActive();
        $user->delete();

        $this->expectException(Exception\UserIsDeleted::class);
        $this->expectExceptionMessage(
            \sprintf('Tried to activate deleted User with ID "%s"', $user->userId()),
        );

        $user->activate();
    }
}
