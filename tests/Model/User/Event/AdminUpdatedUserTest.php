<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Event;

use App\Model\Email;
use App\Model\User\Event\AdminUpdatedUser;
use App\Model\User\Name;
use App\Model\User\UserId;
use App\Tests\CanCreateEvent;
use Faker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Role\Role;

class AdminUpdatedUserTest extends TestCase
{
    use CanCreateEvent;

    public function testOccur(): void
    {
        $faker = Faker\Factory::create();

        $userId = UserId::generate();
        $email = Email::fromString($faker->email);
        $role = new Role('ROLE_USER');
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        $event = AdminUpdatedUser::now($userId, $email, $role, $firstName, $lastName);

        $this->assertEquals($userId, $event->userId());
        $this->assertEquals($email, $event->email());
        $this->assertEquals($role, $event->role());
        $this->assertEquals($firstName, $event->firstName());
        $this->assertEquals($lastName, $event->lastName());
    }

    public function testFromArray(): void
    {
        $faker = Faker\Factory::create();

        $userId = UserId::generate();
        $email = Email::fromString($faker->email);
        $role = new Role('ROLE_USER');
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        /** @var AdminUpdatedUser $event */
        $event = $this->createEvent(
            AdminUpdatedUser::class,
            $userId->toString(),
            [
                'email'     => $email->toString(),
                'role'      => $role->getRole(),
                'firstName' => $firstName->toString(),
                'lastName'  => $lastName->toString(),
            ]
        );

        $this->assertInstanceOf(AdminUpdatedUser::class, $event);

        $this->assertEquals($userId, $event->userId());
        $this->assertEquals($email, $event->email());
        $this->assertEquals($role, $event->role());
        $this->assertEquals($firstName, $event->firstName());
        $this->assertEquals($lastName, $event->lastName());
    }
}
