<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Event;

use App\Model\User\Event\MinimalUserWasAddedByAdmin;
use App\Model\User\Name;
use App\Model\User\Role;
use App\Tests\BaseTestCase;
use Xm\SymfonyBundle\Tests\CanCreateEventFromArray;

class MinimalUserWasAddedByAdminTest extends BaseTestCase
{
    use CanCreateEventFromArray;

    public function testOccur(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId();
        $email = $faker->emailVo();
        $password = $faker->password();
        $role = Role::ROLE_USER();
        $firstName = Name::fromString($faker->firstName());
        $lastName = Name::fromString($faker->lastName());
        $sendInvite = $faker->boolean();

        $event = MinimalUserWasAddedByAdmin::now(
            $userId,
            $email,
            $password,
            $role,
            $firstName,
            $lastName,
            $sendInvite,
        );

        $this->assertSameValueAs($userId, $event->userId());
        $this->assertSameValueAs($email, $event->email());
        $this->assertEquals($password, $event->hashedPassword());
        $this->assertEquals($role, $event->role());
        $this->assertSameValueAs($firstName, $event->firstName());
        $this->assertSameValueAs($lastName, $event->lastName());
        $this->assertSame($sendInvite, $event->sendInvite());
    }

    public function testFromArray(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId();
        $email = $faker->emailVo();
        $password = $faker->password();
        $role = Role::ROLE_USER();
        $firstName = Name::fromString($faker->firstName());
        $lastName = Name::fromString($faker->lastName());
        $sendInvite = $faker->boolean();

        /** @var MinimalUserWasAddedByAdmin $event */
        $event = $this->createEventFromArray(
            MinimalUserWasAddedByAdmin::class,
            $userId->toString(),
            [
                'email'          => $email->toString(),
                'hashedPassword' => $password,
                'role'           => $role->getValue(),
                'firstName'      => $firstName->toString(),
                'lastName'       => $lastName->toString(),
                'sendInvite'     => $sendInvite,
            ],
        );

        $this->assertInstanceOf(MinimalUserWasAddedByAdmin::class, $event);

        $this->assertSameValueAs($userId, $event->userId());
        $this->assertSameValueAs($email, $event->email());
        $this->assertEquals($password, $event->hashedPassword());
        $this->assertEquals($role, $event->role());
        $this->assertSameValueAs($firstName, $event->firstName());
        $this->assertSameValueAs($lastName, $event->lastName());
        $this->assertSame($sendInvite, $event->sendInvite());
    }

    public function testFromArrayMissingKeys(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId();
        $email = $faker->emailVo();
        $password = $faker->password();
        $role = Role::ROLE_USER();

        /** @var MinimalUserWasAddedByAdmin $event */
        $event = $this->createEventFromArray(
            MinimalUserWasAddedByAdmin::class,
            $userId->toString(),
            [
                'email'          => $email->toString(),
                'hashedPassword' => $password,
                'role'           => $role->getValue(),
                // missing: firstName, lastName, sendInvite
            ],
        );

        $this->assertInstanceOf(MinimalUserWasAddedByAdmin::class, $event);

        $this->assertSameValueAs($userId, $event->userId());
        $this->assertSameValueAs($email, $event->email());
        $this->assertEquals($password, $event->hashedPassword());
        $this->assertEquals($role, $event->role());

        $this->assertNull($event->firstName());
        $this->assertNull($event->lastName());
        $this->assertFalse($event->sendInvite());
    }
}
