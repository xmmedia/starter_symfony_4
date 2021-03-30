<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Event;

use App\Model\User\Event\UserWasAddedByAdmin;
use App\Model\User\Name;
use App\Model\User\Role;
use App\Tests\BaseTestCase;
use Xm\SymfonyBundle\Tests\CanCreateEventFromArray;

class UserWasAddedByAdminTest extends BaseTestCase
{
    use CanCreateEventFromArray;

    public function testOccur(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $email = $faker->emailVo;
        $password = $faker->password;
        $role = Role::ROLE_USER();
        $active = $faker->boolean;
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);
        $sendInvite = $faker->boolean;

        $event = UserWasAddedByAdmin::now(
            $userId,
            $email,
            $password,
            $role,
            $active,
            $firstName,
            $lastName,
            $sendInvite,
        );

        $this->assertEquals($userId, $event->userId());
        $this->assertEquals($email, $event->email());
        $this->assertEquals($password, $event->encodedPassword());
        $this->assertEquals($role, $event->role());
        $this->assertEquals($active, $event->active());
        $this->assertEquals($firstName, $event->firstName());
        $this->assertEquals($lastName, $event->lastName());
        $this->assertEquals($sendInvite, $event->sendInvite());
    }

    public function testFromArray(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $email = $faker->emailVo;
        $password = $faker->password;
        $role = Role::ROLE_USER();
        $active = $faker->boolean;
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);
        $sendInvite = $faker->boolean;

        /** @var UserWasAddedByAdmin $event */
        $event = $this->createEventFromArray(
            UserWasAddedByAdmin::class,
            $userId->toString(),
            [
                'email'           => $email->toString(),
                'encodedPassword' => $password,
                'role'            => $role->getValue(),
                'active'          => $active,
                'firstName'       => $firstName->toString(),
                'lastName'        => $lastName->toString(),
                'sendInvite'      => $sendInvite,
            ]
        );

        $this->assertInstanceOf(UserWasAddedByAdmin::class, $event);

        $this->assertEquals($userId, $event->userId());
        $this->assertEquals($email, $event->email());
        $this->assertEquals($password, $event->encodedPassword());
        $this->assertEquals($role, $event->role());
        $this->assertEquals($active, $event->active());
        $this->assertEquals($firstName, $event->firstName());
        $this->assertEquals($lastName, $event->lastName());
        $this->assertEquals($sendInvite, $event->sendInvite());
    }
}
