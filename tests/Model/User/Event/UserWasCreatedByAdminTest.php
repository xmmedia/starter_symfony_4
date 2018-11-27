<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Event;

use App\Model\Email;
use App\Model\User\Event\UserWasCreatedByAdmin;
use App\Model\User\Name;
use App\Model\User\UserId;
use App\Tests\CanCreateEvent;
use Faker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Role\Role;

class UserWasCreatedByAdminTest extends TestCase
{
    use CanCreateEvent;

    public function testOccur(): void
    {
        $faker = Faker\Factory::create();

        $userId = UserId::generate();
        $email = Email::fromString($faker->email);
        $password = $faker->password;
        $role = new Role('ROLE_USER');
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
        $faker = Faker\Factory::create();

        $userId = UserId::generate();
        $email = Email::fromString($faker->email);
        $password = $faker->password;
        $role = new Role('ROLE_USER');
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        /** @var UserWasCreatedByAdmin $event */
        $event = $this->createEvent(
            UserWasCreatedByAdmin::class,
            $userId->toString(),
            [
                'email'           => $email->toString(),
                'encodedPassword' => $password,
                'role'            => $role->getRole(),
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
