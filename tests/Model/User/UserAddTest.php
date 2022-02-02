<?php

declare(strict_types=1);

namespace App\Tests\Model\User;

use App\Model\User\Event;
use App\Model\User\Exception;
use App\Model\User\Name;
use App\Model\User\Role;
use App\Model\User\User;
use App\Tests\BaseTestCase;

class UserAddTest extends BaseTestCase
{
    use UserTestTrait;

    public function testAddByAdmin(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId();
        $email = $faker->emailVo();
        $password = $faker->password();
        $role = Role::ROLE_USER();
        $firstName = Name::fromString($faker->firstName());
        $lastName = Name::fromString($faker->lastName());

        $user = User::addByAdmin(
            $userId,
            $email,
            $password,
            $role,
            true,
            $firstName,
            $lastName,
            false,
            $this->userUniquenessCheckerNone,
        );

        $this->assertInstanceOf(User::class, $user);

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\UserWasAddedByAdmin::class,
            [
                'email'          => $email->toString(),
                'hashedPassword' => $password,
                'role'           => $role->getValue(),
                'active'         => true,
                'firstName'      => $firstName->toString(),
                'lastName'       => $lastName->toString(),
                'sendInvite'     => false,
            ],
            $events,
        );

        $this->assertCount(1, $events);

        $this->assertEquals($userId, $user->userId());
        $this->assertTrue($user->verified());
        $this->assertTrue($user->active());
    }

    public function testAddByAdminSendInvite(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId();
        $email = $faker->emailVo();
        $password = $faker->password();
        $role = Role::ROLE_USER();
        $firstName = Name::fromString($faker->firstName());
        $lastName = Name::fromString($faker->lastName());

        $user = User::addByAdmin(
            $userId,
            $email,
            $password,
            $role,
            true,
            $firstName,
            $lastName,
            true,
            $this->userUniquenessCheckerNone,
        );

        $this->assertInstanceOf(User::class, $user);

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\UserWasAddedByAdmin::class,
            [
                'email'          => $email->toString(),
                'hashedPassword' => $password,
                'role'           => $role->getValue(),
                'active'         => true,
                'firstName'      => $firstName->toString(),
                'lastName'       => $lastName->toString(),
                'sendInvite'     => true,
            ],
            $events,
        );

        $this->assertCount(1, $events);

        $this->assertEquals($userId, $user->userId());
        $this->assertFalse($user->verified());
        $this->assertTrue($user->active());
    }

    public function testAddByAdminNotActive(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId();
        $email = $faker->emailVo();
        $password = $faker->password();
        $role = Role::ROLE_USER();
        $firstName = Name::fromString($faker->firstName());
        $lastName = Name::fromString($faker->lastName());

        $user = User::addByAdmin(
            $userId,
            $email,
            $password,
            $role,
            false,
            $firstName,
            $lastName,
            true,
            $this->userUniquenessCheckerNone,
        );

        $this->assertInstanceOf(User::class, $user);

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\UserWasAddedByAdmin::class,
            [
                'email'          => $email->toString(),
                'hashedPassword' => $password,
                'role'           => $role->getValue(),
                'active'         => false,
                'firstName'      => $firstName->toString(),
                'lastName'       => $lastName->toString(),
                'sendInvite'     => false,
            ],
            $events,
        );

        $this->assertCount(1, $events);

        $this->assertEquals($userId, $user->userId());
        $this->assertTrue($user->verified());
        $this->assertFalse($user->active());
    }

    public function testAddByAdminDuplicateEmail(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId();
        $email = $faker->emailVo();
        $password = $faker->password();
        $role = Role::ROLE_USER();
        $firstName = Name::fromString($faker->firstName());
        $lastName = Name::fromString($faker->lastName());

        $this->expectException(Exception\DuplicateEmail::class);

        User::addByAdmin(
            $userId,
            $email,
            $password,
            $role,
            true,
            $firstName,
            $lastName,
            true,
            $this->userUniquenessCheckerDuplicate,
        );
    }

    public function testAddByAdminMinimal(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId();
        $email = $faker->emailVo();
        $password = $faker->password();
        $role = Role::ROLE_USER();
        $firstName = Name::fromString($faker->firstName());
        $lastName = Name::fromString($faker->lastName());
        $sendInvite = $faker->boolean();

        $user = User::addByAdminMinimum(
            $userId,
            $email,
            $password,
            $role,
            $firstName,
            $lastName,
            $sendInvite,
            $this->userUniquenessCheckerNone,
        );

        $this->assertInstanceOf(User::class, $user);

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\MinimalUserWasAddedByAdmin::class,
            [
                'email'          => $email->toString(),
                'hashedPassword' => $password,
                'role'           => $role->getValue(),
                'firstName'      => $firstName->toString(),
                'lastName'       => $lastName->toString(),
                'sendInvite'     => $sendInvite,
            ],
            $events,
        );

        $this->assertCount(1, $events);

        $this->assertEquals($userId, $user->userId());
        $this->assertTrue($user->verified());
        $this->assertTrue($user->active());
    }

    public function testAddByAdminMinimalDuplicate(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId();
        $email = $faker->emailVo();
        $password = $faker->password();
        $role = Role::ROLE_USER();
        $firstName = Name::fromString($faker->firstName());
        $lastName = Name::fromString($faker->lastName());
        $sendInvite = $faker->boolean();

        $this->expectException(Exception\DuplicateEmail::class);

        User::addByAdminMinimum(
            $userId,
            $email,
            $password,
            $role,
            $firstName,
            $lastName,
            $sendInvite,
            $this->userUniquenessCheckerDuplicate,
        );
    }
}
