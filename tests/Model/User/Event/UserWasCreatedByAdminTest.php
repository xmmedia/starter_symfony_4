<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Event;

use App\Model\User\Event\UserWasCreatedByAdmin;
use App\Model\User\Name;
use App\Model\User\Role;
use App\Tests\BaseTestCase;
use App\Tests\CanCreateEventFromArray;

class UserWasCreatedByAdminTest extends BaseTestCase
{
    use CanCreateEventFromArray;

    public function testOccur(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $email = $faker->emailVo;
        $password = $faker->password;
        $role = Role::ROLE_USER();
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        $event = UserWasCreatedByAdmin::now(
            $userId,
            $email,
            $password,
            $role,
            true,
            $firstName,
            $lastName,
            false
        );

        $this->assertEquals($userId, $event->userId());
        $this->assertEquals($email, $event->email());
        $this->assertEquals($password, $event->encodedPassword());
        $this->assertEquals($role, $event->role());
        $this->assertTrue($event->active());
        $this->assertEquals($firstName, $event->firstName());
        $this->assertEquals($lastName, $event->lastName());
        $this->assertFalse($event->sendInvite());
    }

    public function testFromArray(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $email = $faker->emailVo;
        $password = $faker->password;
        $role = Role::ROLE_USER();
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        /** @var UserWasCreatedByAdmin $event */
        $event = $this->createEventFromArray(
            UserWasCreatedByAdmin::class,
            $userId->toString(),
            [
                'email'           => $email->toString(),
                'encodedPassword' => $password,
                'role'            => $role->getValue(),
                'active'          => true,
                'firstName'       => $firstName->toString(),
                'lastName'        => $lastName->toString(),
                'sendInvite'      => false,
            ]
        );

        $this->assertInstanceOf(UserWasCreatedByAdmin::class, $event);

        $this->assertEquals($userId, $event->userId());
        $this->assertEquals($email, $event->email());
        $this->assertEquals($password, $event->encodedPassword());
        $this->assertEquals($role, $event->role());
        $this->assertTrue($event->active());
        $this->assertEquals($firstName, $event->firstName());
        $this->assertEquals($lastName, $event->lastName());
        $this->assertFalse($event->sendInvite());
    }
}
