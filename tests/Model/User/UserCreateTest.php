<?php

declare(strict_types=1);

namespace App\Tests\Model\User;

use App\Model\EmailGatewayMessageId;
use App\Model\User\Name;
use App\Model\User\Role;
use App\Model\User\Token;
use App\Model\User\User;
use App\Model\User\Event;
use App\Model\User\Exception;
use App\Tests\BaseTestCase;
use App\Tests\FakeAr;

class UserCreateTest extends BaseTestCase
{
    use UserTestTrait;

    public function testCreateByAdmin(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $email = $faker->emailVo;
        $password = $faker->password;
        $role = Role::ROLE_USER();
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        $user = User::createByAdmin(
            $userId,
            $email,
            $password,
            $role,
            true,
            $firstName,
            $lastName,
            false,
            $this->userUniquenessCheckerNone
        );

        $this->assertInstanceOf(User::class, $user);

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\UserWasCreatedByAdmin::class,
            [
                'email'           => $email->toString(),
                'encodedPassword' => $password,
                'role'            => $role->getValue(),
                'active'          => true,
                'firstName'       => $firstName->toString(),
                'lastName'        => $lastName->toString(),
                'sendInvite'      => false,
            ],
            $events
        );

        $this->assertCount(1, $events);

        $this->assertEquals($userId, $user->userId());
        $this->assertTrue($user->verified());
        $this->assertTrue($user->active());
    }

    public function testCreateByAdminSendInvite(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $email = $faker->emailVo;
        $password = $faker->password;
        $role = Role::ROLE_USER();
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        $user = User::createByAdmin(
            $userId,
            $email,
            $password,
            $role,
            true,
            $firstName,
            $lastName,
            true,
            $this->userUniquenessCheckerNone
        );

        $this->assertInstanceOf(User::class, $user);

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\UserWasCreatedByAdmin::class,
            [
                'email'           => $email->toString(),
                'encodedPassword' => $password,
                'role'            => $role->getValue(),
                'active'          => true,
                'firstName'       => $firstName->toString(),
                'lastName'        => $lastName->toString(),
                'sendInvite'      => true,
            ],
            $events
        );

        $this->assertCount(1, $events);

        $this->assertEquals($userId, $user->userId());
        $this->assertFalse($user->verified());
        $this->assertTrue($user->active());
    }

    public function testCreateByAdminNotActive(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $email = $faker->emailVo;
        $password = $faker->password;
        $role = Role::ROLE_USER();
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        $user = User::createByAdmin(
            $userId,
            $email,
            $password,
            $role,
            false,
            $firstName,
            $lastName,
            true,
            $this->userUniquenessCheckerNone
        );

        $this->assertInstanceOf(User::class, $user);

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\UserWasCreatedByAdmin::class,
            [
                'email'           => $email->toString(),
                'encodedPassword' => $password,
                'role'            => $role->getValue(),
                'active'          => false,
                'firstName'       => $firstName->toString(),
                'lastName'        => $lastName->toString(),
                'sendInvite'      => false,
            ],
            $events
        );

        $this->assertCount(1, $events);

        $this->assertEquals($userId, $user->userId());
        $this->assertTrue($user->verified());
        $this->assertFalse($user->active());
    }

    public function testCreateByAdminDuplicateEmail(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $email = $faker->emailVo;
        $password = $faker->password;
        $role = Role::ROLE_USER();
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        $this->expectException(Exception\DuplicateEmail::class);

        User::createByAdmin(
            $userId,
            $email,
            $password,
            $role,
            true,
            $firstName,
            $lastName,
            true,
            $this->userUniquenessCheckerDuplicate
        );
    }

    public function testCreateByAdminMinimal(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $email = $faker->emailVo;
        $password = $faker->password;
        $role = Role::ROLE_USER();

        $user = User::createByAdminMinimum(
            $userId,
            $email,
            $password,
            $role,
            $this->userUniquenessCheckerNone
        );

        $this->assertInstanceOf(User::class, $user);

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\MinimalUserWasCreatedByAdmin::class,
            [
                'email'           => $email->toString(),
                'encodedPassword' => $password,
                'role'            => $role->getValue(),
            ],
            $events
        );

        $this->assertCount(1, $events);

        $this->assertEquals($userId, $user->userId());
        $this->assertTrue($user->verified());
        $this->assertTrue($user->active());
    }

    public function testCreateByAdminMinimalDuplicate(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $email = $faker->emailVo;
        $password = $faker->password;
        $role = Role::ROLE_USER();

        $this->expectException(Exception\DuplicateEmail::class);

        User::createByAdminMinimum(
            $userId,
            $email,
            $password,
            $role,
            $this->userUniquenessCheckerDuplicate
        );
    }
}
