<?php

declare(strict_types=1);

namespace App\Tests\Model\User;

use App\Model\User\Event;
use App\Model\User\Exception;
use App\Model\User\Name;
use App\Model\User\Role;
use App\Tests\BaseTestCase;

class UserUpdateTest extends BaseTestCase
{
    use UserTestTrait;

    public function testUpdateByAdmin(): void
    {
        $faker = $this->faker();

        $user = $this->getUserActive();

        $email = $faker->emailVo();
        $role = Role::ROLE_USER();
        $firstName = Name::fromString($faker->firstName());
        $lastName = Name::fromString($faker->lastName());
        $userData = $faker->userData();

        $user->updateByAdmin(
            $email,
            $role,
            $firstName,
            $lastName,
            $userData,
            $this->userUniquenessCheckerNone,
        );

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\UserWasUpdatedByAdmin::class,
            [
                'email'     => $email->toString(),
                'role'      => $role->getValue(),
                'firstName' => $firstName->toString(),
                'lastName'  => $lastName->toString(),
                'userData'  => $userData->toArray(),
            ],
            $events,
        );

        $this->assertCount(1, $events);
    }

    public function testUpdateByAdminDuplicate(): void
    {
        $faker = $this->faker();

        $user = $this->getUserActive();

        $email = $faker->emailVo();
        $role = Role::ROLE_USER();
        $firstName = Name::fromString($faker->firstName());
        $lastName = Name::fromString($faker->lastName());
        $userData = $faker->userData();

        $this->expectException(Exception\DuplicateEmail::class);

        $user->updateByAdmin(
            $email,
            $role,
            $firstName,
            $lastName,
            $userData,
            $this->userUniquenessCheckerDuplicate,
        );
    }

    public function testUpdateByAdminDeleted(): void
    {
        $faker = $this->faker();

        $user = $this->getUserActive();
        $user->delete();

        $email = $faker->emailVo();
        $role = Role::ROLE_USER();
        $firstName = Name::fromString($faker->firstName());
        $lastName = Name::fromString($faker->lastName());
        $userData = $faker->userData();

        $this->expectException(Exception\UserIsDeleted::class);
        $this->expectExceptionMessage(
            \sprintf('Tried to update (by admin) deleted User with ID "%s"', $user->userId()),
        );

        $user->updateByAdmin(
            $email,
            $role,
            $firstName,
            $lastName,
            $userData,
            $this->userUniquenessCheckerNone,
        );
    }

    public function testUpdate(): void
    {
        $faker = $this->faker();

        $user = $this->getUserActive();

        $email = $faker->emailVo();
        $firstName = Name::fromString($faker->firstName());
        $lastName = Name::fromString($faker->lastName());
        $userData = $faker->userData();

        $user->update(
            $email,
            $firstName,
            $lastName,
            $userData,
            $this->userUniquenessCheckerNone,
        );

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\UserUpdatedProfile::class,
            [
                'email'     => $email->toString(),
                'firstName' => $firstName->toString(),
                'lastName'  => $lastName->toString(),
                'userData'  => $userData->toArray(),
            ],
            $events,
        );

        $this->assertCount(1, $events);
    }

    public function testUpdateInactive(): void
    {
        $faker = $this->faker();

        $user = $this->getUserInactive();

        $email = $faker->emailVo();
        $firstName = Name::fromString($faker->firstName());
        $lastName = Name::fromString($faker->lastName());
        $userData = $faker->userData();

        $this->expectException(Exception\InvalidUserActiveStatus::class);

        $user->update(
            $email,
            $firstName,
            $lastName,
            $userData,
            $this->userUniquenessCheckerNone,
        );
    }

    public function testUpdateDuplicate(): void
    {
        $faker = $this->faker();

        $user = $this->getUserActive();

        $email = $faker->emailVo();
        $firstName = Name::fromString($faker->firstName());
        $lastName = Name::fromString($faker->lastName());
        $userData = $faker->userData();

        $this->expectException(Exception\DuplicateEmail::class);

        $user->update(
            $email,
            $firstName,
            $lastName,
            $userData,
            $this->userUniquenessCheckerDuplicate,
        );
    }

    public function testUpdateDeleted(): void
    {
        $faker = $this->faker();

        $user = $this->getUserActive();
        $user->delete();

        $email = $faker->emailVo();
        $firstName = Name::fromString($faker->firstName());
        $lastName = Name::fromString($faker->lastName());
        $userData = $faker->userData();

        $this->expectException(Exception\UserIsDeleted::class);
        $this->expectExceptionMessage(
            \sprintf('Tried to update deleted User with ID "%s"', $user->userId()),
        );

        $user->update(
            $email,
            $firstName,
            $lastName,
            $userData,
            $this->userUniquenessCheckerNone,
        );
    }
}
