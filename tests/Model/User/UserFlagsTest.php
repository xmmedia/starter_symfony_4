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

class UserFlagsTest extends BaseTestCase
{
    use UserTestTrait;

    public function testVerifyByAdmin(): void
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
            true, // will set the user to unverified
            $this->userUniquenessCheckerNone
        );
        $this->popRecordedEvent($user);

        $user->verifyByAdmin();

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\UserVerifiedByAdmin::class,
            [],
            $events
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

    public function testActivateByAdmin(): void
    {
        $user = $this->getUserInactive();

        $user->activateByAdmin();

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\UserActivatedByAdmin::class,
            [],
            $events
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

    public function testDeactivateByAdmin(): void
    {
        $user = $this->getUserActive();

        $user->deactivateByAdmin();

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\UserDeactivatedByAdmin::class,
            [],
            $events
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

    public function testVerify(): void
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
        $this->popRecordedEvent($user);

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

        $user->deactivateByAdmin();

        $this->expectException(Exception\InvalidUserActiveStatus::class);

        $user->verify();
    }
}
