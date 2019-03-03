<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Event;

use App\Model\Email;
use App\Model\User\Event\AdminUpdatedUser;
use App\Model\User\Name;
use App\Tests\BaseTestCase;
use App\Tests\CanCreateEventFromArray;
use Symfony\Component\Security\Core\Role\Role;

class AdminUpdatedUserTest extends BaseTestCase
{
    use CanCreateEventFromArray;

    public function testOccur(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
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
        $faker = $this->faker();

        $userId = $faker->userId;
        $email = Email::fromString($faker->email);
        $role = new Role('ROLE_USER');
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        /** @var AdminUpdatedUser $event */
        $event = $this->createEventFromArray(
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
