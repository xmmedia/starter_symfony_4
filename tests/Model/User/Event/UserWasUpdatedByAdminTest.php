<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Event;

use App\Model\User\Event\UserWasUpdatedByAdmin;
use App\Model\User\Name;
use App\Model\User\Role;
use App\Tests\BaseTestCase;
use Xm\SymfonyBundle\Tests\CanCreateEventFromArray;

class UserWasUpdatedByAdminTest extends BaseTestCase
{
    use CanCreateEventFromArray;

    public function testOccur(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId();
        $email = $faker->emailVo();
        $role = Role::ROLE_USER();
        $firstName = Name::fromString($faker->firstName());
        $lastName = Name::fromString($faker->lastName());
        $userData = $faker->userData();

        $event = UserWasUpdatedByAdmin::now($userId, $email, $role, $firstName, $lastName, $userData);

        $this->assertEquals($userId, $event->userId());
        $this->assertEquals($email, $event->email());
        $this->assertEquals($role, $event->role());
        $this->assertEquals($firstName, $event->firstName());
        $this->assertEquals($lastName, $event->lastName());
        $this->assertSameValueAs($userData, $event->userData());
    }

    public function testFromArray(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId();
        $email = $faker->emailVo();
        $role = Role::ROLE_USER();
        $firstName = Name::fromString($faker->firstName());
        $lastName = Name::fromString($faker->lastName());
        $userData = $faker->userData();

        /** @var UserWasUpdatedByAdmin $event */
        $event = $this->createEventFromArray(
            UserWasUpdatedByAdmin::class,
            $userId->toString(),
            [
                'email'     => $email->toString(),
                'role'      => $role->getValue(),
                'firstName' => $firstName->toString(),
                'lastName'  => $lastName->toString(),
                'userData'  => $userData->toArray(),
            ],
        );

        $this->assertInstanceOf(UserWasUpdatedByAdmin::class, $event);

        $this->assertEquals($userId, $event->userId());
        $this->assertEquals($email, $event->email());
        $this->assertEquals($role, $event->role());
        $this->assertEquals($firstName, $event->firstName());
        $this->assertEquals($lastName, $event->lastName());
        $this->assertSameValueAs($userData, $event->userData());
    }
}
