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

        $email = $faker->emailVo;
        $role = Role::ROLE_USER();
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        $user->updateByAdmin(
            $email,
            $role,
            $firstName,
            $lastName,
            $this->userUniquenessCheckerNone
        );

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\AdminUpdatedUser::class,
            [
                'email'     => $email->toString(),
                'role'      => $role->getValue(),
                'firstName' => $firstName->toString(),
                'lastName'  => $lastName->toString(),
            ],
            $events
        );

        $this->assertCount(1, $events);
    }

    public function testUpdateByAdminDuplicate(): void
    {
        $faker = $this->faker();

        $user = $this->getUserActive();

        $email = $faker->emailVo;
        $role = Role::ROLE_USER();
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        $this->expectException(Exception\DuplicateEmail::class);

        $user->updateByAdmin(
            $email,
            $role,
            $firstName,
            $lastName,
            $this->userUniquenessCheckerDuplicate
        );
    }

    public function testUpdate(): void
    {
        $faker = $this->faker();

        $user = $this->getUserActive();

        $email = $faker->emailVo;
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        $user->update(
            $email,
            $firstName,
            $lastName,
            $this->userUniquenessCheckerNone
        );

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\UserUpdatedProfile::class,
            [
                'email'     => $email->toString(),
                'firstName' => $firstName->toString(),
                'lastName'  => $lastName->toString(),
            ],
            $events
        );

        $this->assertCount(1, $events);
    }

    public function testUpdateInactive(): void
    {
        $faker = $this->faker();

        $user = $this->getUserInactive();

        $email = $faker->emailVo;
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        $this->expectException(Exception\InvalidUserActiveStatus::class);

        $user->update(
            $email,
            $firstName,
            $lastName,
            $this->userUniquenessCheckerNone
        );
    }

    public function testUpdateDuplicate(): void
    {
        $faker = $this->faker();

        $user = $this->getUserActive();

        $email = $faker->emailVo;
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        $this->expectException(Exception\DuplicateEmail::class);

        $user->update(
            $email,
            $firstName,
            $lastName,
            $this->userUniquenessCheckerDuplicate
        );
    }
}
