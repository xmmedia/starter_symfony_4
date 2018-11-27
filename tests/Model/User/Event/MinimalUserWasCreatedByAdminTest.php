<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Event;

use App\Model\Email;
use App\Model\User\Event\MinimalUserWasCreatedByAdmin;
use App\Model\User\UserId;
use App\Tests\CanCreateEvent;
use Faker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Role\Role;

class MinimalUserWasCreatedByAdminTest extends TestCase
{
    use CanCreateEvent;

    public function testOccur(): void
    {
        $faker = Faker\Factory::create();

        $userId = UserId::generate();
        $email = Email::fromString($faker->email);
        $password = $faker->password;
        $role = new Role('ROLE_USER');

        $event = MinimalUserWasCreatedByAdmin::now($userId, $email, $password, $role);

        $this->assertEquals($userId, $event->userId());
        $this->assertEquals($email, $event->email());
        $this->assertEquals($password, $event->encodedPassword());
        $this->assertEquals($role, $event->role());
    }

    public function testFromArray(): void
    {
        $faker = Faker\Factory::create();

        $userId = UserId::generate();
        $email = Email::fromString($faker->email);
        $password = $faker->password;
        $role = new Role('ROLE_USER');

        /** @var MinimalUserWasCreatedByAdmin $event */
        $event = $this->createEvent(
            MinimalUserWasCreatedByAdmin::class,
            $userId->toString(),
            [
                'email'           => $email->toString(),
                'encodedPassword' => $password,
                'role'            => $role->getRole(),
            ]
        );

        $this->assertInstanceOf(MinimalUserWasCreatedByAdmin::class, $event);

        $this->assertEquals($userId, $event->userId());
        $this->assertEquals($email, $event->email());
        $this->assertEquals($password, $event->encodedPassword());
        $this->assertEquals($role, $event->role());
    }
}
